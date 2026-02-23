<?php

namespace App\Domains\Santri\DTO;

class UpdateSantriData
{
    public function __construct(
        public int $wali_id,
        public string $nis,
        public string $nama_lengkap,
        public string $jenis_kelamin,
        public ?string $tempat_lahir,
        public ?string $tanggal_lahir,
        public ?string $alamat,
        public ?string $no_hp,
        public string $status,
        public ?string $tanggal_masuk,
        public ?string $tanggal_keluar,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            wali_id: $data['wali_id'],
            nis: $data['nis'],
            nama_lengkap: $data['nama_lengkap'],
            jenis_kelamin: $data['jenis_kelamin'],
            tempat_lahir: $data['tempat_lahir'] ?? null,
            tanggal_lahir: $data['tanggal_lahir'] ?? null,
            alamat: $data['alamat'] ?? null,
            no_hp: $data['no_hp'] ?? null,
            status: $data['status'],
            tanggal_masuk: $data['tanggal_masuk'] ?? null,
            tanggal_keluar: $data['tanggal_keluar'] ?? null,
        );
    }
}
