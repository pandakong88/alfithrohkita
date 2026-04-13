<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TemplateVariableSeeder extends Seeder
{
    public function run(): void
    {
        $variables = [

            // =========================
            // 🔹 SANTRI
            // =========================
            [
                'key' => 'santri.nama_lengkap',
                'label' => 'Nama Santri',
                'source' => 'santri.nama_lengkap',
                'type' => 'auto',
            ],
            [
                'key' => 'santri.nis',
                'label' => 'NIS',
                'source' => 'santri.nis',
                'type' => 'auto',
            ],
            [
                'key' => 'santri.jenis_kelamin',
                'label' => 'Jenis Kelamin',
                'source' => 'santri.jenis_kelamin',
                'type' => 'auto',
            ],
            [
                'key' => 'santri.alamat',
                'label' => 'Alamat Santri',
                'source' => 'santri.alamat',
                'type' => 'auto',
            ],

            // =========================
            // 🔹 WALI
            // =========================
            [
                'key' => 'wali.nama',
                'label' => 'Nama Wali',
                'source' => 'wali.nama',
                'type' => 'auto',
            ],
            [
                'key' => 'wali.no_hp',
                'label' => 'No HP Wali',
                'source' => 'wali.no_hp',
                'type' => 'auto',
            ],

            // =========================
            // 🔹 KELAS
            // =========================
            [
                'key' => 'kelas.nama',
                'label' => 'Kelas',
                'source' => 'kelas.nama',
                'type' => 'auto',
            ],

            // =========================
            // 🔹 KAMAR & KOMPLEK
            // =========================
            [
                'key' => 'kamar.nama',
                'label' => 'Kamar',
                'source' => 'kamar.nama',
                'type' => 'auto',
            ],
            [
                'key' => 'komplek.nama',
                'label' => 'Komplek',
                'source' => 'komplek.nama',
                'type' => 'auto',
            ],

            // =========================
            // 🔹 PERIZINAN (MANUAL)
            // =========================
            [
                'key' => 'tanggal_keluar',
                'label' => 'Tanggal Keluar',
                'type' => 'manual',
                'input_type' => 'date',
            ],
            [
                'key' => 'batas_kembali',
                'label' => 'Batas Kembali',
                'type' => 'manual',
                'input_type' => 'date',
            ],
            [
                'key' => 'keperluan',
                'label' => 'Keperluan',
                'type' => 'manual',
                'input_type' => 'textarea',
            ],
            [
                'key' => 'keterangan',
                'label' => 'Keterangan Tambahan',
                'type' => 'manual',
                'input_type' => 'textarea',
            ],
        ];

        foreach ($variables as $var) {
            DB::table('template_variables')->updateOrInsert(
                ['key' => $var['key']],
                array_merge($var, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}