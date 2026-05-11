<?php

namespace App\Http\Controllers\Api;

use App\Domains\Perizinan\Actions\ReturnPerizinanAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\PerizinanDetailResource;
use App\Http\Resources\PerizinanListResource;
use App\Http\Resources\TemplatePerizinanResource;
use App\Models\Perizinan;
use App\Models\TemplatePerizinan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;

class PerizinanController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 📄 GET TEMPLATE (UNTUK FORM DINAMIS FLUTTER)
    |--------------------------------------------------------------------------
    */
    public function templates()
    {
        $templates = TemplatePerizinan::where('is_active', true)->get();

        return TemplatePerizinanResource::collection($templates);
    }

    /*
    |--------------------------------------------------------------------------
    | 📋 LIST PERIZINAN
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        // Gunakan scope query biar lebih rapi
        $query = Perizinan::with(['santri:id,nama_lengkap,nis', 'template:id,nama'])->latest();
    
        // 1. Filter Search (DIBUNGKUS group function agar orWhere tidak merusak filter status/date)
        if ($request->filled('search')) {
            $query->where(function ($mainQuery) use ($request) {
                $mainQuery->whereHas('santri', function ($q) use ($request) {
                    $q->where('nama_lengkap', 'like', "%$request->search%")
                      ->orWhere('nis', 'like', "%$request->search%");
                })
                // Tambahkan pencarian berdasarkan kode surat juga biar makin mantap
                ->orWhere('kode_surat', 'like', "%$request->search%");
            });
        }
    
        // 2. Filter Status
        if ($request->filled('status')) {
            $statuses = explode(',', $request->status);
            $query->whereIn('status', $statuses);
        }
    
        // 3. Filter Template
        if ($request->filled('template_id')) {
            $query->where('template_perizinan_id', $request->template_id);
        }
    
        // 4. Filter Waktu (Mewah & Anti-Bentrok)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            // Prioritaskan Custom Date dulu kalau ada
            $query->whereBetween('tanggal_keluar', [$request->start_date, $request->end_date]);
        } 
        elseif ($request->filled('date_range')) {
            // Baru cek presets (today, week, month)
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('tanggal_keluar', now()->toDateString());
                    break;
                case 'this_week':
                    $query->whereBetween('tanggal_keluar', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('tanggal_keluar', now()->month)
                        ->whereYear('tanggal_keluar', now()->year);
                    break;
            }
        }
        
        return PerizinanListResource::collection(
            $query->paginate($request->limit ?? 15)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 🔍 DETAIL PERIZINAN (UNTUK SCREEN DETAIL FLUTTER)
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $data = Perizinan::with([
            'santri.kelas',
            'santri.kamar',
            'template'
        ])->findOrFail($id);

        return new PerizinanDetailResource($data);
    }

    /*
    |--------------------------------------------------------------------------
    | ➕ CREATE PERIZINAN (ADMIN / KEAMANAN)
    |--------------------------------------------------------------------------
    */
   // App\Http\Controllers\Api\PerizinanController.php

    public function store(Request $request, \App\Domains\Perizinan\Actions\CreatePerizinanAction $action)
    {
        $request->validate([
            'santri_id' => 'required|exists:santris,id',
            'template_perizinan_id' => 'required|exists:template_perizinans,id',
            'tanggal_keluar' => 'required|date',
            'batas_kembali' => 'required|date|after:tanggal_keluar',
            'variables' => 'nullable|array' // Input manual dari Flutter
        ]);

        try {
            $data = \App\Domains\Perizinan\DTO\CreatePerizinanData::fromArray([
                'santri_id' => $request->santri_id,
                'template_perizinan_id' => $request->template_perizinan_id,
                'tanggal_keluar' => $request->tanggal_keluar,
                'batas_kembali' => $request->batas_kembali,
                'keperluan' => $request->variables['keperluan'] ?? null,
                'nomor_manual' => $request->nomor_manual,
                'keterangan' => $request->variables, // Dilempar ke action untuk di-map
            ]);

            $perizinan = $action->execute($data);

            return response()->json(['message' => 'Berhasil', 'data' => $perizinan]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 🔥 SCAN BARCODE (KEMBALI)
    |--------------------------------------------------------------------------
    */
    public function scan(Request $request, ReturnPerizinanAction $action)
    {
        // Cek apakah kode_surat ada di Body atau Query String
        $request->validate(['kode_surat' => 'required|string']);
    
        try {
            $perizinan = Perizinan::where('kode_surat', $request->kode_surat)
                ->with('santri') // Load relasi santri
                ->firstOrFail();
    
            // Eksekusi logic kembali
            $action->execute($perizinan);
    
            $perizinan->refresh();
            return response()->json([
                'success' => true,
                // Perbaikan panggil nama santri dari relasi $perizinan
                'message' => 'Santri ' . $perizinan->santri->nama . ' berhasil kembali.',
                'status'  => 'kembali', 
                'data'    => $perizinan // Kirim object lengkap biar Flutter dapet ID-nya
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kode surat tidak terdaftar!'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    /*
    |--------------------------------------------------------------------------
    | 🔧 MANUAL KEMBALI (FALLBACK TANPA SCAN)
    |--------------------------------------------------------------------------
    */
    public function manual($id, ReturnPerizinanAction $action)
    {
        try {
            $perizinan = Perizinan::with('santri')->findOrFail($id);
            
            // Cek apakah perizinan ini milik pondok si admin yang login
            if ($perizinan->pondok_id !== auth()->user()->pondok_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
    
            $result = $action->execute($perizinan);
    
            return response()->json([
                'success' => true,
                'message' => 'Berhasil! ' . $result->santri->nama_lengkap . ' kembali dengan status ' . $result->status
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 📄 PRINT PDF (UNTUK FLUTTER OPEN LINK)
    |--------------------------------------------------------------------------
    */
    public function print($id)
    {
        $data = Perizinan::with('santri')->findOrFail($id);

        $pdf = Pdf::loadHTML("
            <h3>Surat Izin</h3>
            <p>Nama: {$data->santri->nama_lengkap}</p>
            <p>Kode: {$data->kode_surat}</p>
            <p>Tanggal Keluar: {$data->tanggal_keluar}</p>
            <p>Batas Kembali: {$data->batas_kembali}</p>
        ");

        return $pdf->stream("surat-{$data->kode_surat}.pdf");
    }
}