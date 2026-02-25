<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\Wali;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Domains\Santri\DTO\CreateSantriData;
use App\Domains\Santri\DTO\UpdateSantriData;
use App\Domains\Santri\Actions\CreateSantriAction;
use App\Domains\Santri\Actions\UpdateSantriAction;
use App\Domains\Santri\Actions\DeleteSantriAction;
use App\Domains\Santri\Actions\RestoreSantriAction;

class SantriController extends Controller
{
    public function index()
    {
        $santris = Santri::with(['wali'])
            ->latest()
            ->get();

        return view('tenant.santri.index', compact('santris'));
    }

    public function create()
    {
        $walis = Wali::orderBy('nama')->get();

        return view('tenant.santri.create', compact('walis'));
    }

    public function store(Request $request, CreateSantriAction $action)
    {
        $validated = $request->validate([
            'nis' => 'required|string|max:50',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string|max:20',
            'tanggal_masuk' => 'nullable|date',

            // wali mode
            'wali_id' => 'nullable|exists:walis,id',
            'wali_nama' => 'nullable|string|max:255',
            'wali_no_hp' => 'nullable|string|max:20',
        ]);

        $dto = CreateSantriData::fromArray($validated);

        $action->execute($dto);

        return redirect()
            ->route('tenant.santri.index')
            ->with('success', 'Santri berhasil dibuat.');
    }

    public function edit(Santri $santri)
    {
        $walis = Wali::orderBy('nama')->get();

        return view('tenant.santri.edit', compact('santri', 'walis'));
    }

    public function update(Request $request, Santri $santri, UpdateSantriAction $action)
    {
        $validated = $request->validate([
            'nis' => [
                'required',
                'string',
                'max:50',
                Rule::unique('santris', 'nis')
                    ->where(fn ($q) => $q->where('pondok_id', auth()->user()->pondok_id))
                    ->ignore($santri->id),
            ],
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string|max:20',
            'status' => ['required', Rule::in(['active', 'nonaktif', 'lulus', 'keluar'])],
            'tanggal_masuk' => 'nullable|date',
            'tanggal_keluar' => 'nullable|date',
            'wali_id' => 'nullable|exists:walis,id',
        ]);

        $dto = UpdateSantriData::fromArray($validated);

        $action->execute($santri, $dto);

        return redirect()
            ->route('tenant.santri.index')
            ->with('success', 'Santri berhasil diperbarui.');
    }

    public function destroy(Santri $santri, DeleteSantriAction $action)
    {
        $action->execute($santri);

        return back()->with('success', 'Santri berhasil dihapus.');
    }

    public function trash()
    {
        $santris = Santri::onlyTrashed()
            ->with('wali')
            ->latest()
            ->get();

        return view('tenant.santri.trash', compact('santris'));
    }

    public function restore($id, RestoreSantriAction $action)
    {
        $santri = Santri::onlyTrashed()->findOrFail($id);

        $action->execute($santri);

        return back()->with('success', 'Santri berhasil direstore.');
    }
}