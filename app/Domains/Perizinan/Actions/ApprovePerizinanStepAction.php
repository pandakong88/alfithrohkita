<?php

namespace App\Domains\Perizinan\Actions;

use App\Domains\Absensi\Actions\CreateAbsensiAction;
use App\Domains\Absensi\DTO\AbsensiDTO;
use App\Domains\Shared\Actions\LogActivityAction;
use App\Models\Perizinan;
use App\Models\PerizinanApproval;
use App\Models\AbsensiSesi;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class ApprovePerizinanStepAction
{
    public function __construct(
        protected LogActivityAction $logActivity,
        protected CreateAbsensiAction $absensiAction
    ) {}

    public function execute(Perizinan $perizinan, $user, $stepIndex, $stepName): Perizinan
    {
        return DB::transaction(function () use ($perizinan, $user, $stepIndex, $stepName) {
            // 1. Create Approval Log Record
            PerizinanApproval::create([
                'pondok_id' => $perizinan->pondok_id,
                'perizinan_id' => $perizinan->id,
                'step_index' => $stepIndex,
                'step_name' => $stepName,
                'approved_by' => $user->id,
            ]);

            // 2. Increment Step
            $nextStep = $stepIndex + 1;
            $perizinan->current_step = $nextStep;
            
            // Re-evaluate status
            $template = $perizinan->template;
            $steps = $template && isset($template->rules['approval_workflow']) ? $template->rules['approval_workflow'] : [];
            
            if ($nextStep > count($steps)) {
                // All steps approved! Activate the permit
                $perizinan->status = 'aktif';
                $perizinan->save();

                // Mark student as 'izin'
                $santri = $perizinan->santri;
                $santri->update(['status' => 'izin']);

                // Sync attendance
                $sesiRelevan = AbsensiSesi::active()
                    ->where('pondok_id', $perizinan->pondok_id)
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
                    $period = CarbonPeriod::create(
                        $perizinan->tanggal_keluar->copy()->startOfDay(),
                        $perizinan->batas_kembali->copy()->endOfDay()
                    );

                    $absensiDtos = [];
                    foreach ($period as $date) {
                        $tanggalString = $date->format('Y-m-d');
                        foreach ($sesiRelevan as $sesi) {
                            $absensiDtos[] = new AbsensiDTO(
                                pondok_id: $perizinan->pondok_id,
                                santri_id: $santri->id,
                                sesi_id:   $sesi->id,
                                tanggal:   $tanggalString,
                                status:    'izin',
                                input_by:  $user->id,
                                metode:    'manual',
                                keterangan: "Otomatis: Izin via Surat {$perizinan->kode_surat}"
                            );
                        }
                    }

                    $this->absensiAction->execute($absensiDtos);
                }
            } else {
                $perizinan->save();
            }

            // 3. Log Activity
            $this->logActivity->execute(
                event: 'perizinan.step_approved',
                subject: $perizinan,
                description: "Menyetujui langkah {$stepIndex} ({$stepName}) untuk perizinan santri {$perizinan->santri->nama_lengkap}",
                newValues: $perizinan->toArray()
            );

            return $perizinan;
        });
    }
}
