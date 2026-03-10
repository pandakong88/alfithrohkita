<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = ['manage_users', 'manage_santri', 'manage_keuangan', 'manage_absensi', 'manage_cms', 'manage_wali'];
        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $adminPondok = Role::firstOrCreate(['name' => 'admin_pondok', 'guard_name' => 'web']);
        $adminPondok->syncPermissions(['manage_santri', 'manage_wali', 'manage_absensi']);
    }
}