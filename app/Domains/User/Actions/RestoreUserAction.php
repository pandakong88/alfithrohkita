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