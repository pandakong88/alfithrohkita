<?php

namespace App\Domains\Perizinan\DTO;

class CreateTemplatePerizinanData
{
    public function __construct(
        public string $nama,
        public ?string $deskripsi,
        public ?string $format_surat,
        public int $layout_print = 4,

        // 🔥 core baru
        public array $required_variables = [],

        // path file, bukan upload
        public ?string $file_pdf = null,

        public bool $is_active = true,
        public bool $is_default = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            nama: $data['nama'],
            deskripsi: $data['deskripsi'] ?? null,
            format_surat: $data['format_surat'] ?? null,
            layout_print: (int) ($data['layout_print'] ?? 4),

            required_variables: array_values($data['required_variables'] ?? []),

            file_pdf: $data['file_pdf'] ?? null,

            is_active: (bool) ($data['is_active'] ?? true),
            is_default: (bool) ($data['is_default'] ?? false),
        );
    }
}