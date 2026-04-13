<?php

namespace App\Domains\Perizinan\Actions;

use App\Models\Perizinan;
use App\Domains\Shared\Actions\LogActivityAction;
use Illuminate\Support\Facades\DB;

class DeletePerizinanAction
{
    public function __construct(
        protected LogActivityAction $logActivity
    ) {}

    public function execute(Perizinan $perizinan): void
    {
        DB::transaction(function () use ($perizinan) {

            $this->logActivity->execute(
                event: 'perizinan.deleted',
                subject: $perizinan,
                description: 'Menghapus perizinan santri',
                oldValues: $perizinan->getAttributes()
            );

            $perizinan->delete();
        });
    }
}