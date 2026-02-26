<?php

namespace App\Domains\Santri\Actions;

use App\Models\Santri;
use App\Models\SantriSnapshot;
use App\Models\SantriSnapshotBatch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommitSantriSnapshotAction
{
    public function execute(SantriSnapshotBatch $batch): void
    {
        if ($batch->status === 'committed') {
            throw new \Exception('Snapshot sudah pernah di-commit.');
        }

        DB::transaction(function () use ($batch) {

            foreach ($batch->rows()
                           ->where('is_valid', true)
                           ->get() as $row) {

                $data = $row->payload;

                $santri = Santri::where('pondok_id', $batch->pondok_id)
                    ->where('nis', $data['nis'])
                    ->first();

                if (!$santri) {
                    continue;
                }

                SantriSnapshot::updateOrCreate(
                    [
                        'pondok_id'     => $batch->pondok_id,
                        'santri_id'     => $santri->id,
                        'snapshot_date' => $batch->snapshot_date,
                    ],
                    [
                        'status'     => $data['status'],
                        'kelas'      => $data['kelas'],
                        'catatan'    => $data['catatan'],
                        'created_by' => Auth::id(),
                    ]
                );
            }

            $batch->update([
                'status'       => 'committed',
                'committed_at' => now(),
                'committed_by' => Auth::id(),
            ]);
        });
    }
}