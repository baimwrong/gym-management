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
        ]);

        $expiresAt = Carbon::now()->addMonths((int) $validated['duration_months']);

        $locker->update([
            'status' => 'occupied',
            'assigned_member_id' => $validated['member_id'],
            'assigned_at' => now(),
            'expires_at' => $expiresAt,
        ]);

        return back()->with('success', "Locker {$locker->locker_number} assigned successfully until {$expiresAt->format('M d, Y')}!");
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

        $locker->update(['status' => $validated['status']]);

        return back()->with('success', "Locker {$locker->locker_number} status updated to '{$validated['status']}'.");
    }
}
