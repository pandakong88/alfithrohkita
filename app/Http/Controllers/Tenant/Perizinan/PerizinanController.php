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
        $perizinan = Perizinan::with(['santri', 'template', 'approvals.user'])
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
    | APPROVE STEP WORKFLOW
    |--------------------------------------------------------------------------
    */
    public function approve($id, \App\Domains\Perizinan\Actions\ApprovePerizinanStepAction $action)
    {
        try {
            $perizinan = Perizinan::with(['santri', 'template'])
                ->byPondok(auth()->user()->pondok_id)
                ->findOrFail($id);

            $template = $perizinan->template;
            $steps = $template && isset($template->rules['approval_workflow']) ? $template->rules['approval_workflow'] : [];
            
            $currentStepIndex = $perizinan->current_step;
            
            if ($currentStepIndex > count($steps)) {
                return back()->with('error', 'Semua langkah persetujuan untuk perizinan ini sudah selesai.');
            }

            // Dapatkan step data (index 1-based ke 0-based array)
            // Cari data step yang index-nya sesuai
            $currentStep = null;
            foreach ($steps as $st) {
                if (isset($st['step']) && (int)$st['step'] === $currentStepIndex) {
                    $currentStep = $st;
                    break;
                }
            }

            if (!$currentStep) {
                // Fallback jika array index berantakan
                $currentStep = $steps[$currentStepIndex - 1] ?? null;
            }

            if (!$currentStep) {
                return back()->with('error', 'Konfigurasi alur langkah tidak valid.');
            }

            $requiredRole = $currentStep['required_role'] ?? null;
            $stepName = $currentStep['name'] ?? "Persetujuan Langkah {$currentStepIndex}";

            $user = auth()->user();

            if ($requiredRole && !$user->hasRole($requiredRole) && !$user->hasRole('admin_pondok') && !$user->hasRole('super_admin')) {
                return back()->with('error', "Gagal: Langkah ini memerlukan wewenang peran " . strtoupper(str_replace('_', ' ', $requiredRole)) . ".");
            }

            $action->execute($perizinan, $user, $currentStepIndex, $stepName);

            return back()->with('success', "Persetujuan berhasil dicatat untuk: {$stepName}.");
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

    public function print($id)
    {
        $perizinan = Perizinan::with(['santri.kamar', 'template', 'pondok'])
            ->byPondok(auth()->user()->pondok_id)
            ->findOrFail($id);

        // Generate QR Code SVG string inline
        $qrCodeSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(120)->margin(1)->generate($perizinan->kode_surat);

        if ($perizinan->template->source_type === 'upload_pdf') {
            $placeholders = [
                'pondok.nama' => $perizinan->pondok->name ?? '',
                'pondok.alamat' => $perizinan->pondok->address ?? '',
                'santri.nama_lengkap' => $perizinan->santri->nama_lengkap,
                'santri.nis' => $perizinan->santri->nis,
                'santri.status' => $perizinan->santri->status,
                'kamar.nama' => $perizinan->santri->kamar->nama ?? '',
                'keperluan' => $perizinan->keperluan ?? '-',
                'tanggal_keluar' => $perizinan->tanggal_keluar->format('d/m/Y H:i'),
                'batas_kembali' => $perizinan->batas_kembali->format('d/m/Y H:i'),
                'tanggal_sekarang' => now()->format('d/m/Y'),
                'kode_surat' => $perizinan->kode_surat,
                'qr_code' => $qrCodeSvg,
            ];

            if (is_array($perizinan->variables)) {
                foreach ($perizinan->variables as $key => $val) {
                    if (str_starts_with($key, 'custom_variables.')) {
                        $placeholders[$key] = $val;
                    }
                }
            }

            return view('tenant.perizinan.print_pdf_overlay', compact('perizinan', 'placeholders'));
        }

        $content = $perizinan->template->format_surat;

        $placeholders = [
            '{nama_pondok}' => $perizinan->pondok->name ?? '',
            '{alamat_pondok}' => $perizinan->pondok->address ?? '',
            '{nama_santri}' => $perizinan->santri->nama_lengkap,
            '{nis}' => $perizinan->santri->nis,
            '{status_santri}' => $perizinan->santri->status,
            '{nama_kamar}' => $perizinan->santri->kamar->nama ?? '',
            '{keperluan}' => $perizinan->keperluan ?? '-',
            '{tgl_keluar}' => $perizinan->tanggal_keluar->format('d/m/Y H:i'),
            '{batas_kembali}' => $perizinan->batas_kembali->format('d/m/Y H:i'),
            '{tanggal_sekarang}' => now()->format('d/m/Y'),
            '{kode_surat}' => $perizinan->kode_surat,
            '{qr_code}' => $qrCodeSvg,
        ];

        foreach ($placeholders as $placeholder => $value) {
            $content = str_replace($placeholder, $value, $content);
        }

        if (is_array($perizinan->variables)) {
            foreach ($perizinan->variables as $key => $val) {
                if (str_starts_with($key, 'custom_variables.')) {
                    $originalPlaceholder = '{' . str_replace('custom_variables.', '', $key) . '}';
                    $content = str_replace($originalPlaceholder, $val, $content);
                }
            }
        }

        return view('tenant.perizinan.print_formal', compact('perizinan', 'content'));
    }
}