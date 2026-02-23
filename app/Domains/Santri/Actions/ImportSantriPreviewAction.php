<?php

namespace App\Domains\Santri\Actions;

use App\Models\Santri;
// use App\Domains\Santri\Models\Wali;
use App\Models\SantriImportBatch;
use App\Models\SantriImportRow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ImportSantriPreviewAction
{
    public function execute($file): SantriImportBatch
    {
        return DB::transaction(function () use ($file) {

            $user = Auth::user();
            $pondokId = $user->pondok_id;

            // 1️⃣ Buat batch dulu
            $batch = SantriImportBatch::create([
                'pondok_id'   => $pondokId,
                'uploaded_by' => $user->id,
                'filename'    => $file->getClientOriginalName(),
                'status'      => 'preview',
            ]);

            // 2️⃣ Parse Excel jadi collection
            $rows = Excel::toCollection(null, $file)->first();

            $total = 0;
            $valid = 0;
            $invalid = 0;

            foreach ($rows as $index => $row) {

                $total++;
                $rowNumber = $index + 1;

                $payload = [
                    'nis'            => $row[0] ?? null,
                    'nama_lengkap'   => $row[1] ?? null,
                    'jenis_kelamin'  => $row[2] ?? null,
                    'nama_wali'      => $row[3] ?? null,
                    'no_hp_wali'     => $row[4] ?? null,
                    'tanggal_masuk'  => $row[5] ?? null,
                ];

                $errors = [];

                // =====================
                // VALIDASI SANTRI
                // =====================

                if (!$payload['nis']) {
                    $errors[] = 'NIS wajib diisi.';
                } else {
                    $exists = Santri::where('pondok_id', $pondokId)
                        ->where('nis', $payload['nis'])
                        ->exists();

                    if ($exists) {
                        $errors[] = 'NIS sudah ada.';
                    }
                }

                if (!$payload['nama_lengkap']) {
                    $errors[] = 'Nama wajib diisi.';
                }

                if (!in_array($payload['jenis_kelamin'], ['L', 'P'])) {
                    $errors[] = 'Jenis kelamin harus L atau P.';
                }

                if (!$payload['no_hp_wali']) {
                    $errors[] = 'No HP wali wajib diisi.';
                }

                $isValid = empty($errors);

                if ($isValid) {
                    $valid++;
                } else {
                    $invalid++;
                }

                SantriImportRow::create([
                    'batch_id'  => $batch->id,
                    'row_number'=> $rowNumber,
                    'payload'   => $payload,
                    'errors'    => $errors ?: null,
                    'is_valid'  => $isValid,
                ]);
            }

            // Update batch summary
            $batch->update([
                'total_rows'   => $total,
                'valid_rows'   => $valid,
                'invalid_rows' => $invalid,
            ]);

            return $batch->fresh();
        });
    }
}