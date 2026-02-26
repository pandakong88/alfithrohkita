<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\SantriHandbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SantriHandbookController extends Controller
{
    public function index()
    {
        $handbooks = SantriHandbook::latest('release_date')->get();

        return view('tenant.handbook.index', compact('handbooks'));
    }

    public function create()
    {
        return view('tenant.handbook.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'version' => [
                'required',
                'string',
                Rule::unique('santri_handbooks', 'version')
                    ->where(fn($q) => $q->where('pondok_id', auth()->user()->pondok_id))
            ],
            'release_date' => 'required|date',
            'status' => ['required', Rule::in(['draft','published'])],
            'description' => 'nullable|string',
            'file' => 'required|mimes:pdf|max:10240',
        ]);

        $path = $request->file('file')
            ->store('handbooks');

        if ($request->status === 'published') {
            // auto archive versi lain
            SantriHandbook::where('pondok_id', auth()->user()->pondok_id)
                ->where('status', 'published')
                ->update(['status' => 'archived']);
        }

        SantriHandbook::create([
            'pondok_id'   => auth()->user()->pondok_id,
            'version'     => $request->version,
            'release_date'=> $request->release_date,
            'status'      => $request->status,
            'description' => $request->description,
            'file_path'   => $path,
            'created_by'  => Auth::id(),
        ]);

        return redirect()
            ->route('tenant.santri.handbook.index')
            ->with('success', 'Buku pedoman berhasil ditambahkan.');
    }

    public function edit(SantriHandbook $handbook)
    {
        return view('tenant.handbook.edit', compact('handbook'));
    }

    public function update(Request $request, SantriHandbook $handbook)
    {
        $request->validate([
            'version' => [
                'required',
                'string',
                Rule::unique('santri_handbooks', 'version')
                    ->where(fn($q) => $q->where('pondok_id', auth()->user()->pondok_id))
                    ->ignore($handbook->id),
            ],
            'release_date' => 'required|date',
            'status' => ['required', Rule::in(['draft','published','archived'])],
            'description' => 'nullable|string',
            'file' => 'nullable|mimes:pdf|max:10240',
        ]);

        if ($request->status === 'published') {
            SantriHandbook::where('pondok_id', auth()->user()->pondok_id)
                ->where('id', '!=', $handbook->id)
                ->where('status', 'published')
                ->update(['status' => 'archived']);
        }

        if ($request->hasFile('file')) {
            Storage::delete($handbook->file_path);

            $handbook->file_path = $request->file('file')
                ->store('handbooks');
        }

        $handbook->update([
            'version' => $request->version,
            'release_date' => $request->release_date,
            'status' => $request->status,
            'description' => $request->description,
        ]);

        return redirect()
            ->route('tenant.santri.handbook.index')
            ->with('success', 'Buku pedoman berhasil diperbarui.');
    }

    public function destroy(SantriHandbook $handbook)
    {
        Storage::delete($handbook->file_path);
        $handbook->delete();

        return back()->with('success', 'Buku pedoman dihapus.');
    }
}