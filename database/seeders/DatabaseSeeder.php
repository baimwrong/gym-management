<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            MembershipPlanSeeder::class,
            TrainerSeeder::class,
            MemberSeeder::class,
            FitnessClassSeeder::class,
            ProductSeeder::class,
            LockerSeeder::class,
            EquipmentSeeder::class,
            AttendanceSeeder::class,
        ]);
    }
}
