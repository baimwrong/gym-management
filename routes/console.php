<?php

use App\Models\Attendance;
use App\Models\Member;
use App\Models\MemberSubscription;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Check and transition expired member subscriptions to expired status.
 */
Artisan::command('gym:check-expirations', function () {
    $this->info('Checking expiring gym memberships...');
    
    $expiredSubs = MemberSubscription::where('status', 'active')
        ->whereDate('end_date', '<', now()->toDateString())
        ->get();

    $count = 0;
    foreach ($expiredSubs as $sub) {
        $sub->update(['status' => 'expired']);
        
        // If member has no other active subscriptions, mark member expired
        $hasActive = MemberSubscription::where('member_id', $sub->member_id)
            ->where('status', 'active')
            ->whereDate('end_date', '>=', now()->toDateString())
            ->exists();

        if (!$hasActive) {
            $sub->member?->update(['status' => 'expired']);
        }
        $count++;
    }

    $this->info("Successfully updated {$count} expired memberships.");
})->purpose('Check and transition expired member subscriptions to expired status');

/**
 * Auto-unfreeze subscriptions whose pause period has completed.
 */
Artisan::command('gym:unfreeze-memberships', function () {
    $this->info('Checking paused/frozen memberships ready to reactivate...');

    $frozenSubs = MemberSubscription::where('status', 'frozen')
        ->whereNotNull('unfreeze_date')
        ->whereDate('unfreeze_date', '<=', now()->toDateString())
        ->get();

    $count = 0;
    foreach ($frozenSubs as $sub) {
        $sub->update([
            'status' => 'active',
            'unfreeze_date' => null,
            'notes' => trim(($sub->notes ?? '') . ' [Automatically un-frozen by scheduled audit on ' . now()->toDateString() . ']'),
        ]);

        if ($sub->member && $sub->member->status === 'frozen') {
            $sub->member->update(['status' => 'active']);
        }
        $count++;
    }

    $this->info("Successfully reactivated {$count} frozen memberships.");
})->purpose('Reactivate memberships whose frozen duration has concluded');

/**
 * Auto-close stale attendance check-ins from previous days where member never checked out.
 */
Artisan::command('gym:close-stale-attendances', function () {
    $this->info('Scanning for open attendance records from previous days...');

    $staleAttendances = Attendance::whereNull('check_out_time')
        ->whereDate('check_in_time', '<', now()->toDateString())
        ->get();

    $count = 0;
    foreach ($staleAttendances as $attendance) {
        // Default close time to 2 hours after check-in, capped at 23:59:59 of check-in day
        $checkIn = Carbon::parse($attendance->check_in_time);
        $autoOut = (clone $checkIn)->addHours(2);
        $endOfDay = (clone $checkIn)->endOfDay();
        if ($autoOut->greaterThan($endOfDay)) {
            $autoOut = $endOfDay;
        }

        $attendance->update([
            'check_out_time' => $autoOut,
            'notes' => trim(($attendance->notes ?? '') . ' [Auto-closed by daily system audit]'),
        ]);
        $count++;
    }

    $this->info("Successfully closed {$count} stale attendance records.");
})->purpose('Auto-close open attendance sessions from prior days');

// Register Daily Schedules
Schedule::command('gym:check-expirations')->dailyAt('00:01');
Schedule::command('gym:unfreeze-memberships')->dailyAt('00:05');
Schedule::command('gym:close-stale-attendances')->dailyAt('00:10');
