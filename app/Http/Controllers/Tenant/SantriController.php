<?php

namespace App\Http\Controllers\Tenant;

use App\Domains\Santri\Actions\CreateSantriAction;
use App\Domains\Santri\Actions\DeleteSantriAction;
use App\Domains\Santri\Actions\RestoreSantriAction;
use App\Domains\Santri\Actions\UpdateSantriAction;
use App\Domains\Santri\Actions\ImportSantriPreviewAction;
use App\Domains\Santri\Actions\CommitSantriImportAction;
use App\Domains\Santri\DTO\CreateSantriData;
use App\Domains\Santri\DTO\UpdateSantriData;
use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\SantriImportBatch;
use App\Models\Wali;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Exports\SantriTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class SantriController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | LIST SANTRI
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $santris = Santri::with('wali')
            ->where('pondok_id', auth()->user()->pondok_id)
            ->latest()
            ->get();

        return view('tenant.santri.index', compact('santris'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE FORM
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $walis = Wali::where('pondok_id', auth()->user()->pondok_id)
            ->orderBy('nama')
            ->get();

        return view('tenant.santri.create', compact('walis'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE SANTRI
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | EDIT SANTRI
    |--------------------------------------------------------------------------
    */

    public function edit(Santri $santri)
    {
        abort_if(
            $santri->pondok_id !== auth()->user()->pondok_id,
            403
        );

        $walis = Wali::where('pondok_id', auth()->user()->pondok_id)
            ->orderBy('nama')
            ->get();

        return view('tenant.santri.edit', compact('santri', 'walis'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE SANTRI
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, Santri $santri, UpdateSantriAction $action)
    {
        abort_if(
            $santri->pondok_id !== auth()->user()->pondok_id,
            403
        );

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

    /*
    |--------------------------------------------------------------------------
    | DELETE SANTRI
    |--------------------------------------------------------------------------
    */

    public function destroy(Santri $santri, DeleteSantriAction $action)
    {
        abort_if(
            $santri->pondok_id !== auth()->user()->pondok_id,
            403
        );

        $action->execute($santri);

        return back()->with('success', 'Santri berhasil dihapus.');
    }

    /*
    |--------------------------------------------------------------------------
    | TRASH SANTRI
    |--------------------------------------------------------------------------
    */

    public function trash()
    {
        $santris = Santri::onlyTrashed()
            ->where('pondok_id', auth()->user()->pondok_id)
            ->with('wali')
            ->latest()
            ->get();

        return view('tenant.santri.trash', compact('santris'));
    }

    /*
    |--------------------------------------------------------------------------
    | RESTORE SANTRI
    |--------------------------------------------------------------------------
    */

    public function restore($id, RestoreSantriAction $action)
    {
        $santri = Santri::onlyTrashed()
            ->where('pondok_id', auth()->user()->pondok_id)
            ->findOrFail($id);

        $action->execute($santri);

        return back()->with('success', 'Santri berhasil direstore.');
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT FORM
    |--------------------------------------------------------------------------
    */

    public function importForm()
    {
        return view('tenant.santri.import');
    }

    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD TEMPLATE
    |--------------------------------------------------------------------------
    */
    public function downloadTemplate()
    {
        return Excel::download(
            new SantriTemplateExport,
            'template-import-santri.xlsx'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PREVIEW IMPORT
    |--------------------------------------------------------------------------
    */

    public function previewImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        $batch = app(ImportSantriPreviewAction::class)
            ->execute($request->file('file'));

        return redirect()->route(
            'tenant.santri.import.preview.show',
            $batch->id
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW PREVIEW
    |--------------------------------------------------------------------------
    */

    public function showPreview(SantriImportBatch $batch)
    {
        abort_if(
            $batch->pondok_id !== auth()->user()->pondok_id,
            403
        );

        $rows = $batch->rows()
            ->orderBy('row_number')
            ->paginate(50);

        return view('tenant.santri.import-preview', [
            'batch' => $batch,
            'rows' => $rows
        ]);
    }
    /*
    |--------------------------------------------------------------------------
    | COMMIT IMPORT
    |--------------------------------------------------------------------------
    */

    public function importCommit(SantriImportBatch $batch)
    {
        abort_if(
            $batch->pondok_id !== auth()->user()->pondok_id,
            403
        );

        if ($batch->status !== 'preview') {
            return back()->with('error', 'Batch sudah diproses.');
        }

        app(CommitSantriImportAction::class)
            ->execute($batch);

        return redirect()
            ->route('tenant.santri.index')
            ->with('success', 'Batch berhasil disimpan ke database.');
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT HISTORY
    |--------------------------------------------------------------------------
    */

    public function importHistory()
    {
        $batches = SantriImportBatch::with(['uploader', 'committer'])
            ->where('pondok_id', auth()->user()->pondok_id)
            ->latest()
            ->get();

        return view('tenant.santri.import-history', compact('batches'));
    }
}