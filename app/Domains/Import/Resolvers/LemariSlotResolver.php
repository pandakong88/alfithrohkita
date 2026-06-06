<?php

namespace App\Domains\Import\Resolvers;

use App\Models\LemariSlot;

class LemariSlotResolver
{
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
                'lemari_id' => $lemariId,
                'nomor_slot' => $payload['slot'],
                'status' => $payload['slot_status'] ?? 'kosong',
                'keterangan' => $payload['slot_keterangan'] ?? null
            ]);
        }

        return $slot;
    }

    public function update(LemariSlot $slot, array $payload): LemariSlot
    {
        if (isset($payload['slot_status'])) {
            $slot->status = $payload['slot_status'];
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
