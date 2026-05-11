<?php

namespace App\Domains\Absensi\DTO;

use Illuminate\Http\Request;

class AbsensiDTO {
    public function __construct(
        public int $pondok_id,      // Tambahkan ini untuk keamanan Tenant
        public int $santri_id,
        public int $sesi_id,
        public string $tanggal,
        public string $status,
        public int $input_by,       // Tambahkan ini untuk Audit
        public string $metode = 'manual',
        public ?string $keterangan = null,
    ) {}

    /**
     * Helper khusus dari Request agar Controller tetap tipis
     */
    public static function fromRequest(Request $request): self {
        return new self(
            pondok_id: auth()->user()->pondok_id,
            santri_id: (int) $request->santri_id,
            sesi_id:   (int) $request->sesi_id,
            tanggal:   $request->tanggal,
            status:    $request->status,
            input_by:  auth()->id(),
            metode:    $request->metode ?? 'manual',
            keterangan: $request->keterangan ?? null,
        );
    }
}