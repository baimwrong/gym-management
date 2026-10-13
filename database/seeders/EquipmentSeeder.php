<?php

namespace Database\Seeders;

use App\Models\Equipment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $equipmentItems = [
            [
                'name' => 'Technogym Excite Live Run Treadmill #01',
                'category' => 'cardio',
                'serial_number' => 'TG-RUN-2024-9901',
                'location' => 'Cardio Deck - Level 1',
                'purchase_date' => Carbon::now()->subYears(1)->toDateString(),
                'warranty_expiry' => Carbon::now()->addYears(2)->toDateString(),
                'last_service_date' => Carbon::now()->subMonths(1)->toDateString(),
                'next_service_due' => Carbon::now()->addMonths(5)->toDateString(),
                'status' => 'operational',
                'notes' => 'Lubricated running deck and calibrated speed sensor.',
            ],
            [
                'name' => 'Technogym Excite Live Run Treadmill #02',
                'category' => 'cardio',
                'serial_number' => 'TG-RUN-2024-9902',
                'location' => 'Cardio Deck - Level 1',
                'purchase_date' => Carbon::now()->subYears(1)->toDateString(),
                'warranty_expiry' => Carbon::now()->addYears(2)->toDateString(),
                'last_service_date' => Carbon::now()->subMonths(3)->toDateString(),
                'next_service_due' => Carbon::now()->subDays(5)->toDateString(), // intentionally service overdue!
                'status' => 'needs_service',
                'notes' => 'Slight squeak in rear roller bearing.',
            ],
            [
                'name' => 'Hammer Strength Olympic Power Rack & Platform',
                'category' => 'strength',
                'serial_number' => 'HS-RACK-8810',
                'location' => 'Free Weights Sector',
                'purchase_date' => Carbon::now()->subYears(2)->toDateString(),
                'warranty_expiry' => Carbon::now()->addYears(3)->toDateString(),
                'last_service_date' => Carbon::now()->subMonths(2)->toDateString(),
                'next_service_due' => Carbon::now()->addMonths(4)->toDateString(),
                'status' => 'operational',
                'notes' => 'J-hooks and safety straps inspected.',
            ],
            [
                'name' => 'Life Fitness Dual Adjustable Cable Pulley Crossover',
                'category' => 'strength',
                'serial_number' => 'LF-DAP-7721',
                'location' => 'Machine Area',
                'purchase_date' => Carbon::now()->subYears(1)->toDateString(),
                'warranty_expiry' => Carbon::now()->addYears(2)->toDateString(),
                'last_service_date' => Carbon::now()->subMonths(1)->toDateString(),
                'next_service_due' => Carbon::now()->addMonths(5)->toDateString(),
                'status' => 'operational',
                'notes' => 'Aviation steel cable replaced and tension balanced.',
            ],
            [
                'name' => 'Concept2 RowErg Model D Indoor Rower',
                'category' => 'cardio',
                'serial_number' => 'C2-ROW-3341',
                'location' => 'HIIT / Functional Zone',
                'purchase_date' => Carbon::now()->subMonths(10)->toDateString(),
                'warranty_expiry' => Carbon::now()->addYears(2)->toDateString(),
                'last_service_date' => Carbon::now()->subMonths(2)->toDateString(),
                'next_service_due' => Carbon::now()->addMonths(4)->toDateString(),
                'status' => 'operational',
                'notes' => 'PM5 monitor batteries replaced, chain oiled.',
            ],
            [
                'name' => 'StairMaster 10G Gauntlet Stepmill',
                'category' => 'cardio',
                'serial_number' => 'SM-STEP-1099',
                'location' => 'Cardio Deck - Level 1',
                'purchase_date' => Carbon::now()->subMonths(8)->toDateString(),
                'warranty_expiry' => Carbon::now()->addYears(2)->toDateString(),
                'last_service_date' => Carbon::now()->subMonths(1)->toDateString(),
                'next_service_due' => Carbon::now()->addMonths(5)->toDateString(),
                'status' => 'operational',
                'notes' => 'Alternator belt tension verified.',
            ],
            [
                'name' => 'Rogue Calibrated Steel KG Plates (Set of 450kg)',
                'category' => 'free_weights',
                'serial_number' => 'RG-PLT-0045',
                'location' => 'Deadlift Platform #1',
                'purchase_date' => Carbon::now()->subYears(1)->toDateString(),
                'warranty_expiry' => Carbon::now()->addYears(5)->toDateString(),
                'last_service_date' => Carbon::now()->subMonths(6)->toDateString(),
                'next_service_due' => Carbon::now()->addMonths(6)->toDateString(),
                'status' => 'operational',
                'notes' => 'IPF certified calibrated plates.',
            ]
        ];

        foreach ($equipmentItems as $item) {
            Equipment::updateOrCreate(['serial_number' => $item['serial_number']], $item);
        }
    }
}
