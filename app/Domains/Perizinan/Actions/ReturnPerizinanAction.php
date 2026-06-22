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
        // Pastikan relasi santri & template sudah termuat agar tidak query ulang saat logging / penalty check
        $perizinan->loadMissing(['santri', 'template']);

        return DB::transaction(function () use ($perizinan) {

            // 1. CEK APAKAH BOLEH SCAN
            // Santri hanya boleh scan jika statusnya masih 'aktif' atau 'terlambat' (di luar pondok)
            if ($perizinan->status !== 'aktif' && $perizinan->status !== 'terlambat') {
                $pesan = match ($perizinan->status) {
                    'pending'    => 'Maaf, surat masih PENDING (belum disetujui).',
                    'kembali'    => 'Santri sudah dikonfirmasi KEMBALI sebelumnya.',
                    'dibatalkan' => 'Maaf, surat izin ini sudah DIBATALKAN.',
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

            $isOverdue = $waktuScan->gt($perizinan->batas_kembali);
            if ($isOverdue) {
                $perizinan->durasi_terlambat_menit = $perizinan->batas_kembali->diffInMinutes($waktuScan);
                $keteranganLog = "terlambat {$perizinan->durasi_terlambat_menit} menit";
            } else {
                $perizinan->durasi_terlambat_menit = 0;
                $keteranganLog = "tepat waktu";
            }

            $perizinan->save();

            // 4. UPDATE STATUS SANTRI JADI AKTIF (MASUK PONDOK)
            $perizinan->santri()->update(['status' => 'active']);

            // 5. SINKRONISASI ABSENSI SAAT KEMBALI CEPAT (EARLY RETURN CLEANUP)
            // Hapus log absensi otomatis 'izin' untuk hari esok ke depan
            \App\Models\Absensi::where('santri_id', $perizinan->santri_id)
                ->where('status', 'izin')
                ->where('keterangan', "Otomatis: Izin via Surat {$perizinan->kode_surat}")
                ->where('tanggal', '>', $waktuScan->toDateString())
                ->delete();

            // Hapus log absensi otomatis 'izin' untuk hari ini yang sesinya belum berjalan
            $sesiIdsToDelete = \App\Models\AbsensiSesi::where('pondok_id', $perizinan->pondok_id)
                ->where('jam_mulai', '>=', $waktuScan->toTimeString())
                ->pluck('id');

            if ($sesiIdsToDelete->isNotEmpty()) {
                \App\Models\Absensi::where('santri_id', $perizinan->santri_id)
                    ->where('status', 'izin')
                    ->where('keterangan', "Otomatis: Izin via Surat {$perizinan->kode_surat}")
                    ->where('tanggal', $waktuScan->toDateString())
                    ->whereIn('sesi_id', $sesiIdsToDelete)
                    ->delete();
            }

            // 6. Denda Pelanggaran Otomatis (Late Return Penalty)
            if ($isOverdue && $perizinan->template && isset($perizinan->template->rules['late_penalty']['enabled']) && $perizinan->template->rules['late_penalty']['enabled']) {
                $rules = $perizinan->template->rules['late_penalty'];
                $intervalMinutes = max((int) ($rules['interval_minutes'] ?? 60), 1);
                $pointsPerInterval = (int) ($rules['points_per_interval'] ?? 5);

                $calculatedPoints = floor($perizinan->durasi_terlambat_menit / $intervalMinutes) * $pointsPerInterval;

                if ($calculatedPoints > 0) {
                    \App\Models\PelanggaranSantri::create([
                        'pondok_id' => $perizinan->pondok_id,
                        'santri_id' => $perizinan->santri_id,
                        'kategori_sumber' => 'otomatis',
                        'judul_pelanggaran' => "Keterlambatan kembali izin ({$perizinan->template->nama})",
                        'poin' => $calculatedPoints,
                        'tanggal' => $waktuScan->toDateString(),
                        'catatan_detail' => "Terlambat {$perizinan->durasi_terlambat_menit} menit dari batas kembali {$perizinan->batas_kembali->format('d/m/Y H:i')}.",
                    ]);
                }
            }

            // 7. LOG & REFRESH
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