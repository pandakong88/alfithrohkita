<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Wali;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Domains\Wali\DTO\CreateWaliData;
use App\Domains\Wali\DTO\UpdateWaliData;
use App\Domains\Wali\Actions\CreateWaliAction;
use App\Domains\Wali\Actions\UpdateWaliAction;
use App\Domains\Wali\Actions\DeleteWaliAction;
use App\Domains\Wali\Actions\RestoreWaliAction;
use App\Models\WaliImportBatch;

class WaliController extends Controller
{
    public function index()
    {
        $walis = Wali::withCount('santris')
            ->latest()
            ->get();

        return view('tenant.wali.index', compact('walis'));
    }

    public function create()
    {
        return view('tenant.wali.create');
    }

    public function store(Request $request, CreateWaliAction $action)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => 'nullable|string|max:50',
            'no_hp' => 'required|string|max:20',
            'alamat' => 'nullable|string',
            'pekerjaan' => 'nullable|string|max:255',
        ]);

        $dto = CreateWaliData::fromArray($validated);

        $action->execute($dto);

        return redirect()
            ->route('tenant.wali.index')
            ->with('success', 'Wali berhasil dibuat.');
    }

    public function show(Wali $wali)
    {
        abort_if(
            $wali->pondok_id !== auth()->user()->pondok_id,
            403
        );

        $wali->load([
            'santris.kelas',
            'santris.kamar.kompleks',
            'creator',
            'updater'
        ]);

        return view('tenant.wali.show', compact('wali'));
    }

    public function edit(Wali $wali)
    {
        return view('tenant.wali.edit', compact('wali'));
    }

    public function update(Request $request, Wali $wali, UpdateWaliAction $action)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => 'nullable|string|max:50',
            'no_hp' => [
                'required',
                'string',
                'max:20',
                Rule::unique('walis', 'no_hp')
                    ->where(fn ($q) => $q->where('pondok_id', auth()->user()->pondok_id))
                    ->ignore($wali->id),
            ],
            'alamat' => 'nullable|string',
            'pekerjaan' => 'nullable|string|max:255',
        ]);

        $dto = UpdateWaliData::fromArray($validated);

        $action->execute($wali, $dto);

        return redirect()
            ->route('tenant.wali.index')
            ->with('success', 'Wali berhasil diperbarui.');
    }

    public function destroy(Wali $wali, DeleteWaliAction $action)
    {
        $action->execute($wali);

        return back()->with('success', 'Wali berhasil dihapus.');
    }

    public function trash()
    {
        $walis = Wali::onlyTrashed()
            ->latest()
            ->paginate(10);

        return view('tenant.wali.trash', compact('walis'));
    }

    public function restore($id, RestoreWaliAction $action)
    {
        $wali = Wali::onlyTrashed()->findOrFail($id);

        $action->execute($wali);

        return back()->with('success', 'Wali berhasil direstore.');
    }

    public function ajaxStore(Request $request, CreateWaliAction $action)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'pekerjaan' => 'nullable|string|max:255', // Tambahkan ini jika di modal ada input pekerjaan
        ]);

        $dto = CreateWaliData::fromArray($validated);
        $wali = $action->execute($dto);

        // Kirim response yang sesuai dengan ekspektasi Javascript
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $wali->id,
                'nama' => $wali->nama,
                'no_hp' => $wali->no_hp,
                'text' => $wali->nama . ' (' . $wali->no_hp . ')' // Untuk kemudahan di Select2
            ]
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT FORM
    |--------------------------------------------------------------------------
    |*/
    public function importForm()
    {
        return view('tenant.wali.import');
    }

    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD TEMPLATE
    |--------------------------------------------------------------------------
    |*/
    public function downloadTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\WaliTemplateExport,
            'template-import-wali.xlsx'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PREVIEW IMPORT
    |--------------------------------------------------------------------------
    |*/
    public function previewImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        $batch = app(\App\Domains\Wali\Actions\ImportWaliPreviewAction::class)
            ->execute($request->file('file'));

        return redirect()->route(
            'tenant.wali.import.preview.show',
            $batch->id
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW PREVIEW
    |--------------------------------------------------------------------------
    |*/
    public function showPreview(WaliImportBatch $batch)
    {
        abort_if(
            $batch->pondok_id !== auth()->user()->pondok_id,
            403
        );

        $rows = $batch->rows()
            ->orderBy('row_number')
            ->paginate(50);

        return view('tenant.wali.import-preview', [
            'batch' => $batch,
            'rows' => $rows
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | COMMIT IMPORT
    |--------------------------------------------------------------------------
    |*/
    public function importCommit(WaliImportBatch $batch)
    {
        abort_if(
            $batch->pondok_id !== auth()->user()->pondok_id,
            403
        );

        if ($batch->status !== 'preview') {
            return back()->with('error', 'Batch sudah diproses.');
        }

        app(\App\Domains\Wali\Actions\CommitWaliImportAction::class)
            ->execute($batch);

        return redirect()
            ->route('tenant.wali.index')
            ->with('success', 'Batch berhasil disimpan ke database.');
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT HISTORY
    |--------------------------------------------------------------------------
    |*/
    public function importHistory()
    {
        $batches = WaliImportBatch::with(['uploader', 'committer'])
            ->where('pondok_id', auth()->user()->pondok_id)
            ->latest()
            ->get();

        return view('tenant.wali.import-history', compact('batches'));
    }
}