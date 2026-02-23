<?php

namespace App\Domains\User\Actions;

use App\Domains\Shared\Actions\LogActivityAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteUserAction
{
    public function __construct(
        protected LogActivityAction $logger
    ) {}

    public function execute(User $user): void
    {
        if ($user->hasRole('super_admin')) {
            abort(403, 'Super Admin tidak dapat dihapus.');
        }

        if ($user->id === auth()->id()) {
            abort(403, 'Anda tidak bisa menghapus akun sendiri.');
        }

        if ($user->pondok_id !== auth()->user()->pondok_id) {
            abort(403);
        }

        DB::transaction(function () use ($user) {

            $oldValues = [
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->toArray(),
            ];

            $this->logger->execute(
                event: 'deleted',
                description: "Menghapus user {$user->name}",
                subject: $user,
                oldValues: $oldValues
            );

            $user->delete();
        });
    }
}
