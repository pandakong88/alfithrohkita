<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TemplatePerizinan;
use Illuminate\Support\Str;

class TemplatePerizinanSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            [
                'pondok_id' => 3, // Sesuaikan dengan ID Pondok yang ada
                'nama' => 'Surat Izin Reguler',
                'deskripsi' => 'Template standar untuk izin pulang bulanan atau izin sakit.',
                'source_type' => 'html',
                'format_surat' => '<p>Diberikan izin kepada: {{santri.nama_lengkap}} ({{santri.nis}})...</p>',
                'layout_print' => 1,
                'required_variables' => [
                    'santri.nama_lengkap',
                    'santri.nis',
                    'santri.jenis_kelamin',
                    'kelas.nama',
                    'tanggal_keluar',
                    'batas_kembali',
                    'keperluan'
                ],
                'is_default' => true,
            ],
            [
                'pondok_id' => 3,
                'nama' => 'Surat Izin Susulan',
                'deskripsi' => 'Template untuk santri yang izin mendadak sehingga belum sempat izin secara regulller.',
                'source_type' => 'upload_pdf',
                'file_pdf' => 'templates/izin-susulan-std.pdf',
                'layout_print' => 1,
                'required_variables' => [
                    'santri.nama_lengkap',
                    'santri.nis',
                    'tanggal_keluar',
                    'batas_kembali',
                    'keperluan',
                    'wali.nama_penjemput',
                    'wali.no_hp_penjemput'
                ],
                'is_default' => false,
            ]
        ];

        foreach ($templates as $temp) {
            TemplatePerizinan::create([
                'pondok_id' => $temp['pondok_id'],
                'nama' => $temp['nama'],
                'slug' => Str::slug($temp['nama']),
                'deskripsi' => $temp['deskripsi'],
                'source_type' => $temp['source_type'] ?? 'html',
                'format_surat' => $temp['format_surat'] ?? null,
                'file_pdf' => $temp['file_pdf'] ?? null,
                'layout_print' => $temp['layout_print'],
                'required_variables' => $temp['required_variables'], // Otomatis jadi JSON jika di model ada casting 'array'
                'is_default' => $temp['is_default'],
                'is_active' => true,
                'created_by' => 1, // Sesuaikan dengan ID User Admin
            ]);
        }
    }
}