<?php

namespace App\Domains\Role\Actions;

use App\Models\Role;

class UpdateRoleAction
{
    public function execute(Role $role, array $data): Role
    {
        $role->update([
            'name' => $data['name']
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        return $role;
    }
}
