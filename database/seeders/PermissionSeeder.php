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
            'view_users',
            'manage_users', 
            'view_santri',
            'manage_santri', 
            'view_wali',
            'manage_wali',
            'view_asrama',
            'manage_asrama',
            'view_absensi',
            'manage_absensi',
            'view_perizinan',
            'manage_perizinan',
            'view_pelanggaran',
            'manage_pelanggaran',
            'view_cms',
            'manage_cms',
            'manage_settings',
            'view_keuangan',
            'manage_keuangan',
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

        // 5. SEED ROLE SPESIFIK & HAK AKSES
        $bendahara = Role::firstOrCreate(['name' => 'bendahara', 'guard_name' => 'web']);
        $bendahara->syncPermissions([
            'view_santri',
            'view_wali',
            'view_keuangan',
            'manage_keuangan',
        ]);

        $keamanan = Role::firstOrCreate(['name' => 'keamanan', 'guard_name' => 'web']);
        $keamanan->syncPermissions([
            'view_santri',
            'view_wali',
            'view_perizinan',
            'manage_perizinan',
            'view_pelanggaran',
            'manage_pelanggaran',
        ]);

        $pengurusAsrama = Role::firstOrCreate(['name' => 'pengurus_asrama', 'guard_name' => 'web']);
        $pengurusAsrama->syncPermissions([
            'view_santri',
            'view_asrama',
            'manage_asrama',
            'view_absensi',
            'manage_absensi',
            'view_perizinan',
            'view_pelanggaran',
        ]);
    }
}