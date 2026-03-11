<?php

namespace App\Domains\Santri\Actions;

use App\Models\Santri;
use App\Models\SantriImportBatch;
use App\Models\SantriImportRow;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ImportSantriPreviewAction
{
    public function execute($file): SantriImportBatch
    {
        return DB::transaction(function () use ($file) {

            $user = auth()->user();

            /*
            |--------------------------------------------------------------------------
            | 1️⃣ Buat Batch Import
            |--------------------------------------------------------------------------
            */

            $batch = SantriImportBatch::create([
                'pondok_id'   => $user->pondok_id,
                'uploaded_by' => $user->id,
                'filename'    => $file->getClientOriginalName(),
                'status'      => 'preview',
            ]);

            /*
            |--------------------------------------------------------------------------
            | 2️⃣ Ambil Data Excel
            |--------------------------------------------------------------------------
            */

            $rows = Excel::toCollection(null, $file)->first();

            if (!$rows || $rows->isEmpty()) {
                return $batch;
            }

            /*
            |--------------------------------------------------------------------------
            | 3️⃣ Normalisasi Header
            |--------------------------------------------------------------------------
            */

            $header = $rows->first()
                ->map(function ($h) {
                    return str_replace(' ', '_', strtolower(trim($h)));
                })
                ->toArray();

            /*
            |--------------------------------------------------------------------------
            | 4️⃣ Loop Semua Row Excel
            |--------------------------------------------------------------------------
            */

            foreach ($rows->skip(1) as $index => $row) {

                $payload = [];
                $errors  = [];

                foreach ($header as $colIndex => $columnName) {

                    if (!$columnName) {
                        continue;
                    }

                    $value = $row[$colIndex] ?? null;

                    if ($value !== null && $value !== '') {
                        $payload[$columnName] = trim((string) $value);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Skip Row Kosong
                |--------------------------------------------------------------------------
                */

                if (empty(array_filter($payload))) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | 5️⃣ Validasi Dasar
                |--------------------------------------------------------------------------
                */

                if (empty($payload['nis'] ?? null)) {
                    $errors['nis'] = 'NIS wajib diisi';
                }

                if (empty($payload['nama_lengkap'] ?? null)) {
                    $errors['nama_lengkap'] = 'Nama lengkap wajib diisi';
                }

                if (!empty($payload['jenis_kelamin'])) {

                    if (!in_array($payload['jenis_kelamin'], ['L', 'P'])) {
                        $errors['jenis_kelamin'] = 'Jenis kelamin harus L atau P';
                    }
                }

                $isValid = empty($errors);

                /*
                |--------------------------------------------------------------------------
                | 6️⃣ Tentukan Mode
                |--------------------------------------------------------------------------
                */

                $mode = 'insert';

                if (!$isValid) {

                    $mode = 'error';

                } else {

                    $exists = Santri::where('pondok_id', $user->pondok_id)
                        ->where('nis', $payload['nis'])
                        ->exists();

                    $mode = $exists ? 'update' : 'insert';
                }

                /*
                |--------------------------------------------------------------------------
                | 7️⃣ Simpan Import Row
                |--------------------------------------------------------------------------
                */

                SantriImportRow::create([
                    'batch_id'   => $batch->id,
                    'row_number' => $index + 2,
                    'payload'    => $payload,
                    'errors'     => $errors ?: null,
                    'is_valid'   => $isValid,
                    'mode'       => $mode,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 8️⃣ Update Statistik Batch
            |--------------------------------------------------------------------------
            */

            $totalRows = $batch->rows()->count();

            $validRows = $batch->rows()
                ->where('is_valid', true)
                ->count();

            $invalidRows = $batch->rows()
                ->where('is_valid', false)
                ->count();

            $batch->update([
                'total_rows'   => $totalRows,
                'valid_rows'   => $validRows,
                'invalid_rows' => $invalidRows,
            ]);

            return $batch;
        });
    }
}