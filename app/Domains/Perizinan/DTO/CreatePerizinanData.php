<?php

namespace App\Domains\Perizinan\DTO;

use Carbon\Carbon;

class CreatePerizinanData
{
    public function __construct(
        public int $santri_id,
        public int $template_perizinan_id,
        public Carbon $tanggal_keluar,
        public Carbon $batas_kembali,
        public ?string $keperluan = null,
        // UBAH: Jadi array agar bisa menampung banyak input variabel dinamis
        public ?array $keterangan = [], 
        public ?string $nomor_manual = null,
    ) {
        if ($this->batas_kembali->lt($this->tanggal_keluar)) {
            throw new \InvalidArgumentException(
                'Batas kembali tidak boleh sebelum tanggal keluar'
            );
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            santri_id: $data['santri_id'],
            template_perizinan_id: $data['template_perizinan_id'],
            tanggal_keluar: Carbon::parse($data['tanggal_keluar']),
            batas_kembali: Carbon::parse($data['batas_kembali']),
            keperluan: $data['keperluan'] ?? null,
            // Pastikan ini mengambil data variabel manual dari request
            keterangan: $data['variables'] ?? $data['keterangan'] ?? [],
            nomor_manual: $data['nomor_manual'] ?? null,
        );
    }

    public function durasiHari(): int
    {
        return $this->tanggal_keluar->diffInDays($this->batas_kembali) + 1;
    }
}