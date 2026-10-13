<?php

namespace Database\Seeders;

use App\Models\Locker;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LockerSeeder extends Seeder
{
    public function run(): void
    {
        $members = Member::where('status', 'active')->take(4)->get();

        $zones = [
            'Men Locker Room' => 8,
            'Women Locker Room' => 8,
            'VIP Executive Suite' => 6,
        ];

        $memberIndex = 0;
        foreach ($zones as $zoneName => $count) {
            $prefix = match ($zoneName) {
                'Men Locker Room' => 'M',
                'Women Locker Room' => 'W',
                default => 'VIP',
            };

            for ($i = 1; $i <= $count; $i++) {
                $lockerNumber = sprintf('%s-%02d', $prefix, $i);
                
                $status = 'available';
                $assignedMember = null;
                $assignedAt = null;
                $expiresAt = null;

                // Assign first few lockers to demo members
                if ($i <= 2 && isset($members[$memberIndex])) {
                    $status = 'occupied';
                    $assignedMember = $members[$memberIndex]->id;
                    $assignedAt = Carbon::now()->subMonths(1);
                    $expiresAt = Carbon::now()->addMonths(5);
                    $memberIndex = ($memberIndex + 1) % count($members);
                } elseif ($i === 4 && $prefix === 'M') {
                    $status = 'maintenance';
                }

                Locker::updateOrCreate(
                    ['locker_number' => $lockerNumber],
                    [
                        'zone' => $zoneName,
                        'status' => $status,
                        'assigned_member_id' => $assignedMember,
                        'assigned_at' => $assignedAt,
                        'expires_at' => $expiresAt,
                        'rental_fee_monthly' => ($prefix === 'VIP') ? 35.00 : 15.00,
                    ]
                );
            }
        }
    }
}
