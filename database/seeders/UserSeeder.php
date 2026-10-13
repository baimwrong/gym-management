<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin / Owner
        User::updateOrCreate(
            ['email' => 'admin@gym.local'],
            [
                'name' => 'Marcus Vance (General Manager)',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'phone' => '+1 (555) 019-2831',
                'status' => 'active',
            ]
        );

        // 2. Manager
        User::updateOrCreate(
            ['email' => 'manager@gym.local'],
            [
                'name' => 'Elena Rostova (Operations Manager)',
                'password' => Hash::make('password123'),
                'role' => 'manager',
                'phone' => '+1 (555) 019-2832',
                'status' => 'active',
            ]
        );

        // 3. Receptionist / Front Desk Cashier
        User::updateOrCreate(
            ['email' => 'reception@gym.local'],
            [
                'name' => 'Sarah Connor (Front Desk)',
                'password' => Hash::make('password123'),
                'role' => 'receptionist',
                'phone' => '+1 (555) 019-2833',
                'status' => 'active',
            ]
        );

        // 4. Trainers
        User::updateOrCreate(
            ['email' => 'alex.trainer@gym.local'],
            [
                'name' => 'Alex Thorne',
                'password' => Hash::make('password123'),
                'role' => 'trainer',
                'phone' => '+1 (555) 019-2834',
                'status' => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'maya.trainer@gym.local'],
            [
                'name' => 'Maya Lin',
                'password' => Hash::make('password123'),
                'role' => 'trainer',
                'phone' => '+1 (555) 019-2835',
                'status' => 'active',
            ]
        );
    }
}
