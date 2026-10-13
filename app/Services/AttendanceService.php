<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Member;
use App\Models\User;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Process check-in for a member.
     */
    public function processCheckIn(Member $member, string $method = 'front_desk', ?User $staff = null): array
    {
        $eligibility = $member->isEligibleForCheckIn();

        if (!$eligibility['allowed']) {
            return [
                'success' => false,
                'message' => $eligibility['message'],
                'member' => $member,
                'status' => 'denied',
            ];
        }

        // Check if member is already checked in and has not checked out yet today
        $existing = Attendance::where('member_id', $member->id)
            ->whereNull('check_out_time')
            ->whereDate('check_in_time', now()->toDateString())
            ->first();

        if ($existing) {
            return [
                'success' => true,
                'already_in' => true,
                'message' => "{$member->full_name} is already checked in at {$existing->check_in_time->format('h:i A')}.",
                'member' => $member,
                'attendance' => $existing,
                'status' => 'already_checked_in',
            ];
        }

        $attendance = Attendance::create([
            'member_id' => $member->id,
            'check_in_time' => now(),
            'check_in_method' => $method,
            'checked_in_by_user_id' => $staff?->id ?? auth()->id(),
            'notes' => $eligibility['warning'] ? $eligibility['message'] : null,
        ]);

        return [
            'success' => true,
            'already_in' => false,
            'message' => "Welcome, {$member->full_name}! Check-in recorded at " . now()->format('h:i A'),
            'warning' => $eligibility['warning'],
            'warning_message' => $eligibility['warning'] ? $eligibility['message'] : null,
            'member' => $member,
            'attendance' => $attendance,
            'status' => 'success',
        ];
    }

    /**
     * Process check-out for a member.
     */
    public function processCheckOut(Member $member): array
    {
        $activeAttendance = Attendance::where('member_id', $member->id)
            ->whereNull('check_out_time')
            ->latest('check_in_time')
            ->first();

        if (!$activeAttendance) {
            return [
                'success' => false,
                'message' => "No active session found for {$member->full_name}.",
            ];
        }

        $activeAttendance->update([
            'check_out_time' => now(),
        ]);

        $duration = $activeAttendance->duration_minutes;

        return [
            'success' => true,
            'message' => "Good workout, {$member->full_name}! Checked out successfully. Session time: {$duration} minutes.",
            'attendance' => $activeAttendance,
        ];
    }
}
