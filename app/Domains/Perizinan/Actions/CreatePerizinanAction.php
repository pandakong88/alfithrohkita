<?php

namespace App\Domains\Perizinan\Actions;

use App\Models\Perizinan;
use App\Models\Santri;
use App\Domains\Perizinan\DTO\CreatePerizinanData;
use App\Domains\Shared\Actions\LogActivityAction;
use Illuminate\Support\Facades\DB;

class CreatePerizinanAction
{
    public function __construct(
        protected LogActivityAction $logActivity
    ) {}

    public function execute(CreatePerizinanData $data): Perizinan
    {
        return DB::transaction(function () use ($data) {

            $user = auth()->user();

            $santri = Santri::where('id', $data->santri_id)
                ->where('pondok_id', $user->pondok_id)
                ->firstOrFail();

            $perizinan = Perizinan::create([
                'pondok_id' => $user->pondok_id,
                'santri_id' => $santri->id,
                'template_perizinan_id' => $data->template_perizinan_id,
                'tanggal_keluar' => $data->tanggal_keluar,
                'batas_kembali' => $data->batas_kembali,
                'status' => 'aktif',
                'keperluan' => $data->keperluan,
                'keterangan' => $data->keterangan,
                'created_by' => $user->id,
            ]);

            $this->logActivity->execute(
                event: 'perizinan.created',
                subject: $perizinan,
                description: 'Membuat perizinan santri',
                newValues: $perizinan->getAttributes()
            );

            return $perizinan;
        });
    }
}