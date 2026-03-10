<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        $super = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Super Admin Arjunanda',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );
        $super->assignRole('super_admin');

        // Admin Pondok ID 3
        $admin = User::firstOrCreate(
            ['email' => 'arjunanda@gmail.com'],
            [
                'name' => 'Admin Al-Fitroh',
                'password' => Hash::make('password'),
                'role' => 'admin_pondok',
                'pondok_id' => 3,
                'is_active' => true,
            ]
        );
        $admin->assignRole('admin_pondok');
    }
}