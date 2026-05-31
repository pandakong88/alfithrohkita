<?php

namespace App\Console\Commands;

use App\Models\Perizinan;
use App\Models\Absensi;
use App\Models\AbsensiSesi;
use Illuminate\Console\Command;
use Carbon\Carbon;

class UpdateStatusTerlambat extends Command
{
    // Anda bisa pertahankan signature lama Anda agar tidak perlu mengubah settingan di server/scheduler
    protected $signature = 'app:update-status-terlambat'; 
    protected $description = 'Otomatis set status terlambat di perizinan dan tembak Alfa di absensi harian';

    public function handle()
    {
        $sekarang = Carbon::now();
        $hariIni = Carbon::today()->toDateString();

        // 1. Ambil data perizinan yang terlambat (Eager load relasi santri untuk validasi target)
        $perizinansTerlambat = Perizinan::with('santri')
            ->where('status', 'aktif')
            ->where('batas_kembali', '<', $sekarang)
            ->get();

        if ($perizinansTerlambat->isEmpty()) {
            $this->info("Tidak ada perizinan yang terlambat saat ini.");
            return;
        }

        $totalSuratDiupdate = 0;

        foreach ($perizinansTerlambat as $perizinan) {
            
            // 2. SEKALIGUS TEMBAK ABSENSI HARI INI JADI ALFA
            // Ambil semua sesi absensi aktif di pondok terkait
            // BENAR: Menggunakan scope active() sesuai konfigurasi model kamu
            $sesis = AbsensiSesi::where('pondok_id', $perizinan->pondok_id)
            ->active() 
            ->get();

            foreach ($sesis as $sesi) {
                // Pastikan santri termasuk target sesi (kelas/kamar/global)
                $isTarget = true;
                if ($sesi->target_tipe === 'kelas' && $perizinan->santri->kelas_id != $sesi->target_id) $isTarget = false;
                if ($sesi->target_tipe === 'kamar' && $perizinan->santri->kamar_id != $sesi->target_id) $isTarget = false;
                
                if ($isTarget) {
                    Absensi::updateOrCreate(
                        [
                            'pondok_id' => $perizinan->pondok_id,
                            'santri_id' => $perizinan->santri_id,
                            'sesi_id'   => $sesi->id,
                            'tanggal'   => $hariIni,
                        ],
                        [
                            'status'     => 'alfa',
                            'metode'     => 'manual',
                            'keterangan' => 'Otomatis Alfa: Melewati batas kembali perizinan (' . $perizinan->kode_surat . ')',
                            'input_by'   => null,
                        ]
                    );
                }
            }

            // 3. Update status surat perizinannya menjadi 'terlambat'
            $perizinan->update(['status' => 'terlambat']);
            $totalSuratDiupdate++;
        }

        $this->info("Berhasil mengupdate $totalSuratDiupdate perizinan ke status TERLAMBAT dan mensinkronisasikan ke data Absensi Alfa.");
    }
}