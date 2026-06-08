<?php

namespace App\Domains\User\Actions;

use App\Domains\Shared\Actions\LogActivityAction;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateUserAction
{
    public function __construct(
        protected LogActivityAction $logger
    ) {}

    public function execute(User $user, array $data): User
    {
        if ($user->isAdminPondok() && !auth()->user()->hasAdminAccess()) {
            abort(403, 'Anda tidak memiliki wewenang untuk memodifikasi Admin Pondok.');
        }

        $oldValues = [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name')->toArray(),
        ];

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (!empty($data['password'])) {
            $user->update([
                'password' => Hash::make($data['password'])
            ]);
        }

        $role = Role::where('id', $data['role_id'])
            ->where('pondok_id', auth()->user()->pondok_id)
            ->firstOrFail();

        if (\Illuminate\Support\Str::startsWith($role->name, 'admin_pondok') && !auth()->user()->hasAdminAccess()) {
            abort(403, 'Anda tidak memiliki wewenang untuk memberikan peran Admin Pondok.');
        }

        $user->syncRoles([$role]);

        $newValues = [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => [$role->name],
        ];

        $this->logger->execute(
            event: 'updated',
            description: "Mengupdate user {$user->name}",
            subject: $user,
            oldValues: $oldValues,
            newValues: $newValues
        );

        return $user;
    }
}
