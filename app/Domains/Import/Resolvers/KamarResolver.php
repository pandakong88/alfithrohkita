<?php

namespace App\Domains\Import\Resolvers;

use App\Models\Kamar;

class KamarResolver
{
    protected array $resolvedCache = [];
    protected ?string $currentCacheKey = null;

    /**
     * Resolve Kamar
     * Menggunakan firstOrNew agar hemat query dan tidak langsung insert
     */
    public function resolve(int $pondokId, ?int $komplekId, ?array $payload): ?Kamar
    {
        // 1. Early Return: Cek apakah field 'kamar' ada di payload (sesuai template)
        if (empty($payload) || empty($payload['kamar']) || !$komplekId) {
            return null;
        }

        $namaKamar = trim($payload['kamar']);
        $this->currentCacheKey = "kamar_{$pondokId}_{$komplekId}_" . strtolower($namaKamar);

        if (isset($this->resolvedCache[$this->currentCacheKey])) {
            return $this->resolvedCache[$this->currentCacheKey];
        }

        // 2. Gunakan firstOrNew (bukan create)
        $kamar = Kamar::where('pondok_id', $pondokId)
            ->where('komplek_id', $komplekId)
            ->where('nama', $namaKamar)
            ->first() ?? new Kamar([
                'pondok_id'  => $pondokId,
                'komplek_id' => $komplekId,
                'nama'       => $namaKamar,
            ]);

        return $kamar;
    }

    /**
     * Update data Kamar & Simpan ke DB
     */
    public function update(Kamar $kamar, array $payload): Kamar
    {
        // 3. Update kapasitas hanya jika field ada di template
        if (array_key_exists('kapasitas_kamar', $payload)) {
            $kamar->kapasitas = $payload['kapasitas_kamar'] ?? null;
        }

        // 4. Save hanya jika data baru (belum ada ID) atau ada perubahan
        if (!$kamar->exists || $kamar->isDirty()) {
            $kamar->save();
        }

        if ($this->currentCacheKey) {
            $this->resolvedCache[$this->currentCacheKey] = $kamar;
        }

        return $kamar;
    }
}