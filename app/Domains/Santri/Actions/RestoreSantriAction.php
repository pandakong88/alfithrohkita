<?php

namespace App\Domains\Santri\Actions;

use App\Models\Santri;
use App\Domains\Shared\Actions\LogActivityAction;
use Illuminate\Support\Facades\DB;

class RestoreSantriAction
{
    public function __construct(
        protected LogActivityAction $logActivity
    ) {}

    public function execute(Santri $santri): void
    {
        DB::transaction(function () use ($santri) {

            $santri->restore();

            $this->logActivity->execute(
                event: 'santri.restored',
                subject: $santri,
                description: 'Merestore santri'
            );
        });
    }
}