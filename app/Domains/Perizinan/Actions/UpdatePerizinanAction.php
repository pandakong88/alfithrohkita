<?php

namespace App\Domains\Perizinan\Actions;

use App\Models\Perizinan;
use App\Models\Santri;
use App\Domains\Perizinan\DTO\UpdatePerizinanData;
use App\Domains\Shared\Actions\LogActivityAction;
use Illuminate\Support\Facades\DB;

class UpdatePerizinanAction
{
    public function __construct(
        protected LogActivityAction $logActivity
    ) {}

    public function execute(
        Perizinan $perizinan,
        UpdatePerizinanData $data
    ): Perizinan {

        return DB::transaction(function () use ($perizinan, $data) {

            $user = auth()->user();

            $santri = Santri::where('id', $data->santri_id)
                ->where('pondok_id', $user->pondok_id)
                ->firstOrFail();

            $oldValues = $perizinan->getOriginal();

            $perizinan->update([
                'santri_id' => $santri->id,
                'template_perizinan_id' => $data->template_perizinan_id,
                'tanggal_keluar' => $data->tanggal_keluar,
                'batas_kembali' => $data->batas_kembali,
                'tanggal_kembali' => $data->tanggal_kembali,
                'status' => $data->status,
                'keperluan' => $data->keperluan,
                'keterangan' => $data->keterangan,
                'updated_by' => $user->id,
            ]);

            $this->logActivity->execute(
                event: 'perizinan.updated',
                subject: $perizinan,
                description: 'Memperbarui perizinan santri',
                oldValues: $oldValues,
                newValues: $perizinan->getAttributes()
            );

            return $perizinan;
        });
    }
}