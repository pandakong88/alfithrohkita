<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\SantriHandbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

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

        // 🔥 Upload ke PUBLIC /handbooks
        $destination = public_path('handbooks');

        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }

        $filename = Str::random(40) . '.' .
            $request->file('file')->getClientOriginalExtension();

        $request->file('file')->move($destination, $filename);

        $path = 'handbooks/' . $filename;

        if ($request->status === 'published') {
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

        // 🔥 Jika upload file baru
        if ($request->hasFile('file')) {

            // Hapus file lama
            $oldPath = public_path($handbook->file_path);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }

            // Upload file baru
            $destination = public_path('handbooks');

            $filename = Str::random(40) . '.' .
                $request->file('file')->getClientOriginalExtension();

            $request->file('file')->move($destination, $filename);

            $handbook->file_path = 'handbooks/' . $filename;
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
        // 🔥 Hapus file fisik
        $filePath = public_path($handbook->file_path);

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $handbook->delete();

        return back()->with('success', 'Buku pedoman dihapus.');
    }
}