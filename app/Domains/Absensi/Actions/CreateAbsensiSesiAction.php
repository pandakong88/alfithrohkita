<?php

namespace App\Domains\Absensi\Actions;

use App\Models\AbsensiSesi;
// SESUAIKAN NAMESPACE DTO DI SINI
use App\Domains\Absensi\DTO\AbsensiSesiDTO; 
use App\Domains\Shared\Actions\LogActivityAction;

class CreateAbsensiSesiAction 
{
    protected $logActivity;

    public function __construct(LogActivityAction $logActivity)
    {
        $this->logActivity = $logActivity;
    }

    public function execute(AbsensiSesiDTO $data, ?int $id = null): AbsensiSesi 
    {
        $event = $id ? 'absensi_sesi.updated' : 'absensi_sesi.created';
        $description = $id ? 'Memperbarui sesi absensi' : 'Membuat sesi absensi baru';

        $sesi = AbsensiSesi::updateOrCreate(
            ['id' => $id],
            [
                'nama_sesi'   => $data->nama_sesi,
                'target_tipe' => $data->target_tipe, // Simpan tipe target
                'target_id'   => $data->target_id,
                'jam_mulai'   => $data->jam_mulai,
                'jam_selesai' => $data->jam_selesai,
                'is_active'   => $data->is_active,
            ]
        );

        $this->logActivity->execute(
            event: $event,
            subject: $sesi,
            description: $description,
            newValues: $sesi->getAttributes()
        );

        return $sesi;
    }
}