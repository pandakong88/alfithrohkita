<?php

namespace App\Domains\Santri\Actions;

use App\Models\Santri;
use App\Models\SantriSnapshotBatch;
use App\Models\SantriSnapshotRow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ImportSantriSnapshotPreviewAction
{
    public function execute($file, $snapshotDate): SantriSnapshotBatch
    {
        return DB::transaction(function () use ($file, $snapshotDate) {

            $user = Auth::user();
            $pondokId = $user->pondok_id;

            // 1️⃣ Buat batch
            $batch = SantriSnapshotBatch::create([
                'pondok_id'    => $pondokId,
                'uploaded_by'  => $user->id,
                'snapshot_date'=> $snapshotDate,
                'filename'     => $file->getClientOriginalName(),
                'status'       => 'preview',
            ]);

            $rows = Excel::toCollection(null, $file)->first();

            $total = 0;
            $valid = 0;
            $invalid = 0;

            foreach ($rows->skip(1) as $index => $row) {

                $total++;
                $rowNumber = $index + 2;

                $status = strtolower(trim($row[1] ?? ''));

                // normalisasi
                if ($status === 'inactive') {
                    $status = 'nonaktif';
                }

                $allowedStatus = ['active','nonaktif','lulus','keluar'];

                $payload = [
                    'nis'     => $row[0] ?? null,
                    'status'  => $status,
                    'kelas'   => $row[2] ?? null,
                    'catatan' => $row[3] ?? null,
                ];

                $errors = [];

                // ====================
                // VALIDASI
                // ====================

                if (!$payload['nis']) {
                    $errors[] = 'NIS wajib diisi.';
                }

                if (!in_array($payload['status'], $allowedStatus)) {
                    $errors[] = 'Status tidak valid.';
                }

                // cek santri ada atau tidak
                $santri = null;
                if ($payload['nis']) {
                    $santri = Santri::where('pondok_id', $pondokId)
                        ->where('nis', $payload['nis'])
                        ->first();

                    if (!$santri) {
                        $errors[] = 'Santri tidak ditemukan.';
                    }
                }

                $isValid = empty($errors);

                if ($isValid) {
                    $valid++;
                } else {
                    $invalid++;
                }

                SantriSnapshotRow::create([
                    'batch_id'   => $batch->id,
                    'row_number' => $rowNumber,
                    'payload'    => $payload,
                    'errors'     => $errors ?: null,
                    'is_valid'   => $isValid,
                ]);
            }

            $batch->update([
                'total_rows'   => $total,
                'valid_rows'   => $valid,
                'invalid_rows' => $invalid,
            ]);

            return $batch->fresh();
        });
    }
}