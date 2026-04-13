<?php

namespace App\Domains\Perizinan\DTO;

class UpdateTemplateData
{
    public function __construct(
        public string $nama,
        public ?string $deskripsi,
        public ?string $format_surat,
        public string $layout_print,
        public bool $is_active,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            nama: $data['nama'],
            deskripsi: $data['deskripsi'] ?? null,
            format_surat: $data['format_surat'] ?? null,
            layout_print: $data['layout_print'],
            is_active: $data['is_active'] ?? true,
        );
    }
}