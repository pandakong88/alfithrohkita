<?php

namespace App\Domains\Perizinan\DTO;

class UpdatePerizinanData
{
    public function __construct(
        public int $santri_id,
        public ?int $template_perizinan_id,
        public string $tanggal_keluar,
        public string $batas_kembali,
        public ?string $tanggal_kembali,
        public string $status,
        public ?string $keperluan,
        public ?string $keterangan
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            santri_id: $data['santri_id'],
            template_perizinan_id: $data['template_perizinan_id'] ?? null,
            tanggal_keluar: $data['tanggal_keluar'],
            batas_kembali: $data['batas_kembali'],
            tanggal_kembali: $data['tanggal_kembali'] ?? null,
            status: $data['status'],
            keperluan: $data['keperluan'] ?? null,
            keterangan: $data['keterangan'] ?? null,
        );
    }
}