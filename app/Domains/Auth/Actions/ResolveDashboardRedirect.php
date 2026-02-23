<?php

namespace App\Domains\Auth\Actions;

use App\Models\User;

class ResolveDashboardRedirect
{
    public function execute(User $user): string
    {
        if ($user->hasRole('super_admin')) {
            return '/super-admin/dashboard';
        }

        return '/dashboard';
    }
}
