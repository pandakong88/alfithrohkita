<?php

namespace App\Domains\Import\Resolvers;

use App\Models\Wali;

class WaliResolver
{
    /**
     * Cache memori untuk menyimpan data wali yang sudah benar-benar tersimpan (punya ID)
     */
    protected array $resolvedCache = [];

    /**
     * Temporary key untuk menyimpan alamat cache baris berjalan
     */
    protected ?string $currentCacheKey = null;

    /**
     * Resolve Wali berdasarkan NIK, No HP, atau Nama.
     */
    public function resolve(int $pondokId, ?array $payload): ?Wali
    {
        if (empty($payload)) {
            return null;
        }

        $nama = trim($payload['wali_nama'] ?? '');
        $nik = !empty($payload['wali_nik']) ? trim($payload['wali_nik']) : null;
        $noHp = !empty($payload['wali_no_hp']) ? trim($payload['wali_no_hp']) : null;

        if (empty($nama)) {
            return null;
        }

        // 1. Cek Runtime Cache Terlebih Dahulu (Menghemat Query DB)
        $this->currentCacheKey = $this->generateCacheKey($pondokId, $nik, $noHp, $nama);
        if (isset($this->resolvedCache[$this->currentCacheKey])) {
            return $this->resolvedCache[$this->currentCacheKey];
        }

        $wali = null;

        // 2. Cari berdasarkan NIK
        if (!empty($nik)) {
            $wali = Wali::where('pondok_id', $pondokId)
                ->where('nik', $nik)
                ->first();
        }

        // 3. Cari berdasarkan No HP (jika NIK tidak ketemu)
        if (!$wali && !empty($noHp)) {
            $wali = Wali::where('pondok_id', $pondokId)
                ->where('no_hp', $noHp)
                ->first();
        }

        // 4. Cari berdasarkan Nama (jika NIK & No HP tidak ketemu)
        if (!$wali) {
            $wali = Wali::where('pondok_id', $pondokId)
                ->whereRaw('LOWER(nama) = ?', [strtolower($nama)])
                ->first();
        }

        // 5. JANGAN LANGSUNG CREATE. Buat instance baru di memory saja.
        if (!$wali) {
            $wali = new Wali([
                'pondok_id' => $pondokId,
                'nama'      => $nama,
            ]);
        }

        return $wali;
    }

    /**
     * Update data Wali & Simpan ke DB agar dapet ID buat Cache
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

        $hasChanges = false;

        foreach ($mapping as $payloadKey => $dbColumn) {
            // Gunakan array_key_exists agar mendukung template dinamis (kolom yang gak dipilih gak bakal ngerusak data lama)
            if (array_key_exists($payloadKey, $payload)) {
                $value = trim($payload[$payloadKey] ?? '');
                
                // Jika nilai di excel berbeda dengan di DB, update propertinya
                if ($wali->{$dbColumn} !== $value) {
                    $wali->{$dbColumn} = !empty($value) ? $value : null;
                    $hasChanges = true;
                }
            }
        }

        // 6. Jika ini Wali baru ATAU ada perubahan data lama, eksekusi SAVE (Hanya 1 kali query write!)
        if (!$wali->exists || $hasChanges) {
            $wali->save();
        }

        // 7. Simpan objek yang SUDAH PUNYA ID ini ke dalam runtime cache
        if ($this->currentCacheKey) {
            $this->resolvedCache[$this->currentCacheKey] = $wali;
        }

        return $wali;
    }

    /**
     * Membuat unique key untuk cache
     */
    private function generateCacheKey(int $pondokId, ?string $nik, ?string $noHp, string $nama): string
    {
        if ($nik) {
            return "wali_{$pondokId}_nik_{$nik}";
        }
        
        if ($noHp) {
            return "wali_{$pondokId}_nohp_{$noHp}";
        }

        $cleanNama = str_replace(' ', '', strtolower($nama));
        return "wali_{$pondokId}_nama_{$cleanNama}";
    }
}