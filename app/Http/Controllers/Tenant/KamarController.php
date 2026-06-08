<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Komplek;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KamarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pondokId = auth()->user()->pondok_id;

        $kompleks = Komplek::with(['kamars' => function ($q) {
            $q->withCount('santris')->with(['lemaris.slots']);
        }])->get();

        // Calculate statistics
        $totalKomplek = $kompleks->count();
        $totalKamar = Kamar::count();
        $totalKapasitas = Kamar::sum('kapasitas');
        $terisi = Santri::whereNotNull('kamar_id')->count();

        // Lemari & Slot Statistics
        $totalLemari = 0;
        $totalSlots = 0;
        $slotStats = [
            'dipakai' => 0,
            'kosong' => 0,
            'rusak' => 0,
            'barang' => 0,
        ];

        foreach ($kompleks as $komplek) {
            foreach ($komplek->kamars as $kamar) {
                foreach ($kamar->lemaris as $lemari) {
                    $totalLemari++;
                    foreach ($lemari->slots as $slot) {
                        $totalSlots++;
                        $status = strtolower(trim($slot->status));
                        if (array_key_exists($status, $slotStats)) {
                            $slotStats[$status]++;
                        } else {
                            $slotStats['kosong']++;
                        }
                    }
                }
            }
        }

        return view('tenant.asrama.index', compact(
            'kompleks',
            'totalKomplek',
            'totalKamar',
            'totalKapasitas',
            'terisi',
            'totalLemari',
            'totalSlots',
            'slotStats'
        ));
    }

    /**
     * Store a newly created room in storage.
     */
    public function store(Request $request)
    {
        $pondokId = auth()->user()->pondok_id;

        $validated = $request->validate([
            'komplek_id' => [
                'required',
                Rule::exists('kompleks', 'id')->where('pondok_id', $pondokId),
            ],
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kamars')->where(function ($query) use ($request) {
                    return $query->where('komplek_id', $request->komplek_id);
                }),
            ],
            'kapasitas' => 'required|integer|min:1',
        ]);

        Kamar::create([
            'pondok_id' => $pondokId,
            'komplek_id' => $validated['komplek_id'],
            'nama' => $validated['nama'],
            'kapasitas' => $validated['kapasitas'],
        ]);

        return redirect()->route('tenant.kamar.index')->with('success', 'Kamar berhasil ditambahkan.');
    }

    /**
     * Display the specified room.
     */
    public function show(Kamar $kamar)
    {
        // Load relationships
        $kamar->load(['kompleks', 'santris', 'lemaris.slots.santri']);

        // Available santri (active, and not assigned to any room)
        $availableSantris = Santri::active()
            ->whereNull('kamar_id')
            ->orderBy('nama_lengkap')
            ->get();

        return view('tenant.asrama.kamar-show', compact('kamar', 'availableSantris'));
    }

    /**
     * Update the specified room in storage.
     */
    public function update(Request $request, Kamar $kamar)
    {
        $pondokId = auth()->user()->pondok_id;

        $validated = $request->validate([
            'komplek_id' => [
                'required',
                Rule::exists('kompleks', 'id')->where('pondok_id', $pondokId),
            ],
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kamars')->ignore($kamar->id)->where(function ($query) use ($request) {
                    return $query->where('komplek_id', $request->komplek_id);
                }),
            ],
            'kapasitas' => 'required|integer|min:1',
        ]);

        $kamar->update($validated);

        return redirect()->route('tenant.kamar.index')->with('success', 'Kamar berhasil diperbarui.');
    }

    /**
     * Remove the specified room from storage.
     */
    public function destroy(Kamar $kamar)
    {
        // Set kamar_id to null for all occupant santris
        $kamar->santris()->update(['kamar_id' => null]);
        
        $kamar->delete();

        return redirect()->route('tenant.kamar.index')->with('success', 'Kamar berhasil dihapus.');
    }

    /**
     * Add an occupant (Santri) to the room.
     */
    public function addOccupant(Request $request, Kamar $kamar)
    {
        $pondokId = auth()->user()->pondok_id;

        $validated = $request->validate([
            'santri_id' => [
                'required',
                Rule::exists('santris', 'id')->where('pondok_id', $pondokId),
            ],
        ]);

        // Check if room is full
        if ($kamar->santris()->count() >= $kamar->kapasitas) {
            return back()->with('error', 'Kamar sudah penuh, tidak bisa menambah penghuni baru.');
        }

        $santri = Santri::findOrFail($validated['santri_id']);
        
        // Save using updated_by to track actor
        $santri->updated_by = auth()->id();
        $santri->kamar_id = $kamar->id;
        $santri->save();

        return back()->with('success', "Santri {$santri->nama_lengkap} berhasil ditambahkan ke kamar.");
    }

    /**
     * Remove an occupant (Santri) from the room.
     */
    public function removeOccupant(Kamar $kamar, Santri $santri)
    {
        // Ensure the santri belongs to this room
        if ($santri->kamar_id !== $kamar->id) {
            return back()->with('error', 'Santri tidak berada di kamar ini.');
        }

        // Also release any wardrobe slots assigned to this santri in this room's wardrobes
        foreach ($kamar->lemaris as $lemari) {
            $lemari->slots()->where('santri_id', $santri->id)->update([
                'santri_id' => null,
                'status' => 'kosong',
            ]);
        }

        // Remove from room
        $santri->updated_by = auth()->id();
        $santri->kamar_id = null;
        $santri->save();

        return back()->with('success', "Santri {$santri->nama_lengkap} berhasil dikeluarkan dari kamar.");
    }
}
