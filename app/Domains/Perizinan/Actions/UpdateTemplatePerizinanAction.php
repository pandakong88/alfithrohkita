<?php

namespace App\Domains\Perizinan\Actions;

use App\Models\TemplatePerizinan;
use App\Domains\Perizinan\DTO\UpdateTemplatePerizinanData;
use App\Domains\Shared\Actions\LogActivityAction;
use Illuminate\Support\Facades\DB;

class UpdateTemplatePerizinanAction
{
    public function __construct(
        protected LogActivityAction $logActivity
    ) {}

    public function execute(
        TemplatePerizinan $template,
        UpdateTemplatePerizinanData $data
    ): TemplatePerizinan {

        return DB::transaction(function () use ($template, $data) {

            $oldValues = $template->getOriginal();

            $template->update([
                'nama' => $data->nama,
                'deskripsi' => $data->deskripsi,
                'format_surat' => $data->format_surat,
                'layout_print' => $data->layout_print,
                'is_active' => $data->is_active,
            ]);

            $this->logActivity->execute(
                event: 'template_perizinan.updated',
                subject: $template,
                description: 'Memperbarui template perizinan',
                oldValues: $oldValues,
                newValues: $template->getAttributes()
            );

            return $template;
        });
    }
}