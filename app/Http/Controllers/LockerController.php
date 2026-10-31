<?php

namespace App\Http\Controllers;

use App\Models\Locker;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LockerController extends Controller
{
    public function index()
    {
        $lockers = Locker::with('assignedMember')->orderBy('locker_number')->get();
        $members = Member::where('status', 'active')->orderBy('first_name')->get();

        $zones = $lockers->pluck('zone')->unique()->values()->all();
        $totalLockers = $lockers->count();
        $occupiedCount = $lockers->where('status', 'occupied')->count();
        $availableCount = $lockers->where('status', 'available')->count();

        return view('lockers.index', compact('lockers', 'members', 'zones', 'totalLockers', 'occupiedCount', 'availableCount'));
    }

    public function assign(Request $request, Locker $locker)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'duration_months' => 'required|integer|min:1|max:24',
            'payment_method' => 'nullable|string|in:cash,credit_card,debit_card,qris_pos,bank_transfer',
        ]);

        if ($locker->status !== 'available') {
            return back()->with('error', "Locker {$locker->locker_number} is currently not available (Status: {$locker->status}).");
        }

        $member = Member::findOrFail($validated['member_id']);

        $existingLocker = Locker::where('assigned_member_id', $member->id)
            ->where('id', '!=', $locker->id)
            ->where('status', 'occupied')
            ->first();

        if ($existingLocker) {
            return back()->with('error', "Member {$member->full_name} is already assigned to Locker {$existingLocker->locker_number}.");
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $locker, $member, $request) {
            $durationMonths = (int) $validated['duration_months'];
            $expiresAt = Carbon::now()->addMonths($durationMonths);

            $locker->update([
                'status' => 'occupied',
                'assigned_member_id' => $member->id,
                'assigned_at' => now(),
                'expires_at' => $expiresAt,
            ]);

            $monthlyFee = (float) ($locker->rental_fee_monthly ?? 0);
            $totalFee = round($monthlyFee * $durationMonths, 2);

            if ($totalFee > 0) {
                $invoice = \App\Models\Invoice::create([
                    'invoice_number' => \App\Services\BillingService::generateInvoiceNumber(),
                    'member_id' => $member->id,
                    'user_id' => auth()->id(),
                    'customer_name' => $member->full_name,
                    'subtotal' => $totalFee,
                    'tax_amount' => 0.00,
                    'discount_amount' => 0.00,
                    'total_amount' => $totalFee,
                    'payment_status' => 'paid',
                    'payment_method' => $validated['payment_method'] ?? 'cash',
                    'invoice_type' => 'locker_rental',
                    'paid_at' => now(),
                    'due_date' => now()->toDateString(),
                    'notes' => "Locker {$locker->locker_number} ({$locker->zone}) rental for {$durationMonths} months",
                ]);

                \App\Models\InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'itemable_type' => Locker::class,
                    'itemable_id' => $locker->id,
                    'item_description' => "Locker Rental: #{$locker->locker_number} ({$locker->zone}) - {$durationMonths} Months",
                    'unit_price' => $monthlyFee,
                    'quantity' => $durationMonths,
                    'total_price' => $totalFee,
                ]);
            }

            return back()->with('success', "Locker {$locker->locker_number} assigned to {$member->full_name} until {$expiresAt->format('M d, Y')}!");
        });
    }

    public function release(Locker $locker)
    {
        $locker->update([
            'status' => 'available',
            'assigned_member_id' => null,
            'assigned_at' => null,
            'expires_at' => null,
        ]);

        return back()->with('info', "Locker {$locker->locker_number} is now released and available.");
    }

    public function setStatus(Request $request, Locker $locker)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,occupied,maintenance',
        ]);

        if ($validated['status'] === 'occupied' && !$locker->assigned_member_id) {
            return back()->with('error', 'Cannot set locker to occupied without assigning a member.');
        }

        if ($validated['status'] === 'available') {
            $locker->update([
                'status' => 'available',
                'assigned_member_id' => null,
                'assigned_at' => null,
                'expires_at' => null,
            ]);
            return back()->with('success', "Locker {$locker->locker_number} is now marked Available.");
        }

        $locker->update(['status' => $validated['status']]);

        return back()->with('success', "Locker {$locker->locker_number} status updated to '{$validated['status']}'.");
    }
}
