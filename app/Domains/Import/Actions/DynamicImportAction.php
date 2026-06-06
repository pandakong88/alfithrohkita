<?php

namespace App\Domains\Import\Actions;

use App\Models\Santri;
use App\Models\Wali;
use App\Models\ImportField;
use Maatwebsite\Excel\Facades\Excel;

class DynamicImportAction
{
    public function execute($file)
    {
        $rows = Excel::toCollection(null, $file)->first();

        if (!$rows || $rows->isEmpty()) {
            return;
        }

        $header = $rows->first()
            ->map(fn ($h) => strtolower(trim($h)))
            ->toArray();

        $fields = ImportField::whereIn('field_key', $header)->get();
        $fieldMap = $fields->keyBy('field_key');

        foreach ($rows->skip(1) as $row) {
            $payload = [];

            foreach ($header as $index => $column) {
                if (!isset($fieldMap[$column])) {
                    continue;
                }
                $payload[$column] = $row[$index] ?? null;
            }

            $this->processRow($payload, $fieldMap);
        }
    }

    private function processRow($payload, $fieldMap)
    {
        if (empty($payload['nis'])) {
            return;
        }

        // --------------------------------------------------------------------------
        // 1. Logika Wali (Mencegah Duplikasi)
        // --------------------------------------------------------------------------
        $waliId = null;

        if (!empty($payload['wali_nama'])) {
            // Gunakan No HP sebagai fallback jika NIK kosong agar tidak duplikat
            $searchKey = [];
            if (!empty($payload['wali_nik'])) {
                $searchKey['nik'] = $payload['wali_nik'];
            } elseif (!empty($payload['wali_no_hp'])) {
                $searchKey['no_hp'] = $payload['wali_no_hp'];
            } else {
                // Jika NIK & No HP kosong, cari berdasarkan nama di pondok yang sama
                $searchKey['nama'] = $payload['wali_nama'];
            }
            
            $searchKey['pondok_id'] = auth()->user()->pondok_id;

            $wali = Wali::updateOrCreate(
                $searchKey,
                [
                    'nama' => $payload['wali_nama'],
                    'no_hp' => $payload['wali_no_hp'] ?? null,
                    'nik' => $payload['wali_nik'] ?? null,
                    'pekerjaan' => $payload['wali_pekerjaan'] ?? null,
                ]
            );
            $waliId = $wali->id;
        }

        // --------------------------------------------------------------------------
        // 2. Memisahkan Core Fields vs Custom Fields (JSON)
        // --------------------------------------------------------------------------
        $coreFields = [
            'nis', 'nama_lengkap', 'jenis_kelamin', 'alamat', 'no_hp',
            'wali_nama', 'wali_no_hp', 'wali_nik', 'wali_pekerjaan', 'kelas', 'komplek', 'kamar'
        ];

        $customFieldsData = [];
        foreach ($payload as $key => $value) {
            // Jika field_key tidak terdaftar di coreFields, masukkan ke JSON custom_fields
            if (!in_array($key, $coreFields)) {
                $customFieldsData[$key] = $value;
            }
        }

        // --------------------------------------------------------------------------
        // 3. Smart Upsert (Bisa INSERT baru & bisa UPDATE data lama)
        // --------------------------------------------------------------------------
        $santriData = [
            'nama_lengkap'  => $payload['nama_lengkap'] ?? null,
            'jenis_kelamin' => $payload['jenis_kelamin'] ?? null,
            'alamat'        => $payload['alamat'] ?? null,
            'no_hp'         => $payload['no_hp'] ?? null,
            'custom_fields' => $customFieldsData, // Masuk ke kolom JSON santri
        ];

        // Jika wali ditemukan, hubungkan hubungannya
        if ($waliId) {
            $santriData['wali_id'] = $waliId;
        }

        // Menggunakan updateOrCreate agar jika NIS belum ada -> Auto Insert, jika sudah ada -> Auto Update
        Santri::updateOrCreate(
            [
                'pondok_id' => auth()->user()->pondok_id,
                'nis' => $payload['nis']
            ],
            $santriData
        );
    }
}