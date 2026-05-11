<?php

namespace App\Domains\Perizinan\Actions;

use App\Domains\Absensi\Actions\CreateAbsensiAction;
use App\Domains\Absensi\DTO\AbsensiDTO;
use App\Domains\Perizinan\DTO\CreatePerizinanData;
use App\Domains\Shared\Actions\LogActivityAction;
use App\Models\Perizinan;
use App\Models\Santri;
use App\Models\AbsensiSesi;
use App\Models\TemplatePerizinan;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreatePerizinanAction
{
    public function __construct(
        protected LogActivityAction $logActivity,
        protected CreateAbsensiAction $absensiAction
    ) {}

    public function execute(CreatePerizinanData $data): Perizinan
    {
        return DB::transaction(function () use ($data) {
            $user = auth()->user();
            $pondokId = $user->pondok_id;

            // 1. Validasi Santri & Eager Load
            $santri = Santri::with(['kelas', 'kamar.kompleks'])
                ->where('id', $data->santri_id)
                ->where('pondok_id', $pondokId)
                ->firstOrFail();

            if (Perizinan::where('santri_id', $santri->id)->where('status', 'aktif')->exists()) {
                throw new \Exception('Santri masih dalam masa izin dan belum kembali.');
            }

            // 2. Validasi Template
            $template = TemplatePerizinan::where('id', $data->template_perizinan_id)
                ->where('pondok_id', $pondokId)
                ->firstOrFail();

            // 3. Generate Kode Surat
            do {
                $kode = 'IZN-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
            } while (Perizinan::where('pondok_id', $pondokId)->where('kode_surat', $kode)->exists());

            // 4. Logika Mapping Variabel (Sempurnakan agar tidak muncul '-')
            $requiredKeys = $template->required_variables ?? [];
            $finalSnapshot = [];

            foreach ($requiredKeys as $key) {
                // Prioritas 1: Input manual dari form
                if (isset($data->keterangan[$key]) && !empty($data->keterangan[$key])) {
                    $finalSnapshot[$key] = $data->keterangan[$key];
                } 
                // Prioritas 2: Data spesifik waktu perizinan
                else {
                    $finalSnapshot[$key] = match($key) {
                        'tanggal_keluar' => $data->tanggal_keluar->format('d/m/Y H:i'),
                        'batas_kembali'  => $data->batas_kembali->format('d/m/Y H:i'),
                        'kode_surat'     => $kode,
                        'keperluan'      => $data->keperluan,
                        default          => data_get($santri, $key) ?? '-'
                    };
                }
            }

            // 5. Create Perizinan
            $perizinan = Perizinan::create([
                'pondok_id' => $pondokId,
                'santri_id' => $santri->id,
                'template_perizinan_id' => $template->id,
                'kode_surat' => $kode,
                'nomor_manual' => $data->nomor_manual,
                'tanggal_keluar' => $data->tanggal_keluar,
                'batas_kembali' => $data->batas_kembali,
                'status' => 'aktif',
                'keperluan' => $data->keperluan,
                'variables' => $finalSnapshot,
                'created_by' => $user->id,
            ]);

            // Update status santri
            $santri->update(['status' => 'izin']);

            // 6. Sinkronisasi Absensi (Fix Rentang Tanggal)
            $sesiRelevan = AbsensiSesi::active()
                ->where('pondok_id', $pondokId)
                ->where(function ($query) use ($santri) {
                    $query->where('target_tipe', 'global')
                        ->orWhere(function ($q) use ($santri) {
                            $q->where('target_tipe', 'kelas')->where('target_id', $santri->kelas_id);
                        })
                        ->orWhere(function ($q) use ($santri) {
                            $q->where('target_tipe', 'kamar')->where('target_id', $santri->kamar_id);
                        })
                        ->orWhere(function ($q) use ($santri) {
                            $q->where('target_tipe', 'plotting')
                              ->whereHas('santris', fn($sq) => $sq->where('santri_id', $santri->id));
                        });
                })->get();

            if ($sesiRelevan->isNotEmpty()) {
                // Pastikan start & end of day agar mencakup hari terakhir
                $period = CarbonPeriod::create(
                    $perizinan->tanggal_keluar->copy()->startOfDay(),
                    $perizinan->batas_kembali->copy()->endOfDay()
                );

                $absensiDtos = [];
                foreach ($period as $date) {
                    $tanggalString = $date->format('Y-m-d');
                    foreach ($sesiRelevan as $sesi) {
                        $absensiDtos[] = new AbsensiDTO(
                            pondok_id: $pondokId,
                            santri_id: $santri->id,
                            sesi_id:   $sesi->id,
                            tanggal:   $tanggalString,
                            status:    'izin',
                            input_by:  $user->id,
                            metode:    'manual', // Mengatasi error Enum/Truncated kemarin
                            keterangan: "Otomatis: Izin via Surat {$perizinan->kode_surat}"
                        );
                    }
                }

                $this->absensiAction->execute($absensiDtos);
            }

            // 7. Log Activity
            $this->logActivity->execute(
                event: 'perizinan.created',
                subject: $perizinan,
                description: "Membuat perizinan untuk santri {$santri->nama_lengkap} sampai " . $perizinan->batas_kembali->format('d/m/Y'),
                newValues: $perizinan->toArray()
            );

            return $perizinan;
        });
    }
}