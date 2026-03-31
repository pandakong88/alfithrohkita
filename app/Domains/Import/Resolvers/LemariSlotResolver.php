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

    public function resolve(int $lemariId, array $payload): ?LemariSlot
    {
        if (empty($payload['slot'])) {
            return null;
        }

        $slot = LemariSlot::where('lemari_id', $lemariId)
            ->where('nomor_slot', $payload['slot'])
            ->first();

        if (!$slot) {

            $slot = LemariSlot::create([
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
        $data = [];

        if (isset($payload['slot_status'])) {
            $data['status'] = $payload['slot_status'];
        }

        if (isset($payload['slot_keterangan'])) {
            $data['keterangan'] = $payload['slot_keterangan'];
        }

        if (!empty($data)) {
            $slot->update($data);
        }

        return $slot;
    }
}
