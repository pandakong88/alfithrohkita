<?php

namespace App\Domains\Wali\DTO;

class UpdateWaliData
{
    public function __construct(
        public string $nama,
        public ?string $nik,
        public string $no_hp,
        public ?string $alamat,
        public ?string $pekerjaan,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            nama: $data['nama'],
            nik: $data['nik'] ?? null,
            no_hp: $data['no_hp'],
            alamat: $data['alamat'] ?? null,
            pekerjaan: $data['pekerjaan'] ?? null,
        );
    }
}