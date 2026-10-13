<?php

namespace Database\Seeders;

use App\Models\Trainer;
use App\Models\User;
use Illuminate\Database\Seeder;

class TrainerSeeder extends Seeder
{
    public function run(): void
    {
        $userAlex = User::where('email', 'alex.trainer@gym.local')->first();
        $userMaya = User::where('email', 'maya.trainer@gym.local')->first();

        if ($userAlex) {
            Trainer::updateOrCreate(
                ['user_id' => $userAlex->id],
                [
                    'full_name' => 'Alex "The Titan" Thorne',
                    'phone' => '+1 (555) 019-2834',
                    'email' => $userAlex->email,
                    'specialties' => ['Powerlifting', 'Hypertrophy Bodybuilding', 'Olympic Weightlifting', 'Injury Rehabilitation'],
                    'biography' => 'Former competitive powerlifter and CSCS-certified strength coach with 9+ years helping lifters break through plateaus and build dense muscle.',
                    'hourly_rate' => 65.00,
                    'commission_rate_percentage' => 25.00,
                    'certifications' => 'NSCA-CSCS, USA Weightlifting Level 2, Precision Nutrition L1',
                    'is_available' => true,
                ]
            );
        }

        if ($userMaya) {
            Trainer::updateOrCreate(
                ['user_id' => $userMaya->id],
                [
                    'full_name' => 'Maya Lin',
                    'phone' => '+1 (555) 019-2835',
                    'email' => $userMaya->email,
                    'specialties' => ['HIIT & Conditioning', 'Vinyasa Yoga', 'Functional Calisthenics', 'Fat Loss Nutrition'],
                    'biography' => 'NASM master trainer and certified 500-hour RYT yoga instructor focusing on mobility, metabolic conditioning, and lean aesthetic transformations.',
                    'hourly_rate' => 55.00,
                    'commission_rate_percentage' => 20.00,
                    'certifications' => 'NASM-CPT, RYT-500 Yoga Alliance, FMS Level 1',
                    'is_available' => true,
                ]
            );
        }
    }
}
