<?php

namespace App\Domains\Perizinan\Actions;

use App\Models\Perizinan;
use App\Domains\Shared\Actions\LogActivityAction;
use Illuminate\Support\Facades\DB;

class ReturnPerizinanAction
{
    public function __construct(protected LogActivityAction $logActivity) {}

    public function execute(Perizinan $perizinan): Perizinan
    {
        // Pastikan relasi santri sudah termuat agar tidak query ulang saat logging
        $perizinan->loadMissing('santri');

        return DB::transaction(function () use ($perizinan) {

            // 1. CEK APAKAH BOLEH SCAN
            // Santri hanya boleh scan jika statusnya masih 'aktif' (di luar pondok)
            if ($perizinan->status !== 'aktif') {
                $pesan = match ($perizinan->status) {
                    'pending'    => 'Maaf, surat masih PENDING (belum disetujui).',
                    'kembali'    => 'Santri sudah dikonfirmasi KEMBALI sebelumnya.',
                    'dibatalkan' => 'Maaf, surat izin ini sudah DIBATALKAN.',
                    'terlambat'  => 'Santri sudah diproses (TERLAMBAT).', // Jika Anda tetap mempertahankan status lama
                    default      => 'Status perizinan tidak valid atau santri sudah berada di dalam pondok.'
                };
                throw new \Exception($pesan);
            }

            // 2. SET WAKTU REALITAS SCAN SEKARANG
            $waktuScan = now();
            $perizinan->tanggal_kembali = $waktuScan;

            // 3. HITUNG SELISIH TERLAMBAT & TETAPKAN STATUS AKHIR 'kembali'
            // Semua yang sukses scan statusnya adalah 'kembali'
            $perizinan->status = 'kembali'; 

            if ($waktuScan->gt($perizinan->batas_kembali)) {
                $perizinan->durasi_terlambat_menit = $perizinan->batas_kembali->diffInMinutes($waktuScan);
                $keteranganLog = "terlambat {$perizinan->durasi_terlambat_menit} menit";
            } else {
                $perizinan->durasi_terlambat_menit = 0;
                $keteranganLog = "tepat waktu";
            }

            $perizinan->save();

            // 4. UPDATE STATUS SANTRI JADI AKTIF (MASUK PONDOK)
            $perizinan->santri()->update(['status' => 'active']);

            // 5. LOG & REFRESH
            $perizinan->refresh();
            
            // Menggunakan properti nama_lengkap sesuai dengan file Blade Anda sebelumnya
            $namaSantri = $perizinan->santri->nama_lengkap ?? $perizinan->santri->nama;

            $this->logActivity->execute(
                event: 'perizinan.returned',
                subject: $perizinan,
                description: "Santri {$namaSantri} kembali ke pondok ({$keteranganLog}).",
                newValues: $perizinan->getAttributes()
            );

            return $perizinan;
        });
    }
}