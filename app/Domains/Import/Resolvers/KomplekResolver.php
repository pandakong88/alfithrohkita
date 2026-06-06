<?php

namespace App\Domains\Import\Resolvers;

use App\Models\Komplek;

class KomplekResolver
{
    protected array $resolvedCache = [];
    protected ?string $currentCacheKey = null;

    /**
     * Resolve Komplek
     * Gunakan firstOrNew untuk efisiensi
     */
    public function resolve(int $pondokId, ?array $payload): ?Komplek
    {
        if (empty($payload) || empty($payload['komplek'])) {
            return null;
        }

        $namaKomplek = trim($payload['komplek']);
        $this->currentCacheKey = "komplek_{$pondokId}_" . strtolower($namaKomplek);

        if (isset($this->resolvedCache[$this->currentCacheKey])) {
            return $this->resolvedCache[$this->currentCacheKey];
        }

        $komplek = Komplek::where('pondok_id', $pondokId)
            ->where('nama', $namaKomplek)
            ->first() ?? new Komplek([
                'pondok_id' => $pondokId,
                'nama'      => $namaKomplek
            ]);

        return $komplek;
    }

    /**
     * Update Komplek
     * Harus return objek Komplek (bukan bool) untuk konsistensi di CommitImportAction
     */
    public function update(Komplek $model, array $payload): Komplek
    {
        if (array_key_exists('komplek', $payload)) {
            $model->nama = trim($payload['komplek']);
        }

        // Hanya save ke database jika ada perubahan atau data baru
        if ($model->isDirty() || !$model->exists) {
            $model->save();
        }

        if ($this->currentCacheKey) {
            $this->resolvedCache[$this->currentCacheKey] = $model;
        }

        return $model;
    }
}