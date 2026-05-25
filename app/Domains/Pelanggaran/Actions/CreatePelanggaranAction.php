<?php 
namespace App\Domains\Pelanggaran\Actions;

use App\Models\PelanggaranSantri;
use App\Domains\Pelanggaran\DTO\PelanggaranSantriDTO;
use App\Domains\Shared\Actions\LogActivityAction;
use Illuminate\Support\Facades\DB;

class CreatePelanggaranAction 
{
    public function __construct(
        protected LogActivityAction $logActivity
    ) {}

    /**
     * Mengeksekusi pencatatan pelanggaran santri
     * @param PelanggaranSantriDTO[] $dtos
     */
    public function execute(array $dtos): void 
    {
        DB::transaction(function () use ($dtos) {
            foreach ($dtos as $dto) {
                PelanggaranSantri::create([
                    'pondok_id'         => $dto->pondok_id,
                    'santri_id'         => $dto->santri_id,
                    'kategori_id'       => $dto->kategori_id,
                    'absensi_id'        => $dto->absensi_id,
                    'kategori_sumber'   => $dto->kategori_sumber,
                    'judul_pelanggaran' => $dto->judul_pelanggaran,
                    'poin'              => $dto->poin,
                    'tanggal'           => $dto->tanggal,
                    'catatan_detail'    => $dto->catatan_detail,
                    'foto_bukti'        => $dto->foto_bukti,
                    'user_id'           => $dto->user_id,
                ]);
            }

           // Catat ke Log Activity jika ada data yang diproses
            if (!empty($dtos)) {
                $totalPelanggaran = count($dtos);
                
                // Ambil item pertama secara aman menggunakan helper head() bawaan Laravel
                $firstDto = head($dtos); 

                // Panggil logger khusus untuk mencatat log aktivitas pelanggaran massal
                $this->logActivity->logBatchPelanggaran(
                    total: $totalPelanggaran,
                    sumber: $firstDto->kategori_sumber
                );
            }
        });
    }
}