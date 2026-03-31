<?php

namespace App\Domains\Import\Resolvers;

use App\Models\Santri;

class SantriResolver
{
    /**
     * Resolve Santri
     * Cari berdasarkan pondok + NIS. Jika tidak ada, buat baru.
     */
    public function resolve(int $pondokId, ?array $payload): ?Santri
    {
        // Cegah TypeError jika payload null atau NIS kosong
        if (empty($payload) || empty($payload['nis'])) {
            return null;
        }

        $santri = Santri::where('pondok_id', $pondokId)
            ->where('nis', $payload['nis'])
            ->first();

        if (!$santri) {
            $santri = Santri::create([
                'pondok_id'      => $pondokId,
                'nis'            => $payload['nis'],
                'nama_lengkap'   => $payload['nama_lengkap'] ?? 'Tanpa Nama',
                'jenis_kelamin'  => $payload['jenis_kelamin'] ?? null,
                'tempat_lahir'   => $payload['tempat_lahir'] ?? null,
                'tanggal_lahir'  => $payload['tanggal_lahir'] ?? null,
                'alamat'         => $payload['alamat'] ?? null,
                'no_hp'          => $payload['no_hp'] ?? null,
                'status'         => $payload['status'] ?? 'active',
                'tanggal_masuk'  => $payload['tanggal_masuk'] ?? null,
                'tanggal_keluar' => $payload['tanggal_keluar'] ?? null,
            ]);
        }

        return $santri;
    }

    /**
     * Update data Santri
     */
    public function update(Santri $santri, array $payload): Santri
    {
        $fields = [
            'nama_lengkap',
            'jenis_kelamin',
            'tempat_lahir',
            'tanggal_lahir',
            'alamat',
            'no_hp',
            'status',
            'tanggal_masuk',
            'tanggal_keluar',
        ];

        $data = [];
        foreach ($fields as $field) {
            if (isset($payload[$field])) {
                $data[$field] = $payload[$field];
            }
        }

        if (!empty($data)) {
            $santri->update($data);
        }

        return $santri;
    }
}