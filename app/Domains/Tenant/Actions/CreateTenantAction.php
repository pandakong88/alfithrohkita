<?php

namespace App\Domains\Tenant\Actions;

use App\Models\Pondok;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Domains\Tenant\DTO\CreateTenantData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateTenantAction
{
    public function execute(CreateTenantData $data): Pondok
    {
        return DB::transaction(function () use ($data) {

            // 1️⃣ Buat Pondok
            $pondok = Pondok::create([
                'name' => $data->name,
                'slug' => Str::slug($data->name) . '-' . uniqid(),
                'address' => $data->address,
                'phone' => $data->phone,
                'is_active' => true,
            ]);

            // 2️⃣ Buat User Admin Pondok
            $user = User::create([
                'name' => $data->admin_name,
                'email' => $data->admin_email,
                'password' => Hash::make($data->admin_password),
                'pondok_id' => $pondok->id,
                'is_active' => true,
            ]);

            // 3️⃣ Buat Role admin_pondok khusus pondok ini
            $adminRole = Role::create([
                'name' => 'admin_pondok',
                'guard_name' => 'web',
                'pondok_id' => $pondok->id,
            ]);

            // 4️⃣ Assign semua permission ke role ini
            $permissions = Permission::all();
            $adminRole->syncPermissions($permissions);

            // 5️⃣ Assign role ke user
            $user->assignRole($adminRole);

            return $pondok;
        });
    }
}
