<?php

namespace App\Domains\Import\Resolvers;

use App\Models\LemariSlot;

class LemariSlotResolver
{
    /*
    |--------------------------------------------------------------------------
    | Nilai ENUM yang valid untuk kolom `status`
    |--------------------------------------------------------------------------
    */
    private const VALID_STATUSES = ['dipakai', 'kosong', 'rusak', 'barang'];

    /**
     * Normalisasi nilai status dari berbagai kemungkinan input
     * (bahasa Inggris, variasi kapitalisasi, dll.)
     */
    private function normalizeStatus(?string $status): string
    {
        if (empty($status)) {
            return 'kosong';
        }

        $map = [
            // Bahasa Inggris
            'active'   => 'dipakai',
            'used'     => 'dipakai',
            'occupied' => 'dipakai',
            'empty'    => 'kosong',
            'free'     => 'kosong',
            'available'=> 'kosong',
            'broken'   => 'rusak',
            'damaged'  => 'rusak',
            'storage'  => 'barang',
            'goods'    => 'barang',
            // Bahasa Indonesia (normalisasi case)
            'dipakai'  => 'dipakai',
            'kosong'   => 'kosong',
            'rusak'    => 'rusak',
            'barang'   => 'barang',
        ];

        $normalized = $map[strtolower(trim($status))] ?? null;

        // Jika tidak dikenal, gunakan default 'kosong'
        return in_array($normalized, self::VALID_STATUSES) ? $normalized : 'kosong';
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Slot
    |--------------------------------------------------------------------------
    */

    public function resolve(int $pondokId, ?int $lemariId, ?array $payload): ?LemariSlot
    {
        if (!$lemariId || empty($payload['slot'])) {
            return null;
        }

        $slot = LemariSlot::where('lemari_id', $lemariId)
            ->where('nomor_slot', $payload['slot'])
            ->first();

        if (!$slot) {
            $slot = new LemariSlot([
                'lemari_id'  => $lemariId,
                'nomor_slot' => $payload['slot'],
                'status'     => $this->normalizeStatus($payload['slot_status'] ?? null),
                'keterangan' => $payload['slot_keterangan'] ?? null,
            ]);
        }

        return $slot;
    }

    public function update(LemariSlot $slot, array $payload): LemariSlot
    {
        if (isset($payload['slot_status'])) {
            $slot->status = $this->normalizeStatus($payload['slot_status']);
        }

        if (isset($payload['slot_keterangan'])) {
            $slot->keterangan = $payload['slot_keterangan'];
        }

        if ($slot->isDirty() || !$slot->exists) {
            $slot->save();
        }

        return $slot;
    }
}
