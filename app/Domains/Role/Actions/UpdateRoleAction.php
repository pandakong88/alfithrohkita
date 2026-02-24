<?php

namespace App\Domains\Role\Actions;

use App\Models\ActivityLog;
use App\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;



class UpdateRoleAction
{
    public function execute(Role $role, array $data): Role
    {
        $pondokId = $role->pondok_id;

        // 🔥 Normalisasi nama
        $baseName = Str::slug($data['name'], '_');
        $newName  = $baseName . '_' . $pondokId;

        // 🔥 Cegah duplikat dalam pondok yang sama
        $exists = Role::where('name', $newName)
            ->where('pondok_id', $pondokId)
            ->where('id', '!=', $role->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => 'Role dengan nama tersebut sudah ada di pondok ini.'
            ]);
        }

        $role->update([
            'name' => $newName
        ]);

        $role->syncPermissions($data['permissions'] ?? []);


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