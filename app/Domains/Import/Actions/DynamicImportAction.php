<?php

namespace App\Domains\Import\Actions;

use App\Models\Santri;
use App\Models\Wali;
use App\Models\ImportField;
// use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DynamicImportAction
{
    public function execute($file)
    {

        $rows = Excel::toCollection(null, $file)->first();

        if (!$rows || $rows->isEmpty()) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Header Excel
        |--------------------------------------------------------------------------
        */

        $header = $rows->first()
            ->map(fn ($h) => strtolower(trim($h)))
            ->toArray();


        /*
        |--------------------------------------------------------------------------
        | Mapping Field
        |--------------------------------------------------------------------------
        */

        $fields = ImportField::whereIn('field_key', $header)->get();

        $fieldMap = $fields->keyBy('field_key');


        /*
        |--------------------------------------------------------------------------
        | Loop Row
        |--------------------------------------------------------------------------
        */

        foreach ($rows->skip(1) as $row) {

            $payload = [];

            foreach ($header as $index => $column) {

                if (!isset($fieldMap[$column])) {
                    continue;
                }

                $payload[$column] = $row[$index] ?? null;
            }


            $this->processRow($payload);
        }
    }



    private function processRow($payload)
    {

        if (empty($payload['nis'])) {
            return;
        }

        $santri = Santri::where('pondok_id', auth()->user()->pondok_id)
            ->where('nis', $payload['nis'])
            ->first();

        if (!$santri) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Update Wali
        |--------------------------------------------------------------------------
        */

        if (
            isset($payload['wali_nama']) ||
            isset($payload['wali_no_hp'])
        ) {

            $wali = Wali::updateOrCreate(

                [
                    'pondok_id' => $santri->pondok_id,
                    'nik' => $payload['wali_nik'] ?? null
                ],

                [
                    'nama' => $payload['wali_nama'] ?? null,
                    'no_hp' => $payload['wali_no_hp'] ?? null
                ]
            );

            $santri->update([
                'wali_id' => $wali->id
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Update Santri
        |--------------------------------------------------------------------------
        */

        $santriData = [];

        if (isset($payload['alamat'])) {
            $santriData['alamat'] = $payload['alamat'];
        }

        if (isset($payload['no_hp'])) {
            $santriData['no_hp'] = $payload['no_hp'];
        }

        if (!empty($santriData)) {
            $santri->update($santriData);
        }

    }
}