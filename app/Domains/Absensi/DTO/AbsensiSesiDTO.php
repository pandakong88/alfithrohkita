<?php

namespace App\Domains\Absensi\DTO;

class AbsensiSesiDTO {
    public function __construct(
        public ?string $nama_sesi,
        public ?string $target_tipe,
        public ?int $target_id,
        public ?string $jam_mulai,
        public ?string $jam_selesai,
        public bool $is_active = true,
    ) {}

    public static function fromRequest($request): self {
        return new self(
            nama_sesi: $request->nama_sesi,
            target_tipe: $request->target_tipe, // Jangan lupa ini
            target_id: $request->target_id,
            jam_mulai: $request->jam_mulai,
            jam_selesai: $request->jam_selesai,
            // Jika is_active tidak dikirim (misal dari checkbox), default true
            is_active: $request->has('is_active') ? (bool)$request->is_active : true,
        );
    }

    public function toArray(): array {
        return [
            'nama_sesi'   => $this->nama_sesi,
            'target_tipe' => $this->target_tipe, // Masukkan ke array
            'target_id'   => $this->target_id,
            'jam_mulai'   => $this->jam_mulai,
            'jam_selesai' => $this->jam_selesai,
            'is_active'   => $this->is_active,   // Masukkan ke array
        ];
    }
}