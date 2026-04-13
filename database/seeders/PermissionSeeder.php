<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Daftar Permissions
        $permissions = [
            'manage_users', 
            'manage_santri', 
            'manage_keuangan', 
            'manage_absensi', 
            'manage_cms', 
            'manage_wali',
            'manage_perizinan'
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // 3. Buat Roles
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        
        $adminPondok = Role::firstOrCreate(['name' => 'admin_pondok', 'guard_name' => 'web']);

        // 4. BERIKAN SEMUA AKSES KE ADMIN PONDOK
        // Mengambil semua permissions yang ada di database dan mensinkronisasikannya
        $allPermissions = Permission::all();
        $adminPondok->syncPermissions($allPermissions);
    }
}