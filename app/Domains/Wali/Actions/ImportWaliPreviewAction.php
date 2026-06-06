<?php

namespace App\Domains\Wali\Actions;

use App\Models\Wali;
use App\Models\WaliImportBatch;
use App\Models\WaliImportRow;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ImportWaliPreviewAction
{
    public function execute($file): WaliImportBatch
    {
        return DB::transaction(function () use ($file) {
            $user = auth()->user();

            /*
            |--------------------------------------------------------------------------
            | 1️⃣ Buat Batch Import
            |--------------------------------------------------------------------------
            */
            $batch = WaliImportBatch::create([
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
            $seenNiks = [];
            $seenNoHps = [];

            foreach ($rows->skip(1) as $index => $row) {
                $payload = [];
                $errors  = [];

                foreach ($header as $colIndex => $columnName) {
                    if (!$columnName) {
                        continue;
                    }

                    $value = $row[$colIndex] ?? null;

                    if ($value !== null && $value !== '') {
                        $valueStr = trim((string) $value);

                        // Fix scientific notation for NIK & No HP
                        if (($columnName === 'nik' || $columnName === 'no_hp') && is_numeric($valueStr)) {
                            if (strpos(strtolower($valueStr), 'e') !== false) {
                                $valueStr = number_format((float)$valueStr, 0, '', '');
                            } elseif (is_float($value) && $value == (int)$value) {
                                $valueStr = (string)(int)$value;
                            }
                        }

                        // Normalize phone number
                        if ($columnName === 'no_hp') {
                            $valueStr = preg_replace('/[^0-9]/', '', $valueStr);
                            if (str_starts_with($valueStr, '628')) {
                                $valueStr = '0' . substr($valueStr, 2);
                            } elseif (str_starts_with($valueStr, '8') && strlen($valueStr) >= 9 && strlen($valueStr) <= 12) {
                                $valueStr = '0' . $valueStr;
                            }
                        }

                        $payload[$columnName] = $valueStr;
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
                if (empty($payload['nama'] ?? null)) {
                    $errors['nama'] = 'Nama wajib diisi';
                }

                // Validate NIK format
                if (!empty($payload['nik'])) {
                    if (!preg_match('/^[0-9]{16}$/', $payload['nik'])) {
                        $errors['nik'] = 'NIK harus berupa 16 digit angka';
                    }
                }

                // Validate Phone format
                if (!empty($payload['no_hp'])) {
                    if (!preg_match('/^08[0-9]{8,13}$/', $payload['no_hp'])) {
                        $errors['no_hp'] = 'No HP harus berupa nomor ponsel Indonesia yang valid (dimulai dengan 08, 10-15 digit)';
                    }
                }

                // Check NIK duplicates in Excel
                if (!empty($payload['nik']) && !isset($errors['nik'])) {
                    if (in_array($payload['nik'], $seenNiks)) {
                        $errors['nik'] = 'NIK ganda ditemukan dalam file Excel';
                    } else {
                        $seenNiks[] = $payload['nik'];
                    }
                }

                // Check No HP duplicates in Excel
                if (!empty($payload['no_hp']) && !isset($errors['no_hp'])) {
                    if (in_array($payload['no_hp'], $seenNoHps)) {
                        $errors['no_hp'] = 'No HP ganda ditemukan dalam file Excel';
                    } else {
                        $seenNoHps[] = $payload['no_hp'];
                    }
                }

                $isValid = empty($errors);

                /*
                |--------------------------------------------------------------------------
                | 6️⃣ Tentukan Mode (Insert / Update)
                |--------------------------------------------------------------------------
                */
                $mode = 'insert';

                if (!$isValid) {
                    $mode = 'error';
                } else {
                    $existingWali = null;

                    if (!empty($payload['nik'])) {
                        $existingWali = Wali::where('pondok_id', $user->pondok_id)
                            ->where('nik', $payload['nik'])
                            ->first();
                    }

                    if (!$existingWali && !empty($payload['no_hp'])) {
                        $existingWali = Wali::where('pondok_id', $user->pondok_id)
                            ->where('no_hp', $payload['no_hp'])
                            ->first();
                    }

                    if ($existingWali) {
                        $mode = 'update';
                    } else {
                        $mode = 'insert';
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | 7️⃣ Simpan Import Row
                |--------------------------------------------------------------------------
                */
                WaliImportRow::create([
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
