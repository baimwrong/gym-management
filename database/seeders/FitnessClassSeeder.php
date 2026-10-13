<?php

namespace Database\Seeders;

use App\Models\FitnessClass;
use App\Models\Trainer;
use Illuminate\Database\Seeder;

class FitnessClassSeeder extends Seeder
{
    public function run(): void
    {
        $trainerAlex = Trainer::first();
        $trainerMaya = Trainer::skip(1)->first();

        $classes = [
            [
                'title' => 'CrossFit WOD & Power',
                'description' => 'High intensity barbell and calisthenics workout of the day. Functional capacity and strength.',
                'trainer_id' => $trainerAlex?->id,
                'room' => 'Functional Rig Arena',
                'capacity' => 20,
                'day_of_week' => 'Monday',
                'start_time' => '07:00:00',
                'end_time' => '08:00:00',
                'difficulty_level' => 'advanced',
                'color_tag' => '#ef4444',
                'is_active' => true,
            ],
            [
                'title' => 'Vinyasa Flow Yoga',
                'description' => 'Dynamic breath-synchronized movement sequence designed to release tension, lengthen fascia, and improve core stability.',
                'trainer_id' => $trainerMaya?->id,
                'room' => 'Mind & Body Studio B',
                'capacity' => 25,
                'day_of_week' => 'Monday',
                'start_time' => '08:30:00',
                'end_time' => '09:30:00',
                'difficulty_level' => 'all_levels',
                'color_tag' => '#10b981',
                'is_active' => true,
            ],
            [
                'title' => 'HIIT Inferno & MetCon',
                'description' => 'Metabolic conditioning intervals using assault bikes, kettlebells, rowers, and plyometrics.',
                'trainer_id' => $trainerMaya?->id,
                'room' => 'Turf Sprint Zone',
                'capacity' => 24,
                'day_of_week' => 'Tuesday',
                'start_time' => '17:30:00',
                'end_time' => '18:30:00',
                'difficulty_level' => 'intermediate',
                'color_tag' => '#f59e0b',
                'is_active' => true,
            ],
            [
                'title' => 'Heavy Barbell Strength Clinic',
                'description' => 'Technique breakdown and progressive overload sets for Squat, Bench, and Deadlift under expert coaching.',
                'trainer_id' => $trainerAlex?->id,
                'room' => 'Olympic Lifting Platform',
                'capacity' => 12,
                'day_of_week' => 'Wednesday',
                'start_time' => '18:00:00',
                'end_time' => '19:15:00',
                'difficulty_level' => 'intermediate',
                'color_tag' => '#6366f1',
                'is_active' => true,
            ],
            [
                'title' => 'RPM Spinning & Rhythm Cycling',
                'description' => 'High-cadence cardio ride with immersive lighting and hill climbs to torch calories.',
                'trainer_id' => $trainerMaya?->id,
                'room' => 'Cycle Theater',
                'capacity' => 30,
                'day_of_week' => 'Thursday',
                'start_time' => '06:30:00',
                'end_time' => '07:20:00',
                'difficulty_level' => 'all_levels',
                'color_tag' => '#06b6d4',
                'is_active' => true,
            ],
            [
                'title' => 'Combat Boxing & Kick Conditioning',
                'description' => 'Heavy bag drills, pad work, and footwork conditioning. Zero experience required.',
                'trainer_id' => $trainerAlex?->id,
                'room' => 'Boxing Ring & Bags',
                'capacity' => 18,
                'day_of_week' => 'Friday',
                'start_time' => '17:00:00',
                'end_time' => '18:00:00',
                'difficulty_level' => 'all_levels',
                'color_tag' => '#ec4899',
                'is_active' => true,
            ],
            [
                'title' => 'Weekend Warrior Boot Camp',
                'description' => 'Team-based functional circuit training covering strength, agility, sled pushes, and cardio endurance.',
                'trainer_id' => $trainerMaya?->id,
                'room' => 'Outdoor & Indoor Turf',
                'capacity' => 35,
                'day_of_week' => 'Saturday',
                'start_time' => '09:00:00',
                'end_time' => '10:15:00',
                'difficulty_level' => 'intermediate',
                'color_tag' => '#8b5cf6',
                'is_active' => true,
            ],
        ];

        foreach ($classes as $item) {
            FitnessClass::create($item);
        }
    }
}
