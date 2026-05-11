<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TemplateVariable;

class TemplateVariableSeeder extends Seeder
{
    public function run()
    {
        $variables = [
            // --- KELOMPOK AUTO (DATA DARI DATABASE) ---
            [
                'key' => 'santri.nama_lengkap',
                'label' => 'Nama Santri',
                'source' => 'nama_lengkap',
                'type' => 'auto',
                'input_type' => 'text',
                'options' => null,
                'is_required' => true,
            ],
            [
                'key' => 'santri.nis',
                'label' => 'NIS',
                'source' => 'nis',
                'type' => 'auto',
                'input_type' => 'text',
                'options' => null,
                'is_required' => true,
            ],
            [
                'key' => 'santri.jenis_kelamin',
                'label' => 'Jenis Kelamin',
                'source' => 'jenis_kelamin',
                'type' => 'auto',
                'input_type' => 'select',
                'options' => json_encode([
                    ['value' => 'L', 'label' => 'Laki-laki'],
                    ['value' => 'P', 'label' => 'Perempuan'],
                ]),
                'is_required' => true,
            ],
            [
                'key' => 'kelas.nama',
                'label' => 'Kelas Santri',
                'source' => 'kelas.nama',
                'type' => 'auto',
                'input_type' => 'text',
                'options' => null,
                'is_required' => false,
            ],

            // --- KELOMPOK MANUAL (INPUT OLEH ADMIN) ---
            [
                'key' => 'tanggal_keluar',
                'label' => 'Tanggal Keluar',
                'source' => null,
                'type' => 'manual',
                'input_type' => 'date',
                'options' => null,
                'is_required' => true,
            ],
            [
                'key' => 'batas_kembali',
                'label' => 'Batas Kembali',
                'source' => null,
                'type' => 'manual',
                'input_type' => 'date',
                'options' => null,
                'is_required' => true,
            ],
            [
                'key' => 'tanggal_kembali',
                'label' => 'Tanggal Kembali',
                'source' => null,
                'type' => 'manual',
                'input_type' => 'date',
                'options' => null,
                'is_required' => true,
            ],
            [
                'key' => 'keperluan',
                'label' => 'Keperluan Izin',
                'source' => null,
                'type' => 'manual',
                'input_type' => 'textarea',
                'options' => null,
                'is_required' => true,
            ],
            [
                'key' => 'wali.nama_penjemput',
                'label' => 'Nama Penjemput (Wali)',
                'source' => 'wali.nama', // Default ambil dari DB, tapi bisa diketik ulang
                'type' => 'manual',
                'input_type' => 'text',
                'options' => null,
                'is_required' => true,
            ],
            [
                'key' => 'wali.no_hp_penjemput',
                'label' => 'No. HP Penjemput',
                'source' => 'wali.no_hp',
                'type' => 'manual',
                'input_type' => 'text',
                'options' => null,
                'is_required' => false,
            ],
            [
                'key' => 'kategori_izin',
                'label' => 'Kategori Izin',
                'source' => null,
                'type' => 'manual',
                'input_type' => 'select',
                'options' => json_encode([
                    ['value' => 'reguler', 'label' => 'Sakit / Izin Biasa'],
                    ['value' => 'darurat', 'label' => 'Darurat / Keluarga Meninggal'],
                    ['value' => 'pendidikan', 'label' => 'Lomba / Pendidikan'],
                ]),
                'is_required' => true,
            ],
        ];

        foreach ($variables as $var) {
            TemplateVariable::updateOrCreate(['key' => $var['key']], $var);
        }
    }
}