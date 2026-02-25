<?php

namespace App\Domains\Santri\Actions;

use App\Models\Santri;
use App\Domains\Shared\Actions\LogActivityAction;
use Illuminate\Support\Facades\DB;

class DeleteSantriAction
{
    public function __construct(
        protected LogActivityAction $logActivity
    ) {}

    public function execute(Santri $santri): void
    {
        DB::transaction(function () use ($santri) {

            $oldValues = $santri->toArray();

            $santri->delete();

            $this->logActivity->execute(
                event: 'santri.deleted',
                subject: $santri,
                description: 'Menghapus santri',
                oldValues: $oldValues
            );
        });
    }
}