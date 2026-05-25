<?php 
namespace App\Domains\Pelanggaran\Actions;

use App\Models\KategoriPelanggaran;
use App\Domains\Pelanggaran\DTO\KategoriPelanggaranDTO;
use App\Domains\Shared\Actions\LogActivityAction;
use Illuminate\Support\Facades\DB;

class UpdateKategoriPelanggaranAction 
{
    public function __construct(
        protected LogActivityAction $logActivity
    ) {}

    public function execute(KategoriPelanggaranDTO $dto): KategoriPelanggaran 
    {
        return DB::transaction(function () use ($dto) {
            // Pastikan data yang diupdate milik pondok yang request (Tenant Guard)
            $kategori = KategoriPelanggaran::where('pondok_id', $dto->pondok_id)
                ->findOrFail($dto->id);

            $kategori->update([
                'nama_pelanggaran' => $dto->nama_pelanggaran,
                'poin'             => $dto->poin,
                'tingkat'          => $dto->tingkat,
            ]);

            // Catat Log Aktivitas
            // Pastikan ini dipanggil SETELAH proses $kategori->update(...) 
            // agar $kategori->getChanges() bisa menangkap data apa saja yang berubah.

            $this->logActivity->execute(
                event: 'kategori_pelanggaran.update',
                subject: $kategori, // Mengirim model agar otomatis mencatat subject_type & id
                description: "Mengubah kategori pelanggaran ID #{$kategori->id} menjadi: {$dto->nama_pelanggaran}",
                oldValues: array_intersect_key($kategori->getOriginal(), $kategori->getChanges()), // Data lama sebelum diubah
                newValues: $kategori->getChanges() // Data baru yang berubah saja
            );

            return $kategori;
        });
    }
}