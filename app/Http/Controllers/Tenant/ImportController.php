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
use App\Jobs\ProcessImportJob;

class ImportController extends Controller
{
    public function index()
    {
        $templates = ImportTemplate::with('fields')
            ->where('pondok_id', auth()->user()->pondok_id)
            ->orderBy('nama_template')
            ->get();
        
        // Ambil default template (bisa yang pertama atau yang namanya 'Default')
        $defaultTemplate = $templates->first(); 
        
        return view('tenant.import.upload', compact('templates', 'defaultTemplate'));
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
            'template_id' => 'required|exists:import_templates,id' // Tambahan validasi
        ]);

        try {
            $batch = app(PreviewImportAction::class)->execute(
                auth()->user()->pondok_id,
                auth()->id(),
                $request->template_id,
                $request->file('file'),
                $request->mode_missing_nis ?? 'error', // Default aman
                $request->mode_existing_nis ?? 'update'
            );

            return redirect()->route('tenant.import.show', $batch->id)
                             ->with('success', 'File berhasil dipreview');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
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
        $batch = ImportBatch::where('pondok_id', auth()->user()->pondok_id)
            ->findOrFail($batchId);

        if ($batch->status === 'committed') {
            return back()->with('error', 'Batch ini sudah pernah di-commit.');
        }

        try {
            if ($batch->total_rows > 100) {
                ProcessImportJob::dispatch($batchId, auth()->id());
                return redirect()
                    ->route('tenant.import.history')
                    ->with('success', 'Batch dengan data besar sedang diproses di latar belakang. Silakan cek halaman ini secara berkala.');
            }

            // Kita bungkus di controller agar lebih aman
            app(CommitImportAction::class)->execute($batchId);

            return redirect()
                ->route('tenant.import.detail', $batchId) // Redirect ke detail supaya bisa lihat hasil perubahannya
                ->with('success', 'Import berhasil dijalankan dan data sudah tersinkronisasi.');
        } catch (\Exception $e) {
            // Log errornya di sini jika perlu
            return back()->with('error', 'Terjadi kesalahan saat commit: ' . $e->getMessage());
        }
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