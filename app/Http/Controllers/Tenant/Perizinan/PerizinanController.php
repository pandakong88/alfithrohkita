<?php

namespace App\Http\Controllers\Tenant\Perizinan;

use App\Domains\Perizinan\Actions\CreatePerizinanAction;
use App\Domains\Perizinan\Actions\ReturnPerizinanAction;
use App\Domains\Perizinan\DTO\CreatePerizinanData;
use App\Http\Controllers\Controller;
use App\Models\Perizinan;
use App\Models\Santri;
use App\Models\TemplatePerizinan;
use Illuminate\Http\Request;

class PerizinanController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIST
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $perizinans = Perizinan::with(['santri', 'template'])
            ->byPondok(auth()->user()->pondok_id)
            ->latest()
            ->get();

        return view('tenant.perizinan.index', compact('perizinans'));
    }

    /*
    |--------------------------------------------------------------------------
    | FORM CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $templates = TemplatePerizinan::byPondok(auth()->user()->pondok_id)
            ->where('is_active', true)
            ->get();

        $santris = Santri::byPondok(auth()->user()->pondok_id)
            ->where('status', 'active')
            ->get();

        return view('tenant.perizinan.create', compact('templates', 'santris'));
    }

    /**
     * API untuk Auto-fill data santri (Ajax)
     */
    public function getSantriData($id)
    {
        $santri = Santri::with(['wali', 'kelas'])
            ->byPondok(auth()->user()->pondok_id)
            ->findOrFail($id);
        
        return response()->json([
            'santri' => [
                'nama_lengkap' => $santri->nama_lengkap,
                'nis' => $santri->nis,
                'jenis_kelamin' => $santri->jenis_kelamin,
            ],
            'kelas' => [
                'nama' => $santri->kelas->nama ?? '-',
            ],
            'wali' => [
                'nama' => $santri->wali->nama ?? '',
                'hubungan' => $santri->wali->hubungan ?? '',
                'nomor_hp' => $santri->wali->nomor_hp ?? '',
            ]
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request, CreatePerizinanAction $action)
    {
        $request->validate([
            'santri_id' => 'required|exists:santris,id',
            'template_perizinan_id' => 'required|exists:template_perizinans,id',
            'tanggal_keluar' => 'required|date',
            'batas_kembali' => 'required|date|after_or_equal:tanggal_keluar',
            'keperluan' => 'nullable|string',
            'variables' => 'nullable|array', 
            'nomor_manual' => 'nullable|string|max:100',
        ]);
        // return $request->all();
        try {
            // Data ditransfer ke DTO
            $data = CreatePerizinanData::fromArray($request->all());

            // Eksekusi Action
            $action->execute($data);

            return redirect()
                ->route('tenant.perizinan.index')
                ->with('success', 'Perizinan berhasil dibuat');

        } catch (\Throwable $e) {
            // Log error jika perlu: \Log::error($e->getMessage());
            dd([
                'PESAN_ERROR' => $e->getMessage(),
                'FILE' => $e->getFile(),
                'BARIS' => $e->getLine(),
                'INPUT_DITERIMA' => $request->all()
            ]);
            return back()
                ->withInput()
                ->with('error', 'Gagal membuat izin: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW (DETAIL + QR)
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $perizinan = Perizinan::with(['santri', 'template'])
            ->byPondok(auth()->user()->pondok_id)
            ->findOrFail($id);

        return view('tenant.perizinan.show', compact('perizinan'));
    }

    /*
    |--------------------------------------------------------------------------
    | SCAN QR
    |--------------------------------------------------------------------------
    */
    public function scan($kode)
    {
        $perizinan = Perizinan::with('santri')
            ->where('kode_surat', $kode)
            ->where('pondok_id', auth()->user()->pondok_id)
            ->firstOrFail();

        return view('tenant.perizinan.scan', compact('perizinan'));
    }

    /*
    |--------------------------------------------------------------------------
    | KONFIRMASI KEMBALI
    |--------------------------------------------------------------------------
    */
    public function kembali($id, ReturnPerizinanAction $action)
    {
        try {
            $perizinan = Perizinan::findOrFail($id);
            $action->execute($perizinan);
    
            return back()->with('success', 'Status kepulangan santri berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RIWAYAT (CALENDAR/JSON)
    |--------------------------------------------------------------------------
    */
    public function dataRiwayat($santri_id)
    {
        $riwayats = Perizinan::where('santri_id', $santri_id)
                    ->orderBy('tanggal_keluar', 'desc')
                    ->get();
    
        $events = [];
        foreach ($riwayats as $row) {
            $isOverdue = ($row->status == 'aktif' && now()->gt($row->batas_kembali)) || 
                         ($row->status == 'kembali' && $row->updated_at > $row->batas_kembali);
            
            $label = $row->status == 'kembali' ? 'Kembali' : ($isOverdue ? 'Terlambat' : 'Aktif');
    
            if ($row->status == 'aktif') {
                $color = $isOverdue ? '#f25961' : '#1d7af3'; 
            } else {
                $color = $isOverdue ? '#6c757d' : '#31ce36'; 
            }
    
            $end = $row->batas_kembali;
            if ($end < $row->tanggal_keluar) {
                $end = $row->tanggal_keluar->copy()->addHour();
            }
    
            $events[] = [
                'title'           => ($row->keperluan ?? 'Izin'),
                'start'           => $row->tanggal_keluar->toIso8601String(),
                'end'             => $end->toIso8601String(),
                'allDay'          => false, 
                'backgroundColor' => $color,
                'borderColor'     => 'transparent',
                'extendedProps'   => [
                    'kode'         => $row->kode_surat,
                    'status_label' => $label,
                    'tgl_indo'     => $row->tanggal_keluar->format('d M Y'),
                    'jam'          => $row->tanggal_keluar->format('H:i') . ' - ' . $end->format('H:i'),
                ]
            ];
        }
        return response()->json($events);
    }
}