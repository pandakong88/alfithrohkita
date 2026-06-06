<?php

namespace App\Domains\Import\Resolvers;

use App\Models\Santri;

class SantriResolver
{
    /**
     * Resolve Santri
     * Ambil data lama atau siapkan instance baru (Belum di-save ke DB)
     */
    public function resolve(int $pondokId, ?array $payload): ?Santri
    {
        // 1. Early Return: Jika di template dinamis user gak milih / gak isi NIS, skip entitas ini!
        if (empty($payload) || empty($payload['nis'])) {
            return null;
        }

        // 2. Cari di database berdasarkan multi-tenant pondok_id dan NIS
        $santri = Santri::where('pondok_id', $pondokId)
            ->where('nis', $payload['nis'])
            ->first();

        // 3. JANGAN DI-CREATE DULU. Pakai 'new Santri' agar hemat query dan aman dari constraint NOT NULL
        if (!$santri) {
            $santri = new Santri([
                'pondok_id' => $pondokId,
                'nis'       => $payload['nis'],
                'status'    => $payload['status'] ?? 'active', // Default value jika di excel kosong
            ]);
        }

        return $santri;
    }

    /**
     * Isi/Update attribute data Santri di memory (Belum di-save ke DB)
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

        // 4. Hanya isi attribute jika field-nya diset/dipilih oleh user di template dinamisnya
        foreach ($fields as $field) {
            if (array_key_exists($field, $payload)) {
                // Gunakan properti object biasa, jangan panggil ->update() atau ->save() dulu!
                $santri->{$field} = $payload[$field] ?? null;
            }
        }

        // Default nama jika data baru dan user kelupaan gak masukin kolom nama di template
        if (!$santri->exists && empty($santri->nama_lengkap)) {
            $santri->nama_lengkap = 'Tanpa Nama';
        }

        // 5. Petakan Custom Fields jika ada field_key dinamis lainnya di payload
        $coreAndRelations = [
            'nis', 'nama_lengkap', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 
            'alamat', 'no_hp', 'status', 'tanggal_masuk', 'tanggal_keluar',
            'wali_nama', 'wali_nik', 'wali_no_hp', 'wali_alamat', 'wali_pekerjaan',
            'kelas', 'komplek', 'kamar', 'kapasitas_kamar',
            'lemari', 'lemari_tipe', 'jumlah_slot',
            'slot', 'slot_status', 'slot_keterangan'
        ];

        $customFields = $santri->custom_fields ?? [];
        $hasCustomChanges = false;
        foreach ($payload as $key => $value) {
            if (!in_array($key, $coreAndRelations)) {
                $customFields[$key] = $value;
                $hasCustomChanges = true;
            }
        }
        if ($hasCustomChanges) {
            $santri->custom_fields = $customFields;
        }

        return $santri;
    }
}