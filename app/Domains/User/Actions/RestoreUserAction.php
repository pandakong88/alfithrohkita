<?php

namespace App\Domains\User\Actions;

use App\Domains\Shared\Actions\LogActivityAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RestoreUserAction
{
    public function __construct(
        protected LogActivityAction $logger
    ) {}

    public function execute(User $user): User
    {
        if ($user->isAdminPondok() && !auth()->user()->hasAdminAccess()) {
            abort(403, 'Anda tidak memiliki wewenang untuk memodifikasi Admin Pondok.');
        }

        DB::transaction(function () use ($user) {

            $user->restore();

            $this->logger->execute(
                event: 'restored',
                description: "Merestore user {$user->name}",
                subject: $user
            );
        });

        return $user;
    }
}