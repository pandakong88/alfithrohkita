<?php

namespace App\Domains\Import\Resolvers;

use App\Models\Kelas;

class KelasResolver
{
    protected array $resolvedCache = [];
    protected ?string $currentCacheKey = null;

    /**
     * Resolve Kelas
     * Menggunakan pola firstOrNew (bukan create)
     */
    public function resolve(int $pondokId, ?array $payload): ?Kelas
    {
        if (empty($payload) || empty($payload['kelas'])) {
            return null;
        }

        $namaKelas = trim($payload['kelas']);
        $this->currentCacheKey = "kelas_{$pondokId}_" . strtolower($namaKelas);

        if (isset($this->resolvedCache[$this->currentCacheKey])) {
            return $this->resolvedCache[$this->currentCacheKey];
        }

        // Menggunakan firstOrNew agar hemat query dan tidak langsung insert
        $kelas = Kelas::where('pondok_id', $pondokId)
            ->where('nama', $namaKelas)
            ->first() ?? new Kelas([
                'pondok_id' => $pondokId,
                'nama'      => $namaKelas
            ]);

        return $kelas;
    }

    /**
     * Update Kelas
     * Memanfaatkan isDirty() untuk meminimalisir query ke database
     */
    public function update(Kelas $model, array $payload): Kelas
    {
        if (array_key_exists('kelas', $payload)) {
            $model->nama = trim($payload['kelas']);
        }

        // Hanya save ke database jika ada perubahan (isDirty) atau model baru (exists == false)
        if ($model->isDirty() || !$model->exists) {
            $model->save();
        }

        if ($this->currentCacheKey) {
            $this->resolvedCache[$this->currentCacheKey] = $model;
        }

        return $model;
    }
}