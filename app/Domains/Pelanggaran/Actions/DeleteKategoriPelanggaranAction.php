<?php 
namespace App\Domains\Pelanggaran\Actions;

use App\Models\KategoriPelanggaran;
use App\Domains\Shared\Actions\LogActivityAction;
use Illuminate\Support\Facades\DB;

class DeleteKategoriPelanggaranAction 
{
    public function __construct(
        protected LogActivityAction $logActivity
    ) {}

    public function execute(int $id): void 
    {
        DB::transaction(function () use ($id) {
            $pondokId = auth()->user()->pondok_id;

            $kategori = KategoriPelanggaran::where('pondok_id', $pondokId)
                ->findOrFail($id);

            $kategori->delete();

        // PENTING: Jalankan ini TEPAT SEBELUM $kategori->delete();

        $this->logActivity->execute(
            event: 'kategori_pelanggaran.delete',
            subject: $kategori, // Tetap kirim objek model agar terbaca subject_type dan subject_id-nya
            description: "Menghapus kategori pelanggaran: {$kategori->nama_pelanggaran}",
            oldValues: $kategori->toArray() // Menyimpan seluruh data terakhir sebelum dihapus sebagai histori
        );
        
        });
    }
}