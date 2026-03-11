<?php

namespace App\Domains\Santri\Actions;

use App\Models\Santri;
use App\Models\Kelas;
use App\Models\Komplek;
use App\Models\Kamar;
use App\Models\Wali;
use App\Models\SantriImportBatch;
use Illuminate\Support\Facades\DB;

class CommitSantriImportAction
{
    public function execute(SantriImportBatch $batch)
    {
        DB::transaction(function () use ($batch) {

            $rows = $batch->rows()
                ->where('is_valid', true)
                ->get();

            foreach ($rows as $row) {

                // Normalisasi payload dari Excel
                $payload = array_map('trim', $row->payload ?? []);

                /*
                |--------------------------------------------------------------------------
                | Resolve Kelas
                |--------------------------------------------------------------------------
                */

                $kelasId = null;

                if (isset($payload['kelas']) && $payload['kelas'] !== '') {

                    $kelas = Kelas::firstOrCreate([
                        'pondok_id' => $batch->pondok_id,
                        'nama' => $payload['kelas']
                    ]);

                    $kelasId = $kelas->id;
                }


                /*
                |--------------------------------------------------------------------------
                | Resolve Komplek
                |--------------------------------------------------------------------------
                */

                $komplekId = null;

                if (isset($payload['komplek']) && $payload['komplek'] !== '') {

                    $komplek = Komplek::firstOrCreate([
                        'pondok_id' => $batch->pondok_id,
                        'nama' => $payload['komplek']
                    ]);

                    $komplekId = $komplek->id;
                }


                /*
                |--------------------------------------------------------------------------
                | Resolve Kamar
                |--------------------------------------------------------------------------
                */

                $kamarId = null;

                if (isset($payload['kamar']) && $payload['kamar'] !== '') {
                
                    $kamar = Kamar::firstOrCreate(
                        [
                            'komplek_id' => $komplekId,
                            'nama' => (string) $payload['kamar']
                        ],
                        [
                            'pondok_id' => $batch->pondok_id
                        ]
                    );
                
                    $kamarId = $kamar->id;
                }


                /*
                |--------------------------------------------------------------------------
                | Resolve Wali
                |--------------------------------------------------------------------------
                */

                $waliId = null;

                if (
                    (isset($payload['wali_nama']) && $payload['wali_nama'] !== '') ||
                    (isset($payload['wali_no_hp']) && $payload['wali_no_hp'] !== '')
                ) {
                
                    if (!empty($payload['wali_no_hp'])) {
                
                        // jika ada no hp → gunakan sebagai identifier
                        $wali = Wali::firstOrCreate(
                            [
                                'pondok_id' => $batch->pondok_id,
                                'no_hp' => $payload['wali_no_hp']
                            ],
                            [
                                'nama' => $payload['wali_nama'] ?? 'Wali Santri'
                            ]
                        );
                
                    } else {
                
                        // jika tidak ada no hp → gunakan nama
                        $wali = Wali::firstOrCreate(
                            [
                                'pondok_id' => $batch->pondok_id,
                                'nama' => $payload['wali_nama']
                            ]
                        );
                
                    }
                
                    $waliId = $wali->id;
                }


                /*
                |--------------------------------------------------------------------------
                | Create / Update Santri
                |--------------------------------------------------------------------------
                */

                Santri::updateOrCreate(

                    [
                        'pondok_id' => $batch->pondok_id,
                        'nis' => $payload['nis']
                    ],

                    [
                        'nama_lengkap' => $payload['nama_lengkap'],
                        'jenis_kelamin' => $payload['jenis_kelamin'] ?? null,
                        'alamat' => $payload['alamat'] ?? null,
                        'kelas_id' => $kelasId,
                        'kamar_id' => $kamarId,
                        'wali_id' => $waliId,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Update Status Batch
            |--------------------------------------------------------------------------
            */

            $batch->update([
                'status' => 'committed'
            ]);
        });
    }
}