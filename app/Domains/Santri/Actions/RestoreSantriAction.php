<?php

namespace App\Domains\Santri\Actions;

use App\Models\Santri;
use App\Domains\Shared\Actions\LogActivityAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RestoreSantriAction
{
    public function __construct(
        protected LogActivityAction $logActivity
    ) {}

    public function execute(Santri $santri): Santri
    {
        return DB::transaction(function () use ($santri) {

            $user = Auth::user();

            if ($santri->pondok_id !== $user->pondok_id) {
                abort(403);
            }

            $santri->restore();

            $this->logActivity->execute(
                event: 'santri.restored',
                subject: $santri,
                description: 'Restore data santri',
                oldValues: null,
                newValues: $santri->fresh()->toArray(),
                meta: [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]
            );

            return $santri->fresh();
        });
    }
}
