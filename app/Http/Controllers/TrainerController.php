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
        ]);

        TrainerMemberAssignment::create([
            'trainer_id' => $trainer->id,
            'member_id' => $validated['member_id'],
            'package_name' => $validated['package_name'],
            'total_sessions' => $validated['total_sessions'],
            'completed_sessions' => 0,
            'status' => 'active',
            'start_date' => $validated['start_date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Client successfully assigned to trainer!');
    }

    public function logSession(TrainerMemberAssignment $assignment)
    {
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
