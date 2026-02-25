<?php

namespace App\Domains\Wali\Actions;

use App\Models\Wali;
use App\Domains\Shared\Actions\LogActivityAction;
use Illuminate\Support\Facades\DB;

class RestoreWaliAction
{
    public function __construct(
        protected LogActivityAction $logActivity
    ) {}

    public function execute(Wali $wali): void
    {
        DB::transaction(function () use ($wali) {

            $wali->restore();

            $this->logActivity->execute(
                event: 'wali.restored',
                subject: $wali,
                description: 'Merestore wali',
                oldValues: null,
                newValues: $wali->fresh()->toArray()
            );
        });
    }
}