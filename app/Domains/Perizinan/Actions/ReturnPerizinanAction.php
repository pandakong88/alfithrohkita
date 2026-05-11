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
        return DB::transaction(function () use ($perizinan) {

            // 1. CEK APAKAH BOLEH SCAN
            $bolehScan = ($perizinan->status === 'aktif') || 
                         ($perizinan->status === 'terlambat' && $perizinan->tanggal_kembali === null);

            if (!$bolehScan) {
                $pesan = match ($perizinan->status) {
                    'pending'    => 'Maaf, surat masih PENDING (belum disetujui).',
                    'kembali'    => 'Santri sudah dikonfirmasi KEMBALI sebelumnya.',
                    'dibatalkan' => 'Maaf, surat izin ini sudah DIBATALKAN.',
                    'terlambat'  => 'Santri sudah diproses (TERLAMBAT).',
                    default      => 'Status perizinan tidak valid.'
                };
                throw new \Exception($pesan);
            }

            // 2. SET WAKTU REALITAS SCAN SEKARANG
            $waktuScan = now();
            $perizinan->tanggal_kembali = $waktuScan;

            // 3. HITUNG SELISIH TERLAMBAT
            if ($waktuScan->gt($perizinan->batas_kembali)) {
                $perizinan->status = 'terlambat';
                $perizinan->durasi_terlambat_menit = $perizinan->batas_kembali->diffInMinutes($waktuScan);
            } else {
                $perizinan->status = 'kembali';
                $perizinan->durasi_terlambat_menit = 0;
            }

            $perizinan->save();

            // 4. UPDATE STATUS SANTRI JADI AKTIF (MASUK PONDOK)
            $perizinan->santri()->update(['status' => 'active']);

            // 5. LOG & REFRESH
            $perizinan->refresh();
            $this->logActivity->execute(
                event: 'perizinan.returned',
                subject: $perizinan,
                description: "Santri {$perizinan->santri->nama} kembali dengan status {$perizinan->status}",
                newValues: $perizinan->getAttributes()
            );

            return $perizinan;
        });
    }
}