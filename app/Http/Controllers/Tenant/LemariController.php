<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Lemari;
use App\Models\LemariSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LemariController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $pondokId = auth()->user()->pondok_id;

        $validated = $request->validate([
            'kamar_id' => [
                'required',
                Rule::exists('kamars', 'id')->where('pondok_id', $pondokId),
            ],
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('lemaris')->where(function ($query) use ($request) {
                    return $query->where('kamar_id', $request->kamar_id);
                }),
            ],
            'tipe' => 'required|string|in:lemari,rak_buku,rak_barang',
            'jumlah_slot' => 'required|integer|min:1|max:100',
        ]);

        DB::transaction(function () use ($validated, $pondokId) {
            $lemari = Lemari::create([
                'pondok_id' => $pondokId,
                'kamar_id' => $validated['kamar_id'],
                'nama' => $validated['nama'],
                'tipe' => $validated['tipe'],
                'jumlah_slot' => $validated['jumlah_slot'],
            ]);

            for ($i = 1; $i <= $validated['jumlah_slot']; $i++) {
                $lemari->slots()->create([
                    'nomor_slot' => $i,
                    'status' => 'kosong',
                ]);
            }
        });

        return back()->with('success', 'Lemari dan slot laci berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lemari $lemari)
    {
        $pondokId = auth()->user()->pondok_id;

        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('lemaris')->ignore($lemari->id)->where(function ($query) use ($lemari) {
                    return $query->where('kamar_id', $lemari->kamar_id);
                }),
            ],
            'tipe' => 'required|string|in:lemari,rak_buku,rak_barang',
            'jumlah_slot' => 'required|integer|min:1|max:100',
        ]);

        $newJumlahSlot = (int) $validated['jumlah_slot'];
        $oldJumlahSlot = (int) $lemari->jumlah_slot;

        try {
            DB::transaction(function () use ($lemari, $validated, $newJumlahSlot, $oldJumlahSlot) {
                if ($newJumlahSlot < $oldJumlahSlot) {
                    // Check if any slot to be removed is occupied
                    $occupiedExists = $lemari->slots()
                        ->where('nomor_slot', '>', $newJumlahSlot)
                        ->where(function ($query) {
                            $query->whereNotNull('santri_id')
                                  ->orWhere('status', 'dipakai');
                        })
                        ->exists();

                    if ($occupiedExists) {
                        throw new \Exception('Beberapa slot yang ingin dihapus sedang digunakan oleh santri.');
                    }

                    // Delete extra slots safely
                    $lemari->slots()->where('nomor_slot', '>', $newJumlahSlot)->delete();
                } elseif ($newJumlahSlot > $oldJumlahSlot) {
                    // Create new slots
                    for ($i = $oldJumlahSlot + 1; $i <= $newJumlahSlot; $i++) {
                        $lemari->slots()->create([
                            'nomor_slot' => $i,
                            'status' => 'kosong',
                        ]);
                    }
                }

                $lemari->update($validated);
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Data lemari berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lemari $lemari)
    {
        $lemari->delete();

        return back()->with('success', 'Lemari berhasil dihapus.');
    }
}
