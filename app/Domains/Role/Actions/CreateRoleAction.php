<?php

namespace App\Domains\Role\Actions;

use App\Domains\Role\DTO\CreateRoleData;
use App\Models\ActivityLog;
use App\Models\Role;
use Illuminate\Support\Str;


class CreateRoleAction
{

    public function execute(CreateRoleData $data): Role {
        $pondokId = auth()->user()->pondok_id;

        // slugify dan lower case
        $baseName = Str::slug($data->name, '_'); 
        // contoh: "Pengasuh Dua Puluh" → pengasuh_dua_puluh

        $roleName = $baseName . '_' . $pondokId;

        $role = Role::create([
            'name' => $roleName,
            'guard_name' => 'web',
            'pondok_id' => $pondokId,
        ]);

        $role->syncPermissions($data->permissions);

        ActivityLog::create([
            'pondok_id' => $pondokId,
            'causer_id' => auth()->id(),
            'event' => 'create',
            'subject_type' => Role::class,
            'subject_id' => $role->id,
            'description' => 'Membuat role baru',
            'old_values' => null,
            'new_values' => [
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ],
        ]);
        
        return $role;
    }
}