<?php

namespace App\Domains\Perizinan\Actions;

use App\Models\TemplatePerizinan;
use App\Domains\Perizinan\DTO\CreateTemplatePerizinanData;
use App\Domains\Shared\Actions\LogActivityAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateTemplatePerizinanAction
{
    public function __construct(
        protected LogActivityAction $logActivity
    ) {}

    public function execute(CreateTemplatePerizinanData $data): TemplatePerizinan
    {
        return DB::transaction(function () use ($data) {
    
            $user = Auth::user();
    
            if (!$user) {
                throw new \Exception('User tidak terautentikasi');
            }
    
            $pondokId = $user->pondok_id;
    
            // 🔥 slug selalu dari nama
            $baseSlug = Str::slug($data->nama);
            $slug = $baseSlug;
            $count = 1;
    
            while (
                TemplatePerizinan::where('pondok_id', $pondokId)
                    ->where('slug', $slug)
                    ->exists()
            ) {
                $slug = $baseSlug . '-' . $count++;
            }
    
            // 🔥 VALIDASI MODE
            if ($data->file_pdf && $data->format_surat) {
                throw new \Exception('Template tidak boleh memiliki PDF dan format_surat sekaligus');
            }
    
            if (!$data->file_pdf && !$data->format_surat) {
                throw new \Exception('Template harus memiliki PDF atau format_surat');
            }
    
            // 🔥 VALIDASI VARIABLE
            $validKeys = \App\Models\TemplateVariable::whereIn('key', $data->required_variables)
                ->pluck('key')
                ->toArray();
    
            if (count($validKeys) !== count($data->required_variables)) {
                throw new \Exception('Beberapa variable tidak valid');
            }
    
            // 🔥 HANDLE DEFAULT
            if ($data->is_default) {
                TemplatePerizinan::where('pondok_id', $pondokId)
                    ->update(['is_default' => false]);
            }
    
            $template = TemplatePerizinan::create([
                'pondok_id' => $pondokId,
                'nama' => $data->nama,
                'slug' => $slug,
                'deskripsi' => $data->deskripsi,
                'format_surat' => $data->format_surat,
                'layout_print' => $data->layout_print,
                'required_variables' => $validKeys,
                'file_pdf' => $data->file_pdf,
                'is_default' => $data->is_default,
                'is_active' => $data->is_active,
                'created_by' => $user->id,
            ]);
    
            $this->logActivity->execute(
                event: 'template_perizinan.created',
                subject: $template,
                description: 'Membuat template perizinan',
                newValues: $template->toArray()
            );
    
            return $template;
        });
    }
}