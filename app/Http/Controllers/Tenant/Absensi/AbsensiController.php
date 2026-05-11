<?php

namespace App\Http\Controllers\Tenant\Absensi;

use App\Http\Controllers\Controller;
use App\Domains\Absensi\Actions\CreateAbsensiAction;
// use App\Domains\Absensi\DTO\AbsensiDTO; // Pastikan nama file DTO kamu sesuai
use App\Models\AbsensiSesi;
use App\Models\Santri;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    public function pilihSesi(Request $request)
    {
        $tanggal = $request->get('tanggal', date('Y-m-d'));
    
        $sesis = AbsensiSesi::active()
            ->with(['absensis' => function($q) use ($tanggal) {
                $q->where('tanggal', $tanggal);
            }])
            ->get()
            ->map(function($sesi) {
                // Kita cuma hitung yang statusnya "bermasalah"
                $sesi->jumlah_alfa = $sesi->absensis->where('status', 'alfa')->count();
                $sesi->jumlah_izin_sakit = $sesi->absensis->whereIn('status', ['izin', 'sakit'])->count();
                $sesi->jumlah_terlambat = $sesi->absensis->where('status', 'terlambat')->count();
                
                // Total yang "Tidak Hadir" (Alfa + Izin + Sakit)
                $sesi->total_absen = $sesi->jumlah_alfa + $sesi->jumlah_izin_sakit;
                
                return $sesi;
            });
    
        return view('tenant.absensi.pilih-sesi', compact('sesis', 'tanggal'));
    }

    public function index($sesi_id = null, Request $request)
    {
        $pondokId = auth()->user()->pondok_id;
    
        // 1. Setup Filter (Bulan & Tahun)
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        
        // Ambil sesi aktif
        if (!$sesi_id) {
            $sesi_id = $request->get('sesi_id', AbsensiSesi::where('pondok_id', $pondokId)->first()?->id);
        }
        
        // Jika tidak ada sesi sama sekali di pondok ini
        if (!$sesi_id) {
            return redirect()->back()->with('error', 'Belum ada sesi absensi.');
        }
    
        $sesiAktif = AbsensiSesi::where('pondok_id', $pondokId)->findOrFail($sesi_id);
        $allSesis = AbsensiSesi::where('pondok_id', $pondokId)->get();
        
        $dateObj = \Carbon\Carbon::createFromDate($tahun, $bulan, 1);
        $daysInMonth = $dateObj->daysInMonth;
    
        // 2. Logika Penentuan Daftar Santri Berdasarkan target_tipe
        $querySantri = Santri::active()->where('pondok_id', $pondokId);
    
        if ($sesiAktif->target_tipe === 'plotting') {
            // Ambil santri dari tabel pivot absensi_sesi_santri
            $santris = $sesiAktif->santris() // Pastikan di model AbsensiSesi ada method santris() BelongsToMany
                ->with(['absensis' => function($q) use ($bulan, $tahun, $sesi_id) {
                    $q->whereMonth('tanggal', $bulan)
                      ->whereYear('tanggal', $tahun)
                      ->where('sesi_id', $sesi_id);
                }]);
        } else {
            // Jika global, kelas, atau kamar (mengacu ke kolom di tabel santri)
            if ($sesiAktif->target_tipe === 'kelas') {
                $querySantri->where('kelas_id', $sesiAktif->target_id);
            } elseif ($sesiAktif->target_tipe === 'kamar') {
                $querySantri->where('kamar_id', $sesiAktif->target_id);
            }
            // Jika global, tidak perlu tambahan where lagi
    
            $santris = $querySantri->with(['absensis' => function($q) use ($bulan, $tahun, $sesi_id) {
                $q->whereMonth('tanggal', $bulan)
                  ->whereYear('tanggal', $tahun)
                  ->where('sesi_id', $sesi_id);
            }]);
        }
    
        $dataSantri = $santris->orderBy('nama_lengkap', 'asc')->get();
    
        return view('tenant.absensi.index', [
            'sesiAktif'   => $sesiAktif,
            'allSesis'    => $allSesis,
            'santris'     => $dataSantri,
            'bulan'       => $bulan,
            'tahun'       => $tahun,
            'sesi_id'     => $sesi_id,
            'daysInMonth' => $daysInMonth,
            'dateObj'     => $dateObj,
            'sesis'       => $allSesis
        ]);
    }

    public function store(Request $request, CreateAbsensiAction $action)
    {
        // 1. Pastikan User Login
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }
    
        try {
            $dtos = [];
    
            // PENYESUAIAN DI SINI: Menangani AJAX (Status atau Keterangan)
            if ($request->mode === 'ajax' || $request->mode === 'ajax_keterangan') {
                
                // Jika mode adalah update keterangan, kita perlu ambil status saat ini di DB 
                // atau default ke 'hadir' jika belum ada, agar data tidak ter-reset.
                $status = $request->status;
                
                if ($request->mode === 'ajax_keterangan') {
                    // Cari status terakhir santri di tanggal tersebut agar tidak berubah jadi 'hadir' otomatis
                    $existing = \App\Models\Absensi::where([
                        'santri_id' => $request->santri_id,
                        'tanggal'   => $request->tanggal,
                        'sesi_id'   => $request->sesi_id
                    ])->first();
                    
                    $status = $existing ? $existing->status : 'hadir';
                }
    
                $dtos[] = new \App\Domains\Absensi\DTO\AbsensiDTO(
                    pondok_id: $user->pondok_id,
                    santri_id: (int) $request->santri_id,
                    sesi_id:   (int) $request->sesi_id,
                    tanggal:   $request->tanggal,
                    status:    $status,
                    input_by:  $user->id,
                    metode:    'manual',
                    keterangan: $request->keterangan // Menangkap input dari modal catatan
                );
            } else {
                // Loop massal (untuk form submit biasa)
                foreach ($request->data_absensi as $santriId => $tanggalGroup) {
                    foreach ($tanggalGroup as $tanggal => $data) {
                        $dtos[] = new \App\Domains\Absensi\DTO\AbsensiDTO(
                            pondok_id: $user->pondok_id,
                            santri_id: (int) $santriId,
                            sesi_id:   (int) $request->sesi_id,
                            tanggal:   $tanggal,
                            status:    $data['status'],
                            input_by:  $user->id,
                            metode:    'manual',
                            keterangan: $data['keterangan'] ?? null
                        );
                    }
                }
            }
    
            // Kirim ke Action
            $action->execute($dtos);
    
            if (str_contains($request->mode, 'ajax')) {
                return response()->json(['success' => true]);
            }
    
            return back()->with('success', 'Berhasil disimpan!');
    
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage(),
                'debug' => "Line: " . $e->getLine()
            ], 500);
        }
    }

    public function print(Request $request)
    {
        $pondokId = auth()->user()->pondok_id;
        $sesi_id = $request->get('sesi_id');
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
    
        if (!$sesi_id) return redirect()->back()->with('error', 'Sesi tidak valid.');
    
        $sesiAktif = AbsensiSesi::where('pondok_id', $pondokId)->findOrFail($sesi_id);
        $dateObj = \Carbon\Carbon::createFromDate($tahun, $bulan, 1);
        $daysInMonth = $dateObj->daysInMonth;
    
        // Pakai logika yang sama dengan INDEX kamu
        $querySantri = Santri::active()->where('pondok_id', $pondokId);
        if ($sesiAktif->target_tipe === 'plotting') {
            $santris = $sesiAktif->santris();
        } else {
            if ($sesiAktif->target_tipe === 'kelas') $querySantri->where('kelas_id', $sesiAktif->target_id);
            elseif ($sesiAktif->target_tipe === 'kamar') $querySantri->where('kamar_id', $sesiAktif->target_id);
            $santris = $querySantri;
        }
    
        $dataSantri = $santris->with(['absensis' => function($q) use ($bulan, $tahun, $sesi_id) {
            $q->whereMonth('tanggal', $bulan)
              ->whereYear('tanggal', $tahun)
              ->where('sesi_id', $sesi_id);
        }])->orderBy('nama_lengkap', 'asc')->get();
    
        return view('tenant.absensi.print', [
            'sesiAktif'   => $sesiAktif,
            'santris'     => $dataSantri,
            'bulan'       => $bulan,
            'tahun'       => $tahun,
            'daysInMonth' => $daysInMonth
        ]);
    }
}