<?php

use App\Models\MemberSubscription;
use App\Models\Member;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('gym:check-expirations', function () {
    $this->info('Checking expiring gym memberships...');
    
    // Find active subscriptions whose end date is past
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
            $sub->member->update(['status' => 'expired']);
        }
        $count++;
    }

    $this->info("Successfully updated {$count} expired memberships.");
})->purpose('Check and transition expired member subscriptions to expired status');

Schedule::command('gym:check-expirations')->dailyAt('00:01');
