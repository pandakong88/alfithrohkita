<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Jalankan PermissionSeeder dulu
        $this->call([
            PermissionSeeder::class,
        ]);

        // 2. Buat Role
        $role = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        // 3. Buat User Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'], // Cek berdasarkan email biar gak duplikat
            [
                'name'     => 'Super Admin',
                'password' => bcrypt('password'),
                'role'     => 'super_admin',
                'is_active'=> true,
            ]
        );
        
        // 4. Hubungkan User ke Role
        if (!$superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole($role);
        }
        
        $this->command->info('Seed Berhasil: Permissions, Roles, dan Super Admin telah dibuat!');
    }
}