<?php 
namespace App\Domains\Pelanggaran\DTO;

use Illuminate\Http\Request;

class KategoriPelanggaranDTO 
{
    public function __construct(
        public int $pondok_id,
        public string $nama_pelanggaran,
        public int $poin,
        public string $tingkat, // ringan, sedang, berat
        public ?int $id = null // Digunakan untuk proses update
    ) {}

    /**
     * Build DTO dari Request Web / API
     */
    public static function fromRequest(Request $request, ?int $id = null): self 
    {
        return new self(
            pondok_id:        auth()->user()->pondok_id, // Kunci Tenant SaaS
            nama_pelanggaran: $request->nama_pelanggaran,
            poin:             (int) $request->poin,
            tingkat:          $request->tingkat,
            id:               $id
        );
    }
}