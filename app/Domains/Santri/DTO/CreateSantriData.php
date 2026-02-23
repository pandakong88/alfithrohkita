<?php

namespace App\Domains\Santri\DTO;

class CreateSantriData
{
    public function __construct(
        public ?int $wali_id,
        public string $nis,
        public string $nama_lengkap,
        public string $jenis_kelamin,
        public ?string $tempat_lahir,
        public ?string $tanggal_lahir,
        public ?string $alamat,
        public ?string $no_hp,
        public ?string $tanggal_masuk,

        // 🔥 Data wali baru (optional)
        public ?string $wali_nama,
        public ?string $wali_no_hp,
        public ?string $wali_alamat,
        public ?string $wali_pekerjaan,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            wali_id: $data['wali_id'] ?? null,
            nis: $data['nis'],
            nama_lengkap: $data['nama_lengkap'],
            jenis_kelamin: $data['jenis_kelamin'],
            tempat_lahir: $data['tempat_lahir'] ?? null,
            tanggal_lahir: $data['tanggal_lahir'] ?? null,
            alamat: $data['alamat'] ?? null,
            no_hp: $data['no_hp'] ?? null,
            tanggal_masuk: $data['tanggal_masuk'] ?? null,

            wali_nama: $data['wali_nama'] ?? null,
            wali_no_hp: $data['wali_no_hp'] ?? null,
            wali_alamat: $data['wali_alamat'] ?? null,
            wali_pekerjaan: $data['wali_pekerjaan'] ?? null,
        );
    }
}