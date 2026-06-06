<?php

namespace App\Domains\Import\Resolvers;

use App\Models\Lemari;

class LemariResolver
{
    protected array $resolvedCache = [];
    protected ?string $currentCacheKey = null;

    /**
     * Resolve Lemari
     * Cari berdasarkan pondok + kamar + nama lemari.
     */
    public function resolve(int $pondokId, ?int $kamarId, ?array $payload): ?Lemari
    {
        // Pengecekan null-safety untuk payload dan field lemari
        if (empty($payload) || empty($payload['lemari']) || !$kamarId) {
            return null;
        }

        $namaLemari = trim($payload['lemari']);
        $this->currentCacheKey = "lemari_{$pondokId}_{$kamarId}_" . strtolower($namaLemari);

        if (isset($this->resolvedCache[$this->currentCacheKey])) {
            return $this->resolvedCache[$this->currentCacheKey];
        }

        $lemari = Lemari::where('pondok_id', $pondokId)
            ->where('kamar_id', $kamarId)
            ->where('nama', $namaLemari)
            ->first();

        if (!$lemari) {
            $lemari = new Lemari([
                'pondok_id'   => $pondokId,
                'kamar_id'    => $kamarId,
                'nama'        => $namaLemari,
                'tipe'        => $payload['lemari_tipe'] ?? null,
                'jumlah_slot' => $payload['jumlah_slot'] ?? 4
            ]);
        }

        return $lemari;
    }

    /**
     * Update data Lemari
     */
    public function update(Lemari $lemari, array $payload): Lemari
    {
        if (isset($payload['lemari_tipe'])) {
            $lemari->tipe = $payload['lemari_tipe'];
        }

        if (isset($payload['jumlah_slot'])) {
            $lemari->jumlah_slot = $payload['jumlah_slot'];
        }

        if ($lemari->isDirty() || !$lemari->exists) {
            $lemari->save();
        }

        if ($this->currentCacheKey) {
            $this->resolvedCache[$this->currentCacheKey] = $lemari;
        }

        return $lemari;
    }
}