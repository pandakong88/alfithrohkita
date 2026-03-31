<?php

namespace App\Domains\Import\Resolvers;

use App\Models\Kelas;

class KelasResolver
{
    /**
     * Resolve Kelas
     * Cari berdasarkan pondok + nama kelas. Jika tidak ada, buat baru.
     */
    public function resolve(int $pondokId, ?array $payload): ?Kelas
    {
        // Null-safety check agar tidak error jika payload kosong
        if (empty($payload) || empty($payload['kelas'])) {
            return null;
        }

        $namaKelas = trim($payload['kelas']);

        $kelas = Kelas::where('pondok_id', $pondokId)
            ->where('nama', $namaKelas)
            ->first();

        if (!$kelas) {
            $kelas = Kelas::create([
                'pondok_id' => $pondokId,
                'nama'      => $namaKelas
            ]);
        }

        return $kelas;
    }
}