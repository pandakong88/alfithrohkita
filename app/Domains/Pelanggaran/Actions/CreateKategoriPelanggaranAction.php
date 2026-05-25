<?php 
namespace App\Domains\Pelanggaran\Actions;

use App\Models\KategoriPelanggaran;
use App\Domains\Pelanggaran\DTO\KategoriPelanggaranDTO;
use App\Domains\Shared\Actions\LogActivityAction;
use Illuminate\Support\Facades\DB;

class CreateKategoriPelanggaranAction 
{
    public function __construct(
        protected LogActivityAction $logActivity
    ) {}

    public function execute(KategoriPelanggaranDTO $dto): KategoriPelanggaran 
    {
        return DB::transaction(function () use ($dto) {
            $kategori = KategoriPelanggaran::create([
                'pondok_id'        => $dto->pondok_id,
                'nama_pelanggaran' => $dto->nama_pelanggaran,
                'poin'             => $dto->poin,
                'tingkat'          => $dto->tingkat,
            ]);

            // Catat Log Aktivitas
            $this->logActivity->execute(
                event: 'kategori_pelanggaran.create',
                subject: $kategori, // Mengirim objek model agar tercatat subject_type & subject_id secara otomatis
                description: "Membuat kategori pelanggaran baru: {$dto->nama_pelanggaran} ({$dto->poin} Poin)",
                newValues: [
                    'tingkat' => $dto->tingkat,
                    'pondok_id' => $dto->pondok_id
                ]
            );

            return $kategori;
        });
    }
}