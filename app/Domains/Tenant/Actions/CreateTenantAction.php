<?php

namespace App\Domains\Tenant\Actions;

use App\Models\Pondok;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\ActivityLog;
use App\Domains\Tenant\DTO\CreateTenantData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class CreateTenantAction
{
    public function execute(CreateTenantData $data): Pondok
    {
        $logoPath = null;

        try {
            return DB::transaction(function () use ($data, &$logoPath) {

                // 1️⃣ Upload Logo (jika ada)
                if ($data->logo) {
                    $logoPath = $data->logo->store(
                        'pondok/logo',
                        'public'
                    );
                }

                // 2️⃣ Buat Pondok
                $pondok = Pondok::create([
                    'name' => $data->name,
                    'slug' => Str::slug($data->name) . '-' . uniqid(),
                    'address' => $data->address,
                    'phone' => $data->phone,
                    'logo' => $logoPath,
                    'is_active' => true,
                ]);

                // 3️⃣ Buat User Admin Pondok
                $user = User::create([
                    'name' => $data->admin_name,
                    'email' => $data->admin_email,
                    'password' => Hash::make($data->admin_password),
                    'pondok_id' => $pondok->id,
                    'is_active' => true,
                ]);

                // 4️⃣ Buat Role khusus pondok
                $roleName = 'admin_pondok_' . $pondok->id;

                $adminRole = Role::firstOrCreate([
                    'name' => $roleName,
                    'guard_name' => 'web',
                    'pondok_id' => $pondok->id,
                ]);

                // 5️⃣ Assign semua permission
                $permissions = Permission::all();
                $adminRole->syncPermissions($permissions);

                // 6️⃣ Assign role ke user
                $user->assignRole($roleName);

                // 7️⃣ Activity Log
                ActivityLog::create([
                    'pondok_id' => $pondok->id,
                    'causer_id' => auth()->id(),
                    'event' => 'create',
                    'subject_type' => Pondok::class,
                    'subject_id' => $pondok->id,
                    'description' => 'Membuat pondok baru',
                    'old_values' => null,
                    'new_values' => $pondok->toArray(),
                    'meta' => [
                        'admin_name' => $data->admin_name,
                        'admin_email' => $data->admin_email,
                    ],
                ]);

                return $pondok;
            });

        } catch (Throwable $e) {

            // ❌ Jika gagal → hapus logo yang sudah terupload
            if ($logoPath && Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }

            throw $e;
        }
    }
}