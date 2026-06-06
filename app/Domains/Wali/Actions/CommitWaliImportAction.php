<?php

namespace App\Domains\Wali\Actions;

use App\Models\Wali;
use App\Models\WaliImportBatch;
use Illuminate\Support\Facades\DB;

class CommitWaliImportAction
{
    public function execute(WaliImportBatch $batch)
    {
        DB::transaction(function () use ($batch) {
            $rows = $batch->rows()
                ->where('is_valid', true)
                ->get();

            foreach ($rows as $row) {
                $payload = array_map('trim', $row->payload ?? []);

                $updateAttributes = [
                    'nama' => $payload['nama'],
                    'alamat' => $payload['alamat'] ?? null,
                    'pekerjaan' => $payload['pekerjaan'] ?? null,
                ];

                // If NIK is provided, try to find by NIK
                $existingWali = null;
                if (!empty($payload['nik'])) {
                    $existingWali = Wali::where('pondok_id', $batch->pondok_id)
                        ->where('nik', $payload['nik'])
                        ->first();
                }

                // If not found by NIK and No HP is provided, try to find by No HP
                if (!$existingWali && !empty($payload['no_hp'])) {
                    $existingWali = Wali::where('pondok_id', $batch->pondok_id)
                        ->where('no_hp', $payload['no_hp'])
                        ->first();
                }

                if ($existingWali) {
                    // Update existing
                    $existingWali->update(array_merge([
                        'nik' => $payload['nik'] ?? $existingWali->nik,
                        'no_hp' => $payload['no_hp'] ?? $existingWali->no_hp,
                        'updated_by' => auth()->id(),
                    ], $updateAttributes));
                } else {
                    // Create new
                    Wali::create(array_merge([
                        'pondok_id' => $batch->pondok_id,
                        'nik' => $payload['nik'] ?? null,
                        'no_hp' => $payload['no_hp'] ?? null,
                        'created_by' => auth()->id(),
                    ], $updateAttributes));
                }
            }

            $batch->update([
                'status' => 'committed',
                'committed_by' => auth()->id(),
                'committed_at' => now(),
            ]);
        });
    }
}
