<?php

namespace App\Domains\Absensi\Actions;

use App\Models\Absensi;
use App\Domains\Absensi\DTO\AbsensiDTO; // Sesuaikan dengan nama DTO kamu
use App\Domains\Shared\Actions\LogActivityAction;
use Illuminate\Support\Facades\DB;

class CreateAbsensiAction 
{
    public function __construct(
        protected LogActivityAction $logActivity
    ) {}

    /**
     * @param AbsensiDTO[] $dtos
     */
    public function execute(array $dtos): void 
    {
        DB::transaction(function () use ($dtos) {
            foreach ($dtos as $dto) {
                
                if ($dto->status === 'hadir') {
                    Absensi::where([
                        'santri_id' => $dto->santri_id,
                        'sesi_id'   => $dto->sesi_id,
                        'tanggal'   => $dto->tanggal,
                    ])->delete();
                    
                    continue; 
                }
    
                // AMBIL DATA DARI DTO, JANGAN DARI auth() LAGI
                Absensi::updateOrCreate(
                    [
                        'pondok_id' => $dto->pondok_id, // Pakai properti DTO
                        'santri_id' => $dto->santri_id,
                        'sesi_id'   => $dto->sesi_id,
                        'tanggal'   => $dto->tanggal,
                    ],
                    [
                        'status'     => $dto->status,
                        'metode'     => $dto->metode,
                        'keterangan' => $dto->keterangan,
                        'input_by'   => $dto->input_by, // Pakai properti DTO
                    ]
                );
            }
    
           // Di dalam CreateAbsensiAction.php bagian paling bawah

            if (!empty($dtos)) {
                $first = $dtos[0];
                
                // Panggil method khusus yang baru kita buat
                $this->logActivity->logBatchAbsensi(
                    sesiId:  $first->sesi_id,
                    tanggal: $first->tanggal,
                    total:   count($dtos)
                );
            }
        });
    }
}