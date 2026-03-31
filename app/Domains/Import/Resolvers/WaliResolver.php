<?php

namespace App\Domains\Import\Resolvers;

use App\Models\Wali;

class WaliResolver
{
    /**
     * Resolve Wali berdasarkan NIK, No HP, atau Nama.
     * Menggunakan logika fallback yang aman.
     */
    public function resolve(int $pondokId, ?array $payload): ?Wali
    {
        if (empty($payload)) {
            return null;
        }

        $nama = trim($payload['wali_nama'] ?? '');
        $nik = $payload['wali_nik'] ?? null;
        $noHp = $payload['wali_no_hp'] ?? null;

        if (empty($nama)) {
            return null;
        }

        // 1. Cari berdasarkan NIK
        if (!empty($nik)) {
            $wali = Wali::where('pondok_id', $pondokId)
                ->where('nik', $nik)
                ->first();
            if ($wali) return $wali;
        }

        // 2. Cari berdasarkan No HP
        if (!empty($noHp)) {
            $wali = Wali::where('pondok_id', $pondokId)
                ->where('no_hp', $noHp)
                ->first();
            if ($wali) return $wali;
        }

        // 3. Cari berdasarkan Nama
        $wali = Wali::where('pondok_id', $pondokId)
            ->whereRaw('LOWER(nama) = ?', [strtolower($nama)])
            ->first();

        // 4. Buat baru jika tidak ada
        if (!$wali) {
            $wali = Wali::create([
                'pondok_id' => $pondokId,
                'nama'      => $nama,
                'nik'       => $nik,
                'no_hp'     => $noHp,
                'alamat'    => $payload['wali_alamat'] ?? null,
                'pekerjaan' => $payload['wali_pekerjaan'] ?? null,
            ]);
        }

        return $wali;
    }

    /**
     * Update data Wali
     */
    public function update(Wali $wali, array $payload): Wali
    {
        $mapping = [
            'wali_nama'      => 'nama',
            'wali_nik'       => 'nik',
            'wali_no_hp'     => 'no_hp',
            'wali_alamat'    => 'alamat',
            'wali_pekerjaan' => 'pekerjaan',
        ];

        $updateData = [];

        foreach ($mapping as $payloadKey => $dbColumn) {
            if (isset($payload[$payloadKey]) && !empty($payload[$payloadKey])) {
                $updateData[$dbColumn] = $payload[$payloadKey];
            }
        }

        if (!empty($updateData)) {
            $wali->update($updateData);
        }

        return $wali;
    }
}