<?php

namespace Database\Seeders;

use App\Models\MembershipPlan;
use Illuminate\Database\Seeder;

class MembershipPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Silver Monthly',
                'slug' => 'silver-monthly',
                'description' => 'Full access to gym floor and strength training equipment during standard operating hours.',
                'duration_type' => 'months',
                'duration_count' => 1,
                'price' => 49.00,
                'signup_fee' => 20.00,
                'includes_classes' => false,
                'includes_pt_sessions' => 0,
                'max_freeze_days' => 14,
                'badge_color' => 'secondary',
                'features' => [
                    'Full gym floor access',
                    'Locker room & shower access',
                    'Free mobile workout tracking',
                    'Standard operating hours access'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Gold Quarterly',
                'slug' => 'gold-quarterly',
                'description' => 'Most popular 3-month membership. Includes all group fitness classes and 2 personal training sessions.',
                'duration_type' => 'months',
                'duration_count' => 3,
                'price' => 129.00,
                'signup_fee' => 10.00,
                'includes_classes' => true,
                'includes_pt_sessions' => 2,
                'max_freeze_days' => 21,
                'badge_color' => 'warning',
                'features' => [
                    '24/7 RFID key card access',
                    'Unlimited Group Fitness Classes (Yoga, HIIT, RPM)',
                    '2 Complimentary 1-on-1 PT coaching sessions',
                    'Sauna & Steam room access',
                    'Monthly body composition scan (InBody)'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Platinum VIP Annual',
                'slug' => 'platinum-vip-annual',
                'description' => 'Comprehensive 12-month VIP fitness experience with VIP lockers, laundry service, and 10 PT sessions.',
                'duration_type' => 'years',
                'duration_count' => 1,
                'price' => 480.00,
                'signup_fee' => 0.00,
                'includes_classes' => true,
                'includes_pt_sessions' => 10,
                'max_freeze_days' => 45,
                'badge_color' => 'primary',
                'features' => [
                    '24/7 VIP keyless entry',
                    'Unlimited group fitness & specialty classes',
                    '10 Personal Training sessions included',
                    'Dedicated assigned VIP locker',
                    'Complimentary towel & shake bar voucher (monthly)',
                    'Custom tailored nutrition & diet plan',
                    'Zero enrollment fee'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'CrossFit & Boxing Special',
                'slug' => 'crossfit-boxing-special',
                'description' => 'Tailored for functional fitness athletes, combat conditioning, and high-intensity enthusiasts.',
                'duration_type' => 'months',
                'duration_count' => 1,
                'price' => 89.00,
                'signup_fee' => 15.00,
                'includes_classes' => true,
                'includes_pt_sessions' => 1,
                'max_freeze_days' => 14,
                'badge_color' => 'danger',
                'features' => [
                    'Unlimited CrossFit WOD & Combat Boxing classes',
                    'Functional training turf & rig access',
                    'Free Olympic lifting chalk & straps',
                    'Specialist coach evaluation'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Student Semester Pass',
                'slug' => 'student-semester-pass',
                'description' => 'Discounted 4-month pass for verified high school and university students.',
                'duration_type' => 'months',
                'duration_count' => 4,
                'price' => 120.00,
                'signup_fee' => 0.00,
                'includes_classes' => true,
                'includes_pt_sessions' => 0,
                'max_freeze_days' => 30,
                'badge_color' => 'info',
                'features' => [
                    'Valid student ID required',
                    'Full gym floor access',
                    'Access to all scheduled classes',
                    'Weekend guest pass included'
                ],
                'is_active' => true,
            ]
        ];

        foreach ($plans as $plan) {
            MembershipPlan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
