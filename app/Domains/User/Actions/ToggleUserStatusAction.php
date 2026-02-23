<?php

namespace App\Domains\User\Actions;

use App\Domains\Shared\Actions\LogActivityAction;
use App\Models\User;

class ToggleUserStatusAction
{
    public function __construct(
        protected LogActivityAction $logger
    ) {}

    public function execute(User $user): User
    {
        if ($user->hasRole('super_admin')) {
            abort(403);
        }

        if ($user->pondok_id !== auth()->user()->pondok_id) {
            abort(403);
        }

        $oldStatus = $user->is_active;

        $user->update([
            'is_active' => !$user->is_active
        ]);

        $this->logger->execute(
            event: 'status_changed',
            description: "Mengubah status user {$user->name}",
            subject: $user,
            oldValues: ['is_active' => $oldStatus],
            newValues: ['is_active' => $user->is_active]
        );

        return $user;
    }
}
