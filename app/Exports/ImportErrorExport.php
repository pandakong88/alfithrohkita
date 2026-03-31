<?php

namespace App\Exports;

use App\Models\ImportBatch;
use Maatwebsite\Excel\Concerns\FromCollection;

class ImportErrorExport implements FromCollection
{
    protected $batchId;

    public function __construct($batchId)
    {
        $this->batchId = $batchId;
    }

    public function collection()
    {
        $batch = ImportBatch::with('rows')->findOrFail($this->batchId);

        $data = collect();

        $headerAdded = false;

        foreach ($batch->rows->where('is_valid', false) as $row) {

            $payload = $row->payload;

            // buat header dari payload pertama
            if (!$headerAdded) {

                $headers = array_keys($payload);
                $headers[] = 'error';

                $data->push($headers);

                $headerAdded = true;
            }

            $line = array_values($payload);

            $line[] = implode(', ', $row->errors ?? []);

            $data->push($line);
        }

        return $data;
    }
}
