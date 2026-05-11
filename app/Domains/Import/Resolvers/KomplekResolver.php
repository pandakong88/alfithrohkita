<?php

namespace App\Domains\Import\Resolvers;

use App\Models\Komplek;

class KomplekResolver
{
    /**
     * Resolve Komplek
     * Cari komplek berdasarkan pondok + nama. Jika tidak ada → buat baru.
     */
    public function resolve(int $pondokId, ?array $payload): ?Komplek
    {
        // Null-safety check agar tidak fatal error jika payload null atau key 'komplek' kosong
        if (empty($payload) || empty($payload['komplek'])) {
            return null;
        }

        $namaKomplek = trim($payload['komplek']);

        $komplek = Komplek::where('pondok_id', $pondokId)
            ->where('nama', $namaKomplek)
            ->first();

        if (!$komplek) {
            $komplek = Komplek::create([
                'pondok_id' => $pondokId,
                'nama'      => $namaKomplek
            ]);
        }

        return $komplek;
    }

    /**
     * Update Komplek
     * Sinkronisasi data model dengan payload terbaru saat commit import.
     */
    public function update(Komplek $model, array $payload): bool
    {
        if (empty($payload['komplek'])) {
            return false;
        }

        $namaBaru = trim($payload['komplek']);

        // Hanya update jika namanya berubah
        if ($model->nama !== $namaBaru) {
            return $model->update([
                'nama' => $namaBaru
            ]);
        }

        return true;
    }
}