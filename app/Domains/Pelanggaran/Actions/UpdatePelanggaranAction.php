<?php 

namespace App\Domains\Pelanggaran\Actions;

use App\Models\PelanggaranSantri;
use App\Domains\Shared\Actions\LogActivityAction;
use Illuminate\Support\Facades\DB;

class UpdatePelanggaranAction 
{
    public function __construct(
        protected LogActivityAction $logActivity
    ) {}

    public function execute(int $id, array $data): PelanggaranSantri 
    {
        return DB::transaction(function () use ($id, $data) {
            $pondokId = auth()->user()->pondok_id;

            $pelanggaran = PelanggaranSantri::where('pondok_id', $pondokId)
                ->findOrFail($id);

            // 1. Ambil data asli sebelum diupdate agar data lama tidak hilang/tertimpa
            $oldData = $pelanggaran->getOriginal();

            $pelanggaran->update([
                'judul_pelanggaran' => $data['judul_pelanggaran'],
                'poin'              => $data['poin'],
                'catatan_detail'    => $data['catatan_detail'] ?? $pelanggaran->catatan_detail,
            ]);

            // 2. Tangkap kolom apa saja yang benar-benar berubah
            $changes = $pelanggaran->getChanges();

            // Catat ke Log Activity
            $this->logActivity->execute(
                event: 'pelanggaran_santri.update',
                subject: $pelanggaran, // Otomatis mencatat subject_type (PelanggaranSantri) dan subject_id
                description: "Mengubah data pelanggaran ID #{$id} untuk Santri ID #{$pelanggaran->santri_id}",
                oldValues: array_intersect_key($oldData, $changes), // Nilai lama dari kolom yang berubah saja
                newValues: $changes // Nilai baru yang berubah
            );

            return $pelanggaran;
        });
    }
}