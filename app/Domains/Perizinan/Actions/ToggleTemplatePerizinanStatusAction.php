<?php

namespace App\Domains\Perizinan\Actions;

use App\Models\TemplatePerizinan;
use App\Domains\Shared\Actions\LogActivityAction;
use Illuminate\Support\Facades\DB;

class ToggleTemplatePerizinanStatusAction
{
    public function __construct(
        protected LogActivityAction $logActivity
    ) {}

    public function execute(TemplatePerizinan $template): TemplatePerizinan
    {
        return DB::transaction(function () use ($template) {

            $template->update([
                'is_active' => !$template->is_active
            ]);

            $this->logActivity->execute(
                event: 'template_perizinan.status_changed',
                subject: $template,
                description: 'Mengubah status template perizinan',
                newValues: [
                    'is_active' => $template->is_active
                ]
            );

            return $template;
        });
    }
}