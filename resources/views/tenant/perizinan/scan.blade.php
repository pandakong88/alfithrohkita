@extends('layouts.tenant')

@section('content')
<div class="page-inner" style="background: #f8fafc; min-height: 100vh; padding-top: 2rem;">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            
            {{-- BACK BUTTON --}}
            <div class="mb-4">
                <a href="{{ route('tenant.perizinan.index') }}" class="btn btn-link text-secondary fw-bold p-0 text-decoration-none">
                    <i class="fa fa-arrow-left me-2"></i> KEMBALI KE DAFTAR
                </a>
            </div>

            @php
                $isOverdue = $perizinan->status == 'aktif' && now()->gt($perizinan->batas_kembali);
                // Palet warna yang lebih soft/profesional
                $statusColor = $isOverdue ? '#e11d48' : ($perizinan->status == 'kembali' ? '#10b981' : '#6366f1');
                $gradient = $isOverdue ? 'linear-gradient(135deg, #e11d48 0%, #fb7185 100%)' : ($perizinan->status == 'kembali' ? 'linear-gradient(135deg, #059669 0%, #34d399 100%)' : 'linear-gradient(135deg, #4f46e5 0%, #818cf8 100%)');
            @endphp

            <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 24px;">
                
                {{-- HEADER: STATUS VISUAL --}}
                <div class="position-relative p-5 text-center text-white" style="background: {{ $gradient }};">
                    {{-- Ornamen Halus --}}
                    <div class="bg-ornament"></div>
                    
                    <div class="status-icon-container mb-4">
                        <div class="status-icon-main shadow-lg">
                            @if($isOverdue) <i class="fas fa-exclamation-triangle"></i>
                            @elseif($perizinan->status == 'kembali') <i class="fas fa-check-circle"></i>
                            @else <i class="fas fa-walking"></i>
                            @endif
                        </div>
                    </div>

                    <h5 class="text-uppercase fw-bold mb-1 opacity-75" style="letter-spacing: 3px; font-size: 0.85rem;">Status Perizinan</h5>
                    <h2 class="fw-black mb-3" style="font-weight: 900; letter-spacing: -0.5px;">
                        {{ $isOverdue ? 'KETERLAMBATAN' : ($perizinan->status == 'kembali' ? 'SUDAH KEMBALI' : 'SEDANG IZIN') }}
                    </h2>
                    
                    {{-- Ganti bagian badge ID sebelumnya dengan ini --}}
                    <div class="mt-2">
                        <span class="px-3 py-2 fw-bold text-white" 
                            style="background: rgba(0, 0, 0, 0.2); 
                                    border-radius: 12px; 
                                    font-size: 0.9rem; 
                                    letter-spacing: 1px; 
                                    border: 1px solid rgba(255, 255, 255, 0.3);
                                    backdrop-filter: blur(4px);">
                            <i class="fas fa-hashtag small mr-1 me-1 opacity-75"></i> 
                            {{ $perizinan->kode_surat }}
                        </span>
                    </div>
                </div>

                {{-- CONTENT --}}
                <div class="card-body p-4 bg-white">
                    {{-- PROFIL SANTRI --}}
                    <div class="text-center mb-5">
                        <p class="text-muted small text-uppercase fw-bold mb-1" style="letter-spacing: 1px;">Nama Santri</p>
                        <h3 class="fw-bold text-dark mb-2">{{ strtoupper($perizinan->santri->nama_lengkap) }}</h3>
                        <span class="badge bg-light text-secondary border px-3">NIS: {{ $perizinan->santri->nis }}</span>
                    </div>

                    {{-- TIMELINE BOXES --}}
                    <div class="row g-3 mb-5">
                        <div class="col-6">
                            <div class="time-card">
                                <span class="time-label">WAKTU KELUAR</span>
                                <span class="time-value text-dark">{{ $perizinan->tanggal_keluar->format('H:i') }}</span>
                                <span class="time-date">{{ $perizinan->tanggal_keluar->format('d M Y') }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="time-card border-{{ $isOverdue ? 'danger' : 'light' }}">
                                <span class="time-label">BATAS KEMBALI</span>
                                <span class="time-value {{ $isOverdue ? 'text-danger' : 'text-dark' }}">{{ $perizinan->batas_kembali->format('H:i') }}</span>
                                <span class="time-date">{{ $perizinan->batas_kembali->format('d M Y') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- OVERDUE INFO --}}
                    @if($isOverdue)
                        <div class="alert alert-danger border-0 rounded-4 p-3 d-flex align-items-center mb-5" style="background: #fff1f2;">
                            <div class="icon-box me-3 bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="fas fa-clock fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-danger">Terlambat {{ now()->diffForHumans($perizinan->batas_kembali, true) }}</h6>
                                <p class="mb-0 small text-danger opacity-75">Harap segera lakukan konfirmasi.</p>
                            </div>
                        </div>
                    @endif
 
                    {{-- ACTION --}}
                    @if($perizinan->status == 'pending')
                        @php
                            $template = $perizinan->template;
                            $steps = $template && isset($template->rules['approval_workflow']) ? $template->rules['approval_workflow'] : [];
                            $currentStepIndex = $perizinan->current_step;
                            $currentStep = null;
                            foreach ($steps as $st) {
                                if (isset($st['step']) && (int)$st['step'] === $currentStepIndex) {
                                    $currentStep = $st;
                                    break;
                                }
                            }
                            if (!$currentStep && !empty($steps[$currentStepIndex - 1])) {
                                $currentStep = $steps[$currentStepIndex - 1];
                            }
                            $requiredRole = $currentStep ? ($currentStep['required_role'] ?? null) : null;
                            $stepName = $currentStep ? ($currentStep['name'] ?? "Persetujuan Langkah {$currentStepIndex}") : '';
                            $userHasRole = $requiredRole ? (auth()->user()->hasRole($requiredRole) || auth()->user()->hasRole('admin_pondok') || auth()->user()->hasRole('super_admin')) : true;
                        @endphp

                        @if($currentStep)
                            <div class="text-center mb-4 p-3 bg-light rounded-4 border">
                                <p class="text-muted small text-uppercase fw-bold mb-1" style="letter-spacing: 1px; font-size: 10px;">Tahapan Persetujuan Aktif</p>
                                <h4 class="fw-bold text-info mb-1"><i class="fas fa-stream me-2"></i>{{ $stepName }}</h4>
                                <span class="badge bg-soft-info text-info border px-3 mt-1" style="font-size: 9px;">Otoritas: {{ strtoupper(str_replace('_', ' ', $requiredRole)) }}</span>
                            </div>

                            @if($userHasRole)
                                <form method="POST" action="{{ route('tenant.perizinan.approve', $perizinan->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-confirm-modern bg-info text-white shadow-info" style="box-shadow: 0 10px 20px rgba(21, 114, 232, 0.2);">
                                        <span>SETUJUI TAHAP INI</span>
                                        <i class="fas fa-check-circle ms-2"></i>
                                    </button>
                                </form>
                            @else
                                <div class="alert alert-warning border-0 rounded-4 p-3 d-flex align-items-center mb-0" style="background: #fffbeb;">
                                    <div class="icon-box me-3 bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; flex-shrink: 0;">
                                        <i class="fas fa-lock fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-warning" style="font-size: 12px;">Persetujuan Terkunci</h6>
                                        <p class="mb-0 small text-warning opacity-75" style="font-size: 11px;">Hanya peran {{ strtoupper(str_replace('_', ' ', $requiredRole)) }} yang dapat menyetujui langkah ini.</p>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="p-4 rounded-4 bg-light border border-dashed text-center">
                                <i class="fas fa-exclamation-circle text-warning mb-2 fa-lg"></i>
                                <p class="text-muted small fw-bold text-uppercase mb-1">Status tidak sinkron</p>
                                <h6 class="fw-bold text-dark mb-0">Langkah Persetujuan tidak ditemukan</h6>
                            </div>
                        @endif
                    @elseif($perizinan->status == 'aktif')
                        <form method="POST" action="{{ route('tenant.perizinan.kembali', $perizinan->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-confirm-modern">
                                <span>KONFIRMASI KEDATANGAN</span>
                                <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </form>
                    @else
                        <div class="p-4 rounded-4 bg-light border border-dashed text-center">
                            <i class="fas fa-check-double text-success mb-2 fa-lg"></i>
                            <p class="text-muted small fw-bold text-uppercase mb-1">Telah divalidasi pada:</p>
                            <h6 class="fw-bold text-dark mb-0">{{ $perizinan->updated_at->format('d M Y, H:i') }}</h6>
                        </div>
                    @endif
                </div>
 
                {{-- FOOTER INFO --}}
                <div class="card-footer bg-light border-0 p-4">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-comment-alt text-muted mt-1 me-3"></i>
                        <div>
                            <p class="mb-0 small text-muted fw-bold">KEPERLUAN:</p>
                            <p class="mb-0 text-dark" style="font-size: 0.95rem;">{{ $perizinan->keperluan ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FAIL-SAFE MANUAL SEARCH ON SCAN PAGE --}}
            <div class="card border-0 shadow-sm mt-4 p-4" style="border-radius: 24px;">
                <div class="form-group p-0 m-0">
                    <label class="small fw-bold text-muted mb-2"><i class="fas fa-keyboard me-1"></i> Scan / Ketik Kode Surat Lain</label>
                    <div class="input-group">
                        <input type="text" id="manualCodeSearch" class="form-control" placeholder="Contoh: IZN-20260609-ABCD" style="border-radius: 12px 0 0 12px;">
                        <button class="btn btn-dark" type="button" onclick="performManualSearch()" style="border-radius: 0 12px 12px 0;"><i class="fa fa-search"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function performManualSearch() {
        const val = document.getElementById('manualCodeSearch').value.trim();
        if (val) {
            window.location.href = `/dashboard/perizinan/scan/${val}`;
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('manualCodeSearch');
        if (input) {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performManualSearch();
                }
            });
        }
    });
</script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');

    body { font-family: 'Plus Jakarta Sans', sans-serif; }

    .bg-ornament {
        position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; 
        background: rgba(255,255,255,0.1); border-radius: 50%;
    }

    .status-icon-main {
        width: 80px; height: 80px; background: white; border-radius: 24px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 32px; color: {{ $statusColor }};
        transform: rotate(-10deg);
        transition: 0.3s;
    }

    .time-card {
        padding: 1.25rem; background: #fdfdfd; border: 1px solid #f1f5f9;
        border-radius: 20px; text-align: center;
    }

    .time-label { display: block; font-size: 0.65rem; font-weight: 800; color: #94a3b8; letter-spacing: 1px; margin-bottom: 5px; }
    .time-value { display: block; font-size: 1.4rem; font-weight: 800; line-height: 1; }
    .time-date { font-size: 0.75rem; color: #64748b; font-weight: 600; }

    .btn-confirm-modern {
        width: 100%; padding: 1.2rem; border-radius: 18px; border: none;
        background: #1e293b; color: white; font-weight: 700;
        letter-spacing: 0.5px; transition: all 0.3s ease;
        box-shadow: 0 10px 20px rgba(30, 41, 59, 0.2);
    }

    .btn-confirm-modern:hover {
        background: #0f172a; transform: translateY(-2px);
        box-shadow: 0 15px 25px rgba(30, 41, 59, 0.3);
    }

    .rounded-4 { border-radius: 1rem !important; }
    .fw-black { font-weight: 900; }
</style>
@endsection