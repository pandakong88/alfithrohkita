<?php

namespace Database\Seeders;

use App\Models\Permission;

use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run()
    {
        $permissions = [
            'manage_users',
            'manage_santri',
            'manage_keuangan',
            'manage_absensi',
            'manage_cms',
        ];
    
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
    
}
