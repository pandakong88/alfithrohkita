<?php

namespace App\Domains\Role\Actions;

use App\Models\Role;
use App\Domains\Role\DTO\CreateRoleData;

class CreateRoleAction
{
    public function execute(CreateRoleData $data): Role
    {
        $role = Role::create([
            'name' => $data->name,
            'guard_name' => 'web',
            'pondok_id' => auth()->user()->pondok_id,
        ]);

        $role->syncPermissions($data->permissions);

        return $role;
    }
}
