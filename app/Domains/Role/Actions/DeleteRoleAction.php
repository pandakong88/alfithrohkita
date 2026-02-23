<?php

namespace App\Domains\Role\Actions;

use App\Models\Role;

class DeleteRoleAction
{
    public function execute(Role $role): void
    {
        // Jangan hapus role sistem
        if (in_array($role->name, ['super_admin','admin_pondok'])) {
            abort(403);
        }

        $role->delete();
    }
}
