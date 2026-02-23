<?php

namespace App\Domains\User\Actions;

use App\Domains\Shared\Actions\LogActivityAction;
use App\Domains\User\DTO\CreateUserData;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUserAction
{
    public function __construct(
        protected LogActivityAction $logger
    ) {}

    public function execute(CreateUserData $data): User
    {
        $user = User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password),
            'pondok_id' => auth()->user()->pondok_id,
            'is_active' => true,
        ]);

        $role = Role::where('id', $data->role_id)
            ->where('pondok_id', auth()->user()->pondok_id)
            ->firstOrFail();

        $user->assignRole($role);

        // 🔥 Audit Log
        $this->logger->execute(
            event: 'created',
            description: "Membuat user {$user->name}",
            subject: $user,
            newValues: [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $role->name,
            ]
        );

        return $user;
    }
}
