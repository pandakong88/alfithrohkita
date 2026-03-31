<?php

namespace App\Http\Controllers\Tenant;

use App\Domains\Import\Actions\CommitImportAction;
use App\Domains\Import\Actions\PreviewImportAction;
use App\Domains\Import\Actions\RollbackImportAction;
use App\Exports\ImportErrorExport;
use App\Http\Controllers\Controller;
use App\Models\ImportBatch;
use App\Models\ImportTemplate;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function index()
    {
        $templates = ImportTemplate::where('pondok_id', auth()->user()->pondok_id)
            ->orderBy('nama_template')
            ->get();
    
        return view('tenant.import.upload', compact('templates'));
    }
    /*
    |--------------------------------------------------------------------------
    | Upload Excel → Preview Import
    |--------------------------------------------------------------------------
    */

    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
            'template_id' => 'required'
        ]);

        $batch = app(PreviewImportAction::class)->execute(
            auth()->user()->pondok_id,
            auth()->id(),
            $request->template_id,
            $request->file('file'),
            $request->mode_missing_nis,
            $request->mode_existing_nis
        );

        return redirect()->route('tenant.import.show', $batch->id);
    }


    /*
    |--------------------------------------------------------------------------
    | Halaman Preview Import
    |--------------------------------------------------------------------------
    */

    public function show($batchId)
    {
        $batch = ImportBatch::with('rows')
            ->where('pondok_id', auth()->user()->pondok_id)
            ->findOrFail($batchId);

        return view('tenant.import.preview', compact('batch'));
    }


    /*
    |--------------------------------------------------------------------------
    | Commit Import
    |--------------------------------------------------------------------------
    */

    public function commit($batchId)
    {
        app(CommitImportAction::class)->execute($batchId);

        return redirect()
            ->route('tenant.import.show', $batchId)
            ->with('success', 'Import berhasil dijalankan');
    }

    public function history()
    {
        $batches = ImportBatch::with('uploader')
            ->where('pondok_id', auth()->user()->pondok_id)
            ->latest()
            ->paginate(20);

        return view('tenant.import.history', compact('batches'));
    }

    public function detail($batchId)
    {
        $batch = ImportBatch::with([
            'rows',
            'changes'
        ])
        ->where('pondok_id', auth()->user()->pondok_id)
        ->findOrFail($batchId);
    
        return view('tenant.import.detail', compact('batch'));
    }

    public function downloadErrors($batchId)
    {
        return Excel::download(
            new ImportErrorExport($batchId),
            'import_errors_'.$batchId.'.xlsx'
        );
    }

    public function rollback($batchId)
    {
        app(RollbackImportAction::class)->execute($batchId);

        return back()->with('success','Import berhasil di rollback');
    }
}