<?php

namespace App\Domains\Perizinan\Actions;

use App\Models\TemplatePerizinan;
use App\Models\TemplateVariable;
use App\Domains\Perizinan\DTO\UpdateTemplatePerizinanData;
use App\Domains\Shared\Actions\LogActivityAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

            // 🔥 VALIDASI MODE
            if ($data->file_pdf && $data->format_surat) {
                throw new \Exception('Tidak boleh menggunakan PDF dan format_surat sekaligus');
            }

            if (!$data->file_pdf && !$data->format_surat) {
                throw new \Exception('Template harus memiliki PDF atau format_surat');
            }

            // 🔥 VALIDASI VARIABLE
            $validKeys = TemplateVariable::whereIn('key', $data->required_variables)
                ->pluck('key')
                ->toArray();

            if (count($validKeys) !== count($data->required_variables)) {
                throw new \Exception('Beberapa variable tidak valid');
            }

            // 🔥 HANDLE DEFAULT
            if ($data->is_default) {
                TemplatePerizinan::where('pondok_id', $template->pondok_id)
                    ->where('id', '!=', $template->id)
                    ->update(['is_default' => false]);
            }

            // 🔥 HANDLE FILE PDF (hapus lama jika ganti)
            $filePath = $template->file_pdf;

            if ($data->file_pdf && $data->file_pdf !== $template->file_pdf) {
                if ($template->file_pdf) {
                    Storage::disk('public')->delete($template->file_pdf);
                }
                $filePath = $data->file_pdf;
            }

            // 🔥 UPDATE
            $template->update([
                'nama' => $data->nama,
                'slug' => Str::slug($data->nama),
                'deskripsi' => $data->deskripsi,
                'format_surat' => $data->format_surat,
                'layout_print' => $data->layout_print,
                'required_variables' => $validKeys,
                'file_pdf' => $filePath,
                'is_active' => $data->is_active,
                'is_default' => $data->is_default,
            ]);

            // 🔥 LOG
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