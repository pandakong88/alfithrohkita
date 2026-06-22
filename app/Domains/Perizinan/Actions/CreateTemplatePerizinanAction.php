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
            $keysToCheck = [];
            $isAssoc = false;
            if (is_array($data->required_variables)) {
                foreach ($data->required_variables as $key => $val) {
                    $item = is_string($key) ? $key : $val;
                    if (str_starts_with($item, 'custom_variables.') || in_array($item, ['qr_code', 'kode_surat'])) {
                        continue;
                    }
                    if (is_string($key)) {
                        $keysToCheck[] = $key;
                        $isAssoc = true;
                    } else {
                        $keysToCheck[] = $val;
                    }
                }
            }

            $validKeys = \App\Models\TemplateVariable::whereIn('key', $keysToCheck)
                ->pluck('key')
                ->toArray();
    
            if (count($validKeys) !== count($keysToCheck)) {
                throw new \Exception('Beberapa variable tidak valid');
            }

            $savedVariables = [];
            if ($isAssoc) {
                foreach ($data->required_variables as $key => $val) {
                    if (in_array($key, $validKeys) || str_starts_with($key, 'custom_variables.') || in_array($key, ['qr_code', 'kode_surat'])) {
                        $savedVariables[$key] = $val;
                    }
                }
            } else {
                $customKeys = [];
                foreach ($data->required_variables as $v) {
                    if (str_starts_with($v, 'custom_variables.') || in_array($v, ['qr_code', 'kode_surat'])) {
                        $customKeys[] = $v;
                    }
                }
                $savedVariables = array_merge($validKeys, $customKeys);
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
                'source_type' => $data->file_pdf ? 'upload_pdf' : 'html',
                'format_surat' => $data->format_surat,
                'layout_print' => $data->layout_print,
                'required_variables' => $savedVariables,
                'rules' => $data->rules,
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