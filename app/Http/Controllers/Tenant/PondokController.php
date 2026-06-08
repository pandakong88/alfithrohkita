<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Pondok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PondokController extends Controller
{
    /**
     * Tampilkan halaman profil & pengaturan pondok
     */
    public function profile()
    {
        $pondok = Pondok::findOrFail(Auth::user()->pondok_id);
        return view('tenant.pondok.profile', compact('pondok'));
    }

    /**
     * Perbarui profil & pengaturan pondok
     */
    public function updateProfile(Request $request)
    {
        $pondok = Pondok::findOrFail(Auth::user()->pondok_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'nis_pattern' => 'nullable|string|max:100',
        ]);

        // Handler status checkbox
        $validated['nis_auto_generate'] = $request->has('nis_auto_generate') ? true : false;

        // Handler unggah logo
        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada
            if ($pondok->logo) {
                Storage::disk('public')->delete($pondok->logo);
            }
            $path = $request->file('logo')->store('logos', 'public');
            $validated['logo'] = $path;
        }

        $pondok->update($validated);

        return back()->with('success', 'Profil dan Pengaturan Pondok berhasil diperbarui.');
    }
}
