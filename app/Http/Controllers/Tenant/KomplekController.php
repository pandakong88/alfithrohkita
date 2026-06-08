<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Komplek;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KomplekController extends Controller
{
    /**
     * Store a newly created complex in storage.
     */
    public function store(Request $request)
    {
        $pondokId = auth()->user()->pondok_id;

        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kompleks')->where(function ($query) use ($pondokId) {
                    return $query->where('pondok_id', $pondokId);
                }),
            ],
        ]);

        Komplek::create([
            'pondok_id' => $pondokId,
            'nama' => $validated['nama'],
        ]);

        return redirect()->route('tenant.kamar.index')->with('success', 'Komplek berhasil ditambahkan.');
    }

    /**
     * Update the specified complex in storage.
     */
    public function update(Request $request, Komplek $komplek)
    {
        $pondokId = auth()->user()->pondok_id;

        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kompleks')->ignore($komplek->id)->where(function ($query) use ($pondokId) {
                    return $query->where('pondok_id', $pondokId);
                }),
            ],
        ]);

        $komplek->update($validated);

        return redirect()->route('tenant.kamar.index')->with('success', 'Komplek berhasil diperbarui.');
    }

    /**
     * Remove the specified complex from storage.
     */
    public function destroy(Komplek $komplek)
    {
        $komplek->delete();

        return redirect()->route('tenant.kamar.index')->with('success', 'Komplek berhasil dihapus.');
    }
}
