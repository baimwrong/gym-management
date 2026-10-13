<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $members = Member::where('status', 'active')->get();

        if ($members->isEmpty()) {
            return;
        }

        // Today's attendance
        $today = Carbon::today();

        // 1. Members currently in gym
        if (isset($members[0])) {
            Attendance::create([
                'member_id' => $members[0]->id,
                'check_in_time' => Carbon::now()->subMinutes(42),
                'check_out_time' => null, // currently inside!
                'check_in_method' => 'qr_kiosk',
                'notes' => 'Member pass scanned at front turnstile',
            ]);
        }

        if (isset($members[1])) {
            Attendance::create([
                'member_id' => $members[1]->id,
                'check_in_time' => Carbon::now()->subMinutes(25),
                'check_out_time' => null, // currently inside!
                'check_in_method' => 'rfid_card',
                'notes' => 'RFID tag scanned',
            ]);
        }

        if (isset($members[2])) {
            Attendance::create([
                'member_id' => $members[2]->id,
                'check_in_time' => Carbon::now()->subHours(2),
                'check_out_time' => Carbon::now()->subMinutes(30),
                'check_in_method' => 'front_desk',
                'notes' => 'Checked out after CrossFit session',
            ]);
        }

        if (isset($members[3])) {
            Attendance::create([
                'member_id' => $members[3]->id,
                'check_in_time' => Carbon::now()->subHours(3),
                'check_out_time' => Carbon::now()->subHours(1)->subMinutes(45),
                'check_in_method' => 'front_desk',
                'notes' => 'Morning cardio workout',
            ]);
        }

        // Generate past 7 days attendance for trend charts
        for ($d = 1; $d <= 7; $d++) {
            $pastDate = Carbon::today()->subDays($d);
            foreach ($members->take(4) as $index => $m) {
                $checkInHour = 7 + ($index * 3);
                $checkIn = (clone $pastDate)->setHour($checkInHour)->setMinute(15);
                $checkOut = (clone $checkIn)->addMinutes(65 + ($index * 10));

                Attendance::create([
                    'member_id' => $m->id,
                    'check_in_time' => $checkIn,
                    'check_out_time' => $checkOut,
                    'check_in_method' => 'front_desk',
                    'notes' => 'Routine session',
                ]);
            }
        }
    }
}
