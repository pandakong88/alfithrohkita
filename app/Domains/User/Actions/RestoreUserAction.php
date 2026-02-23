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

    public function execute(int $userId): User
    {
        $user = User::onlyTrashed()
            ->where('pondok_id', auth()->user()->pondok_id)
            ->where('id', $userId)
            ->firstOrFail();

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
