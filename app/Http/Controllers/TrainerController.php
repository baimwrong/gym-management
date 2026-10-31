<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Trainer;
use App\Models\TrainerMemberAssignment;
use App\Models\User;
use Illuminate\Http\Request;

class TrainerController extends Controller
{
    public function index()
    {
        $trainers = Trainer::with(['user', 'activeClients.member'])
            ->withCount('activeClients')
            ->get();

        return view('trainers.index', compact('trainers'));
    }

    public function show(Trainer $trainer)
    {
        $trainer->load([
            'user',
            'assignments.member',
            'classes',
            'workoutPlans',
            'dietPlans',
        ]);

        $members = Member::where('status', 'active')->orderBy('first_name')->get();

        return view('trainers.show', compact('trainer', 'members'));
    }

    public function assignClient(Request $request, Trainer $trainer)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'package_name' => 'required|string|max:120',
            'total_sessions' => 'required|integer|min:1|max:100',
            'start_date' => 'required|date',
            'notes' => 'nullable|string',
            'use_plan_sessions' => 'nullable|boolean',
            'package_price' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|in:cash,credit_card,debit_card,qris_pos,bank_transfer',
        ]);

        $member = Member::with('activeSubscription.plan')->findOrFail($validated['member_id']);
        $totalSessions = (int) $validated['total_sessions'];

        return \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $trainer, $member, $totalSessions, $request) {
            $notes = $validated['notes'] ?? '';

            // If deducting from active membership plan allowance
            if ($request->boolean('use_plan_sessions')) {
                $sub = $member->activeSubscription;
                if (!$sub || $sub->remaining_pt_sessions < $totalSessions) {
                    $available = $sub?->remaining_pt_sessions ?? 0;
                    return back()->with('error', "Cannot assign: Member only has {$available} plan PT sessions remaining.");
                }
                $sub->decrement('remaining_pt_sessions', $totalSessions);
                $notes = trim($notes . " [Deducted {$totalSessions} sessions from {$sub->plan->name} quota]");
            } elseif (!empty($validated['package_price']) && (float) $validated['package_price'] > 0) {
                // Generate PT package billing invoice
                $price = (float) $validated['package_price'];
                $invoice = \App\Models\Invoice::create([
                    'invoice_number' => \App\Services\BillingService::generateInvoiceNumber(),
                    'member_id' => $member->id,
                    'user_id' => auth()->id(),
                    'customer_name' => $member->full_name,
                    'subtotal' => $price,
                    'tax_amount' => 0.00,
                    'discount_amount' => 0.00,
                    'total_amount' => $price,
                    'payment_status' => 'paid',
                    'payment_method' => $validated['payment_method'] ?? 'cash',
                    'invoice_type' => 'pt_package',
                    'paid_at' => now(),
                    'due_date' => now()->toDateString(),
                    'notes' => "Personal Training Package: {$validated['package_name']} ({$totalSessions} sessions with {$trainer->full_name})",
                ]);

                \App\Models\InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'itemable_type' => Trainer::class,
                    'itemable_id' => $trainer->id,
                    'item_description' => "PT Package: {$validated['package_name']} ({$totalSessions} Sessions)",
                    'unit_price' => $price,
                    'quantity' => 1,
                    'total_price' => $price,
                ]);
            }

            TrainerMemberAssignment::create([
                'trainer_id' => $trainer->id,
                'member_id' => $member->id,
                'package_name' => $validated['package_name'],
                'total_sessions' => $totalSessions,
                'completed_sessions' => 0,
                'status' => 'active',
                'start_date' => $validated['start_date'],
                'notes' => $notes ?: null,
            ]);

            return back()->with('success', "Client {$member->full_name} assigned to {$trainer->full_name} for {$totalSessions} sessions!");
        });
    }

    public function logSession(TrainerMemberAssignment $assignment)
    {
        $user = auth()->user();
        if ($user->role === 'trainer' && $user->trainer?->id !== $assignment->trainer_id) {
            abort(403, 'Unauthorized: You can only log sessions for your own assigned clients.');
        }

        if ($assignment->completed_sessions >= $assignment->total_sessions) {
            return back()->with('warning', 'All sessions for this package have already been completed!');
        }

        $assignment->increment('completed_sessions');

        if ($assignment->completed_sessions >= $assignment->total_sessions) {
            $assignment->update(['status' => 'completed']);
            return back()->with('success', 'PT session logged! Package is now marked as Completed.');
        }

        $remaining = $assignment->remaining_sessions;
        return back()->with('success', "PT session logged successfully. {$remaining} sessions remaining.");
    }
}
