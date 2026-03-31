<?php

namespace App\Domains\Import\Actions;

use App\Models\ImportBatch;
use App\Models\ImportRow;
use App\Models\ImportField;
use App\Models\Santri;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PreviewImport;

class PreviewImportAction
{
    public function execute($pondokId, $userId, $templateId, $file, $modeMissing, $modeExisting)
    {

        $import = new PreviewImport();
        
        Excel::import($import, $file);
        
        $rows = $import->rows;

        if (!$rows || $rows->isEmpty()) {
            return null;
        }

        // Create batch
        $batch = ImportBatch::create([
            'pondok_id' => $pondokId,
            'template_id' => $templateId,
            'uploaded_by' => $userId,
            'filename' => $file->getClientOriginalName(),
            'mode_missing_nis' => $modeMissing,
            'mode_existing_nis' => $modeExisting,
        ]);

        $header = $rows->first()
            ->map(fn($h) => strtolower(trim($h)))
            ->toArray();

        $fields = ImportField::whereIn('field_key', $header)->get();
        $fieldMap = $fields->keyBy('field_key');

        $total = 0;
        $valid = 0;
        $invalid = 0;

        foreach ($rows->skip(1) as $index => $row) {

            $total++;

            $payload = [];
            $errors = [];
            $mode = null;

            foreach ($header as $i => $column) {

                if (!isset($fieldMap[$column])) {
                    continue;
                }

                $value = $row[$i] ?? null;

                // paksa string untuk nomor panjang
                if (is_numeric($value)) {
                    $value = (string) $value;
                }

                $payload[$column] = $value;
            }

            // Validasi NIS
            if (empty($payload['nis'])) {
                $errors[] = 'NIS kosong';
                $mode = 'error';
            }

            $santri = null;

            if (!empty($payload['nis'])) {

                $santri = Santri::where('pondok_id', $pondokId)
                    ->where('nis', $payload['nis'])
                    ->first();

                if ($santri) {

                    if ($modeExisting === 'skip') {
                        $mode = 'skip';
                    } else {
                        $mode = 'update';
                    }

                } else {

                    if ($modeMissing === 'create') {
                        $mode = 'insert';
                    } elseif ($modeMissing === 'skip') {
                        $mode = 'skip';
                    } else {
                        $mode = 'error';
                        $errors[] = 'NIS tidak ditemukan';
                    }

                }
            }

            $isValid = empty($errors);

            ImportRow::create([
                'batch_id' => $batch->id,
                'row_number' => $index + 2,
                'payload' => $payload,
                'errors' => $errors ?: null,
                'mode' => $mode,
                'is_valid' => $isValid,
            ]);

            if ($isValid) {
                $valid++;
            } else {
                $invalid++;
            }
        }

        $batch->update([
            'total_rows' => $total,
            'valid_rows' => $valid,
            'invalid_rows' => $invalid,
        ]);

        return $batch;
    }
}