<?php

namespace App\Domains\Import\Resolvers;

use App\Models\Kamar;

class KamarResolver
{
    /**
     * Resolve Kamar
     * Cari kamar berdasarkan pondok + nama kamar. Jika tidak ada, buat baru.
     */
    public function resolve(int $pondokId, ?int $komplekId, ?array $payload): ?Kamar
    {
        // Null-safety check untuk payload dan field kamar
        if (empty($payload) || empty($payload['kamar'])) {
            return null;
        }

        $namaKamar = trim($payload['kamar']);

        $kamar = Kamar::where('pondok_id', $pondokId)
            ->where('nama', $namaKamar)
            ->first();

        if (!$kamar) {
            $kamar = Kamar::create([
                'pondok_id'  => $pondokId,
                'komplek_id' => $komplekId,
                'nama'       => $namaKamar,
                'kapasitas'  => $payload['kapasitas_kamar'] ?? null,
            ]);
        }

        return $kamar;
    }

    /**
     * Update Kamar
     */
    public function update(Kamar $kamar, array $payload): Kamar
    {
        $data = [];

        if (isset($payload['kapasitas_kamar'])) {
            $data['kapasitas'] = $payload['kapasitas_kamar'];
        }

        if (!empty($data)) {
            $kamar->update($data);
        }

        return $kamar;
    }
}