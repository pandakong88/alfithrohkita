<?php

namespace App\Domains\Pelanggaran\DTO;

use Illuminate\Http\Request;

class PelanggaranSantriDTO 
{
    public function __construct(
        public int $pondok_id,
        public int $santri_id,
        public ?int $kategori_id,
        public string $judul_pelanggaran,
        public int $poin,
        public string $tanggal,
        public string $kategori_sumber = 'manual',
        public ?string $catatan_detail = null,
        public ?string $foto_bukti = null,
        public ?int $user_id = null,
        public ?int $absensi_id = null,
    ) {}

    /**
     * Mengubah Request Multi-Santri menjadi Array of DTOs
     * @return self[]
     */
    public static function fromRequestCollection(Request $request, ?string $pathFoto = null): array
    {
        $dtos = [];
        $pondokId = auth()->user()->pondok_id;
        $userId = auth()->id();
        $tanggal = $request->tanggal ?? date('Y-m-d');

        // Looping setiap santri_id yang dikirim dari Flutter/Web
        foreach ($request->santri_ids as $santriId) {
            $dtos[] = new self(
                pondok_id:         $pondokId,
                santri_id:         (int) $santriId,
                kategori_id:       $request->kategori_id ? (int) $request->kategori_id : null,
                judul_pelanggaran: $request->judul_pelanggaran,
                poin:              (int) $request->poin,
                tanggal:           $tanggal,
                kategori_sumber:   $request->kategori_sumber ?? 'manual',
                catatan_detail:    $request->catatan_detail,
                foto_bukti:        $pathFoto, // Path foto yang sama disebarkan ke semua santri
                user_id:           $userId,
                absensi_id:        $request->absensi_id ? (int) $request->absensi_id : null,
            );
        }

        return $dtos;
    }
}