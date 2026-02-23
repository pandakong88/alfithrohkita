<?php

namespace App\Http\Controllers\Tenant;
use App\Domains\Santri\Actions\CreateSantriAction;
use App\Domains\Santri\Actions\DeleteSantriAction;
use App\Domains\Santri\Actions\RestoreSantriAction;
use App\Domains\Santri\Actions\UpdateSantriAction;
use App\Domains\Santri\DTO\CreateSantriData;
use App\Domains\Santri\DTO\UpdateSantriData;
use App\Domains\Santri\Actions\ImportSantriPreviewAction;
use App\Models\SantriImportBatch;
use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\Wali;
use Illuminate\Http\Request;

class SantriController extends Controller
{
    public function index(Request $request)
    {
        $santris = Santri::with('wali')
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->status, fn($q) => $q->status($request->status))
            ->latest()
            ->paginate(10);

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
            'wali_id' => 'nullable|exists:walis,id',
            'wali_nama' => 'nullable|string|max:255',
            'wali_no_hp' => 'nullable|string|max:20',
    
            'nis' => 'required|string|max:50',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
        ]);
    
        // 🔥 Validasi custom: minimal pilih wali atau isi wali baru
        if (!$request->wali_id && !$request->wali_nama) {
            return back()
                ->withErrors(['wali' => 'Pilih wali atau isi data wali baru.'])
                ->withInput();
        }
    
        $dto = CreateSantriData::fromArray($request->all());
    
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
            'wali_id' => 'required|exists:walis,id',
            'nis' => 'required|string|max:50',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'status' => 'required|in:active,nonaktif,lulus,keluar',
        ]);

        $dto = UpdateSantriData::fromArray($request->all());

        $action->execute($santri, $dto);

        return redirect()->route('santri.index')
            ->with('success', 'Santri berhasil diperbarui.');
    }

    public function destroy(Santri $santri, DeleteSantriAction $action)
    {
        $action->execute($santri);

        return back()->with('success', 'Santri berhasil dihapus.');
    }

    public function restore($id, RestoreSantriAction $action)
    {
        $santri = Santri::withTrashed()->findOrFail($id);

        $action->execute($santri);

        return back()->with('success', 'Santri berhasil direstore.');
    }

    public function importPreview(Request $request, ImportSantriPreviewAction $action)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        $batch = $action->execute($request->file('file'));

        return redirect()
            ->route('tenant.santri.import.preview.show', $batch->id);
    }
    public function importForm()
    {
        return view('tenant.santri.import-form');
    }

    public function importPreviewShow(SantriImportBatch $batch)
    {
        // Pastikan tenant tidak bisa akses batch pondok lain
        if ($batch->pondok_id !== auth()->user()->pondok_id) {
            abort(403);
        }

        $rows = $batch->rows()->orderBy('row_number')->paginate(20);

        return view('tenant.santri.import-preview', compact('batch', 'rows'));
    }

}
