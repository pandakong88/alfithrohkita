<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Role;


class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        $superAdmin = User::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@alfitroh.com',
            'password' => bcrypt('password'),
            'role'     => 'super_admin',
            'is_active'=> true,
        ]);
        
        $role = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);
        
        $superAdmin->assignRole($role);
        
        $this->command->info('Seed Berhasil: Pondok, Roles, dan Users telah dibuat!');
    }
}