<?php

namespace App\Http\Controllers\Tenant;

use App\Domains\Import\Actions\CommitImportAction;
use App\Domains\Import\Actions\PreviewImportAction;
use App\Domains\Import\Actions\RollbackImportAction;
use App\Exports\ImportErrorExport;
use App\Http\Controllers\Controller;
use App\Models\ImportBatch;
use App\Models\ImportTemplate;
use App\Models\Komplek;
use App\Models\Kamar;
use App\Models\Kelas;
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

        $kompleks = Komplek::where('pondok_id', auth()->user()->pondok_id)->orderBy('nama')->get();
        $kelas = Kelas::where('pondok_id', auth()->user()->pondok_id)->orderBy('nama')->get();
        $kamars = Kamar::where('pondok_id', auth()->user()->pondok_id)->orderBy('nama')->get();
        
        return view('tenant.import.upload', compact('templates', 'defaultTemplate', 'kompleks', 'kelas', 'kamars'));
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

        $dormStructure = $this->getDormStructure($batch);

        return view('tenant.import.preview', compact('batch', 'dormStructure'));
    }

    private function getDormStructure(ImportBatch $batch): array
    {
        $dormStructure = [];
        foreach ($batch->rows as $row) {
            $p = $row->payload;
            $komplek = $p['komplek'] ?? null;
            $kamar = $p['kamar'] ?? null;
            $lemari = $p['lemari'] ?? null;
            $slot = $p['slot'] ?? null;
            $status = $p['slot_status'] ?? 'kosong';
            
            if ($komplek && $kamar) {
                if (!isset($dormStructure[$komplek])) {
                    $dormStructure[$komplek] = [];
                }
                if (!isset($dormStructure[$komplek][$kamar])) {
                    $dormStructure[$komplek][$kamar] = [
                        'kapasitas' => $p['kapasitas_kamar'] ?? null,
                        'lemaris' => []
                    ];
                }
                if ($lemari) {
                    if (!isset($dormStructure[$komplek][$kamar]['lemaris'][$lemari])) {
                        $jumlahSlot = (int)($p['jumlah_slot'] ?? 4);
                        
                        $dormStructure[$komplek][$kamar]['lemaris'][$lemari] = [
                            'tipe' => $p['lemari_tipe'] ?? 'lemari',
                            'jumlah_slot' => $jumlahSlot,
                            'slots' => [
                                'dipakai' => 0,
                                'kosong' => 0,
                                'rusak' => 0,
                                'barang' => 0,
                                'details' => []
                            ]
                        ];
                        
                        // Initialize all slots to kosong by default
                        for ($i = 1; $i <= $jumlahSlot; $i++) {
                            $dormStructure[$komplek][$kamar]['lemaris'][$lemari]['slots']['details'][$i] = [
                                'status' => 'kosong',
                                'santri_nama' => null,
                                'santri_nis' => null
                            ];
                        }
                    }
                    if ($slot) {
                        $status = strtolower(trim($status));
                        if ($status === 'active') $status = 'dipakai';
                        if ($status === 'empty') $status = 'kosong';
                        if (!in_array($status, ['dipakai', 'kosong', 'rusak', 'barang'])) {
                            $status = 'kosong';
                        }
                        
                        // Overwrite with real occupant details
                        $dormStructure[$komplek][$kamar]['lemaris'][$lemari]['slots']['details'][(int)$slot] = [
                            'status' => $status,
                            'santri_nama' => $p['nama_lengkap'] ?? 'Tanpa Nama',
                            'santri_nis' => $p['nis'] ?? '-'
                        ];
                    }
                }
            }
        }

        // Count totals for each cabinet
        foreach ($dormStructure as $komplekKey => $kamars) {
            foreach ($kamars as $kamarKey => $kamarData) {
                foreach ($kamarData['lemaris'] as $lemariKey => $lemariData) {
                    $slots = $lemariData['slots']['details'];
                    $counts = [
                        'dipakai' => 0,
                        'kosong' => 0,
                        'rusak' => 0,
                        'barang' => 0
                    ];
                    foreach ($slots as $slotData) {
                        $st = $slotData['status'];
                        if (isset($counts[$st])) {
                            $counts[$st]++;
                        } else {
                            $counts['kosong']++;
                        }
                    }
                    $dormStructure[$komplekKey][$kamarKey]['lemaris'][$lemariKey]['slots']['dipakai'] = $counts['dipakai'];
                    $dormStructure[$komplekKey][$kamarKey]['lemaris'][$lemariKey]['slots']['kosong'] = $counts['kosong'];
                    $dormStructure[$komplekKey][$kamarKey]['lemaris'][$lemariKey]['slots']['rusak'] = $counts['rusak'];
                    $dormStructure[$komplekKey][$kamarKey]['lemaris'][$lemariKey]['slots']['barang'] = $counts['barang'];
                }
            }
        }

        return $dormStructure;
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
            $batch->update([
                'status' => 'processing',
                'processed_rows' => 0
            ]);

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
            $batch->update([
                'status' => 'failed'
            ]);
            return back()->with('error', 'Terjadi kesalahan saat commit: ' . $e->getMessage());
        }
    }

    public function status($batchId)
    {
        $batch = ImportBatch::where('pondok_id', auth()->user()->pondok_id)
            ->findOrFail($batchId);

        return response()->json([
            'status'         => $batch->status,
            'total_rows'     => $batch->total_rows,
            'processed_rows' => $batch->processed_rows,
            'valid_rows'     => $batch->valid_rows,
            'invalid_rows'   => $batch->invalid_rows,
        ]);
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
    
        $dormStructure = $this->getDormStructure($batch);
    
        return view('tenant.import.detail', compact('batch', 'dormStructure'));
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