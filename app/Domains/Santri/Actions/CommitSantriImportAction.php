<?php

namespace App\Domains\Santri\Actions;

use App\Models\Santri;
use App\Models\SantriImportBatch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommitSantriImportAction
{
    public function execute(SantriImportBatch $batch): void
    {
        if ($batch->status === 'committed') {
            throw new \Exception('Batch sudah pernah di-commit.');
        }

        DB::transaction(function () use ($batch) {

            foreach ($batch->rows()
                           ->where('is_valid', true)
                           ->get() as $row) {

                $data = $row->payload;

                Santri::updateOrCreate(
                    [
                        'pondok_id' => $batch->pondok_id,
                        'nis'       => $data['nis'],
                    ],
                    [
                        'nama_lengkap'  => $data['nama_lengkap'],
                        'jenis_kelamin' => $data['jenis_kelamin'],
                        'status'        => $data['status'],
                    ]
                );
            }

            $batch->update([
                'status'       => 'committed',
                'committed_by' => Auth::id(),
                'committed_at' => now(),
            ]);
        });
    }
}