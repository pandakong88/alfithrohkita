<?php

namespace App\Domains\Perizinan\Actions;

use App\Models\TemplatePerizinan;
use App\Domains\Shared\Actions\LogActivityAction;
use Illuminate\Support\Facades\DB;

class DeleteTemplatePerizinanAction
{
    public function __construct(
        protected LogActivityAction $logActivity
    ) {}

    public function execute(TemplatePerizinan $template): void
    {
        DB::transaction(function () use ($template) {

            $this->logActivity->execute(
                event: 'template_perizinan.deleted',
                subject: $template,
                description: 'Menghapus template perizinan',
                oldValues: $template->getAttributes()
            );

            $template->delete();
        });
    }
}