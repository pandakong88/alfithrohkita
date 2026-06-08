<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\LemariSlot;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LemariSlotController extends Controller
{
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LemariSlot $slot)
    {
        $pondokId = auth()->user()->pondok_id;

        // Tenant check: Ensure the slot's wardrobe belongs to the current user's pondok
        if ($slot->lemari->pondok_id !== $pondokId) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'status' => 'required|string|in:dipakai,kosong,rusak,barang',
            'santri_id' => [
                'nullable',
                Rule::exists('santris', 'id')->where('pondok_id', $pondokId),
            ],
            'keterangan' => 'nullable|string|max:500',
        ]);

        // If santri is assigned, automatically force status to 'dipakai'
        if ($validated['santri_id']) {
            $validated['status'] = 'dipakai';
        } else {
            // If santri is not assigned, status cannot be 'dipakai' unless it's for general goods
            if ($validated['status'] === 'dipakai') {
                $validated['status'] = 'kosong';
            }
        }

        $slot->update([
            'status' => $validated['status'],
            'santri_id' => $validated['santri_id'] ?: null,
            'keterangan' => $validated['keterangan'],
        ]);

        return back()->with('success', "Slot Loker #{$slot->nomor_slot} berhasil diperbarui.");
    }
}
