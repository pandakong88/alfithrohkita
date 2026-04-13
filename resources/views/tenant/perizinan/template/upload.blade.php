@extends('layouts.tenant')

@section('content')
<div class="container-fluid py-4" style="background-color: #f3f4f6; min-height: 100vh;">
    {{-- ERP HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4 bg-white p-3 rounded-3 shadow-sm border-start border-primary border-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('tenant.template-perizinan.index') }}" class="text-decoration-none text-muted">Akademik</a></li>
                    <li class="breadcrumb-item active fw-bold text-primary">Konfigurasi Template PDF</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark">Modul Pencetakan Digital v1.0</h4>
        </div>
    </div>

    @if(!$template)
    {{-- STEP 1: UPLOAD ZONE (PROFESSIONAL) --}}
    <div class="row justify-content-center py-5">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-dark p-4 text-center">
                    <h5 class="text-white mb-0">Impor Dokumen Sumber</h5>
                </div>
                <div class="card-body p-5">
                    <form method="POST" action="{{ route('tenant.template-perizinan.upload.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="upload-drop-zone p-5 border-2 border-dashed rounded-4 text-center mb-4 transition-all" id="drop-zone">
                            <div class="icon-box bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px;">
                                <i class="fas fa-file-pdf fa-2x text-danger"></i>
                            </div>
                            <h6 class="fw-bold">Seret file PDF ke sini</h6>
                            <p class="text-muted small">Maksimal ukuran file: 2MB (Hanya PDF)</p>
                            <input type="file" name="file_pdf" class="form-control d-none" id="file_pdf_input" required onchange="this.form.submit()">
                            <button type="button" class="btn btn-outline-primary btn-sm px-4 rounded-pill" onclick="document.getElementById('file_pdf_input').click()">Pilih File</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($template)
    {{-- STEP 2: ERP SPLIT VIEW --}}
    <div class="row g-4">
        {{-- PANEL KIRI: DOKUMEN PREVIEW --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100 rounded-3">
                <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-eye me-2 text-primary"></i>Visualisasi Dokumen</h6>
                    <div class="d-flex gap-2">
                        <button class="btn btn-light btn-sm rounded-circle"><i class="fas fa-search-plus"></i></button>
                        <button class="btn btn-light btn-sm rounded-circle"><i class="fas fa-search-minus"></i></button>
                    </div>
                </div>
                <div class="card-body bg-secondary bg-opacity-10 p-4 overflow-auto d-flex justify-content-center align-items-start">
                    {{-- Simulasi Kertas A4 --}}
                    <div id="paper-simulation" class="shadow-lg transition-all" style="background: white; width: 100%; max-width: 600px; min-height: 800px; position: relative;">
                        <iframe id="pdf-frame" src="{{ asset('storage/'.$template->file_pdf) }}#toolbar=0" 
                                width="100%" height="800px" style="border:none;"></iframe>
                    </div>
                </div>
            </div>
        </div>

        {{-- PANEL KANAN: KONFIGURASI SISTEM --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3">
                <form method="POST" action="{{ route('tenant.template-perizinan.store') }}">
                    @csrf
                    <input type="hidden" name="template_id" value="{{ $template->id }}">

                    <div class="card-body p-4">
                        <h6 class="section-title mb-3">DATA IDENTITAS</h6>
                        <div class="mb-3">
                            <label class="small fw-bold text-muted mb-1">LABEL TEMPLATE</label>
                            <input type="text" name="nama" class="form-control bg-light border-0 py-2 shadow-none" placeholder="Misal: Surat Izin Keluar Malam" required>
                        </div>
                        <div class="mb-4">
                            <label class="small fw-bold text-muted mb-1">DESKRIPSI OPERASIONAL</label>
                            <textarea name="deskripsi" class="form-control bg-light border-0 shadow-none" rows="2" placeholder="Tujuan penggunaan..."></textarea>
                        </div>

                        <h6 class="section-title mb-3">STRUKTUR PENCETAKAN</h6>
                        <div class="row g-2 mb-4">
                            @php $layouts = [1 => 'Full A4', 2 => 'Split A5', 4 => 'Grid A6']; @endphp
                            @foreach($layouts as $val => $label)
                            <div class="col-4 text-center">
                                <input type="radio" class="btn-check layout-radio" name="layout_print" id="l{{$val}}" value="{{$val}}" {{ $val == 1 ? 'checked' : '' }}>
                                <label class="btn btn-outline-light text-dark border p-3 w-100 rounded-3 shadow-xs" for="l{{$val}}">
                                    <i class="fas {{ $val == 1 ? 'fa-file' : ($val == 2 ? 'fa-columns' : 'fa-th-large') }} d-block mb-1 text-primary"></i>
                                    <span style="font-size: 10px;">{{ $label }}</span>
                                </label>
                            </div>
                            @endforeach
                        </div>

                        <h6 class="section-title mb-3">MAPPING VARIABLE (DATA TAGS)</h6>
                        <div class="variable-container border rounded-3 p-2 bg-light mb-4" style="max-height: 250px; overflow-y: auto;">
                            @foreach($variables as $var)
                            <div class="var-card p-2 bg-white rounded-2 mb-2 border shadow-xs d-flex align-items-center">
                                <div class="form-check m-0">
                                    <input class="form-check-input" type="checkbox" name="variables[]" value="{{ $var->key }}" id="v-{{ $loop->index }}">
                                </div>
                                <label class="ms-2 d-flex flex-column" for="v-{{ $loop->index }}" style="cursor: pointer;">
                                    <span class="fw-bold text-primary" style="font-size: 11px;">{{ $var->label }}</span>
                                    <span class="text-muted" style="font-size: 9px;">{{ $var->key }}</span>
                                </label>
                            </div>
                            @endforeach
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-3 fw-bold rounded-3 shadow">
                                <i class="fas fa-check-double me-2"></i>SIMPAN KONFIGURASI
                            </button>
                            <a href="#" class="btn btn-light text-danger fw-bold py-2 border">Batal & Hapus Draft</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
    /* ERP STYLE OVERRIDES */
    .bg-soft-primary { background-color: #e0e7ff; }
    .section-title { font-size: 11px; font-weight: 800; color: #6b7280; letter-spacing: 0.5px; border-left: 3px solid #2563eb; padding-left: 10px; }
    .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    
    /* Layout Radio Button */
    .btn-check:checked + .btn-outline-light { border-color: #2563eb !important; background-color: #eff6ff !important; color: #2563eb !important; }
    
    /* Variable Selection */
    .var-card { transition: all 0.2s; cursor: pointer; }
    .var-card:hover { border-color: #2563eb; transform: translateX(3px); }
    
    /* Drop Zone Animation */
    .upload-drop-zone:hover { border-color: #2563eb; background-color: #f8fafc; cursor: pointer; }
    .transition-all { transition: all 0.3s ease; }

    /* Simulasi Preview PDF berdasarkan layout */
    .layout-split-2 { transform: scale(0.7); transform-origin: top; }
    .layout-split-4 { transform: scale(0.5); transform-origin: top; }

    /* Custom Scrollbar */
    .variable-container::-webkit-scrollbar { width: 4px; }
    .variable-container::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
</style>

<script>
    // JS untuk simulasi visual layout (OPSIONAL - Biar kerasa canggih)
    document.querySelectorAll('.layout-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            const paper = document.getElementById('paper-simulation');
            if(this.value == 2) {
                paper.style.maxWidth = "400px";
            } else if(this.value == 4) {
                paper.style.maxWidth = "300px";
            } else {
                paper.style.maxWidth = "600px";
            }
        });
    });
</script>
@endsection