<?php

namespace App\Domains\Import\Resolvers;

use App\Models\Lemari;

class LemariResolver
{
    /**
     * Resolve Lemari
     * Cari berdasarkan pondok + kamar + nama lemari.
     */
    public function resolve(int $pondokId, ?int $kamarId, ?array $payload): ?Lemari
    {
        // Pengecekan null-safety untuk payload dan field lemari
        if (empty($payload) || empty($payload['lemari'])) {
            return null;
        }

        $namaLemari = trim($payload['lemari']);

        $lemari = Lemari::where('pondok_id', $pondokId)
            ->where('kamar_id', $kamarId)
            ->where('nama', $namaLemari)
            ->first();

        if (!$lemari) {
            $lemari = Lemari::create([
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
        $data = [];

        if (isset($payload['lemari_tipe'])) {
            $data['tipe'] = $payload['lemari_tipe'];
        }

        if (isset($payload['jumlah_slot'])) {
            $data['jumlah_slot'] = $payload['jumlah_slot'];
        }

        if (!empty($data)) {
            $lemari->update($data);
        }

        return $lemari;
    }
}