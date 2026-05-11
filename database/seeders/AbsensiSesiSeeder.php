<?php

namespace Database\Seeders;

use App\Models\AbsensiSesi;
use App\Models\Pondok; // Pastikan nama model Pondok Anda benar
use Illuminate\Database\Seeder;

class AbsensiSesiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil pondok pertama sebagai target seeding
        $pondok = Pondok::first();

        if (!$pondok) {
            $this->command->warn('Seeding diabaikan: Tidak ada data Pondok. Jalankan PondokSeeder dulu!');
            return;
        }

        $sesi = [
            [
                'nama_sesi' => 'Jamaah Subuh & Dzikir',
                'jam_mulai' => '04:00:00',
                'jam_selesai' => '05:30:00',
            ],
            [
                'nama_sesi' => 'Madrasah Diniyah Pagi',
                'jam_mulai' => '07:30:00',
                'jam_selesai' => '12:00:00',
            ],
            [
                'nama_sesi' => 'Jamaah Maghrib & Isya',
                'jam_mulai' => '18:00:00',
                'jam_selesai' => '20:00:00',
            ],
            [
                'nama_sesi' => 'Kajian Kitab Malam',
                'jam_mulai' => '20:30:00',
                'jam_selesai' => '22:00:00',
            ],
        ];

        foreach ($sesi as $s) {
            // Kita gunakan updateOrCreate agar tidak duplikat jika seeder dijalankan ulang
            AbsensiSesi::updateOrCreate(
                [
                    'pondok_id' => $pondok->id, // Filter berdasarkan pondok
                    'nama_sesi' => $s['nama_sesi']
                ],
                [
                    'pondok_id'   => $pondok->id,
                    'nama_sesi'   => $s['nama_sesi'],
                    'jam_mulai'   => $s['jam_mulai'],
                    'jam_selesai' => $s['jam_selesai'],
                ]
            );
        }

        $this->command->info('AbsensiSesiSeeder berhasil dijalankan untuk Pondok: ' . $pondok->nama_pondok);
    }
}