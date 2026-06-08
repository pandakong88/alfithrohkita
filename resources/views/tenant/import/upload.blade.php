@php
    $templatesJson = $templates->map(function($t) {
        return [
            'id' => $t->id,
            'nama_template' => $t->nama_template,
            'fields' => $t->fields->map(function($f) {
                return [
                    'label' => $f->label,
                    'field_key' => $f->field_key,
                    'is_required' => (bool)$f->is_required,
                    'entity' => $f->entity,
                ];
            })
        ];
    })->keyBy('id')->toJson();
@endphp
@extends('layouts.tenant')


@section('title', 'Import Excel - Sistem Manajemen Data')

@section('content')
<div class="container" style="min-height: 90vh;">
    <div class="page-inner py-4">
        <div class="max-w-5xl mx-auto">
            
            {{-- BREADCRUMB --}}
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb breadcrumb-style-1 mb-0" style="background: transparent; padding: 0;">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.import-templates.index') }}">Template Survey</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Import Excel</li>
                </ol>
            </nav>

            {{-- HEADER SECTION --}}
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                <div class="d-flex align-items-center">
                    <div class="icon-avatar bg-primary-gradient text-white me-3 shadow-sm">
                        <i class="fas fa-database fa-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-dark fw-bold mb-0" style="font-size: 1.6rem;">Pusat Integrasi Data</h3>
                        <p class="text-muted mb-0 small">Unggah dan sinkronisasi data santri/wali institusi secara masal menggunakan template Excel.</p>
                    </div>
                </div>
            </div>

            {{-- ALERT MESSAGES --}}
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px; background-color: #fde8e8; color: #9b1c1c;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-3 fa-lg"></i>
                        <div>
                            <strong>Gagal memproses file:</strong> {{ session('error') }}
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px; background-color: #fef3c7; color: #92400e;">
                    <div class="d-flex">
                        <i class="fas fa-exclamation-circle me-3 fa-lg mt-1"></i>
                        <div>
                            <strong>Validasi Gagal:</strong>
                            <ul class="mb-0 ps-3 mt-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('tenant.import.preview') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-4">
                    {{-- KOLOM KIRI --}}
                    <div class="col-md-7">
                        <div class="card card-custom h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-file-excel me-2 text-primary"></i> Struktur & Sumber Data</h6>
                            </div>
                            <div class="card-body p-4">
                                {{-- Pilih Template --}}
                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-uppercase text-muted mb-1.5">Pilih Struktur Template Excel</label>
                                    <select name="template_id" id="template-select" class="form-control select2-standard" required>
                                        @foreach($templates as $template)
                                            <option value="{{ $template->id }}" {{ ($defaultTemplate && $defaultTemplate->id == $template->id) ? 'selected' : '' }}>
                                                {{ $template->nama_template }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Live Template Info & Download --}}
                                <div class="template-info-box mb-4 d-none" id="template-info-card">
                                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                        <div>
                                            <span class="text-uppercase text-muted d-block" style="font-size: 10px; font-weight: 700; letter-spacing: 0.5px;">STRUKTUR KOLOM</span>
                                            <h6 class="fw-bold mb-0 text-primary" id="info-template-name">Template</h6>
                                        </div>
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-outline-primary btn-round btn-xs dropdown-toggle shadow-none" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 11px; padding: 4px 10px;">
                                                <i class="fas fa-download me-1"></i> Unduh Template
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="font-size: 13px; min-width: 220px; margin-top: 5px;">
                                                <li>
                                                    <a class="dropdown-item py-2" id="download-blank-btn" href="#">
                                                        <i class="fas fa-file-excel text-success me-2" style="width: 16px;"></i> 
                                                        <strong>Template Kosong</strong>
                                                        <small class="text-muted d-block mt-0.5">Untuk entri data baru massal</small>
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                     <a class="dropdown-item py-2" id="download-data-btn" href="javascript:void(0)">
                                                         <i class="fas fa-database text-primary me-2" style="width: 16px;"></i> 
                                                         <strong>Template + Data Santri</strong>
                                                         <small class="text-muted d-block mt-0.5">Untuk pembaruan data lama massal</small>
                                                     </a>
                                                 </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-12" id="required-fields-section">
                                            <span class="d-block text-xs fw-semibold text-danger mb-1"><i class="fas fa-lock me-1"></i> Kolom Wajib (Harus ada):</span>
                                            <div id="required-fields-container" class="d-flex flex-wrap"></div>
                                        </div>
                                        <div class="col-12 mt-2.5" id="optional-fields-section">
                                            <span class="d-block text-xs fw-semibold text-muted mb-1"><i class="fas fa-plus me-1"></i> Kolom Opsional:</span>
                                            <div id="optional-fields-container" class="d-flex flex-wrap"></div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Drag & Drop Dropzone --}}
                                <div class="upload-zone-wrapper">
                                    <div class="upload-zone p-5 text-center position-relative" id="drop-zone">
                                        <input type="file" name="file" id="file-input" class="position-absolute w-100 h-100 top-0 start-0 opacity-0 cursor-pointer" accept=".xlsx, .xls, .csv" required style="z-index: 10;">
                                        <div class="icon-upload mb-3">
                                            <i class="fas fa-file-excel text-primary fa-4x opacity-50"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1" id="file-name">Tarik & Lepas File Excel di Sini</h6>
                                        <p class="text-muted small mb-0">Atau klik area ini untuk menjelajahi file (.xlsx, .xls, .csv) Max 10MB</p>
                                        
                                        <div id="file-info" class="mt-3 d-none">
                                            <span class="badge bg-primary px-3 py-2 rounded-pill shadow-sm" style="font-weight: 500;">
                                                <span id="selected-name">File</span>
                                            </span>
                                        </div>
                                    </div>
                                    <button type="button" id="clear-file-btn" class="btn btn-xs btn-danger btn-round shadow-sm position-absolute d-none" style="top: 15px; right: 15px; z-index: 20; padding: 4px 10px;">
                                        <i class="fas fa-times me-1"></i> Bersihkan File
                                    </button>
                                </div>

                                {{-- Validation Feedback Card --}}
                                <div id="validation-feedback-card" class="mt-3 d-none">
                                    <div class="card card-round border shadow-sm mb-0" id="feedback-card-inner" style="border-radius: 12px;">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-start gap-3" style="gap: 12px;">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center p-2" id="feedback-icon-container" style="width: 38px; height: 38px; min-width: 38px;">
                                                    <i class="fas fa-check-circle fa-lg" id="feedback-icon"></i>
                                                </div>
                                                <div class="flex-fill" style="min-width: 0;">
                                                    <h6 class="fw-bold text-dark mb-1" id="feedback-title" style="font-size: 13.5px;">Validasi Berhasil</h6>
                                                    <p class="text-muted mb-0 small" id="feedback-message" style="font-size: 11.5px; line-height: 1.4;">Struktur kolom sesuai dengan template.</p>
                                                    
                                                    <div id="feedback-errors-section" class="mt-2 d-none">
                                                        <span class="text-danger fw-semibold d-block text-xs mb-1"><i class="fas fa-exclamation-triangle me-1"></i> Detail Kesalahan:</span>
                                                        <ul class="text-danger mb-0 text-xs" id="feedback-errors-list" style="line-height: 1.5; padding-left: 1.2rem;">
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- KOLOM KANAN --}}
                    <div class="col-md-5">
                        <div class="card card-custom h-100">
                            <div class="card-header">
                                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-sliders-h me-2 text-primary"></i> Konfigurasi Import</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-uppercase text-muted mb-2">Penanganan Baris Data Baru</label>
                                    <div class="toggle-tile-group">
                                        <div class="flex-fill">
                                            <input type="radio" name="mode_missing_nis" value="create" id="m1" checked class="toggle-tile-input">
                                            <label for="m1" class="toggle-tile-label">
                                                <div class="tile-circle"></div>
                                                <div>
                                                    <span class="tile-title text-sm d-block text-dark">Buat Baru</span>
                                                    <span class="text-xs text-muted d-block mt-0.5">Daftarkan santri baru</span>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="flex-fill">
                                            <input type="radio" name="mode_missing_nis" value="skip" id="m2" class="toggle-tile-input">
                                            <label for="m2" class="toggle-tile-label">
                                                <div class="tile-circle"></div>
                                                <div>
                                                    <span class="tile-title text-sm d-block text-dark">Lewati</span>
                                                    <span class="text-xs text-muted d-block mt-0.5">Abaikan baris baru</span>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-uppercase text-muted mb-2">Penanganan Duplikasi (NIS Sama)</label>
                                    <select name="mode_existing_nis" class="form-select py-2.5 rounded-3" style="border-color: #cbd5e1; font-size: 14px;">
                                        <option value="update" selected>Perbarui Data Database (Update)</option>
                                        <option value="skip">Abaikan Baris Tersebut (Keep Old)</option>
                                    </select>
                                    <small class="text-muted d-block mt-2" style="font-size: 11px; line-height: 1.4;">
                                        Menentukan aksi sistem jika nomor NIS di Excel sudah terdaftar di sistem. Update akan menimpa field lama dengan field yang baru diunggah.
                                    </small>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0 p-4 pt-0">
                                <button type="submit" class="btn btn-gradient-primary w-100 py-3 fw-bold rounded-3 shadow-sm">
                                    PROSES VALIDASI BERKAS <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                                <div class="text-center mt-3">
                                    <a href="{{ route('tenant.import.history') }}" class="text-sm fw-semibold text-primary decoration-none">
                                        <i class="fas fa-history me-1.5"></i> Lihat Riwayat & Log Import
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* CSS Utility Variables */
    .page-inner {
        padding-top: 15px !important;
    }
    
    .max-w-5xl {
        max-width: 1024px;
        margin: 0 auto;
    }

    /* Icon Avatar Styles */
    .icon-avatar {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .bg-primary-gradient {
        background: linear-gradient(135deg, #1572e8 0%, #064095 100%) !important;
    }
    
    /* Card design */
    .card-custom {
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03), 0 1px 3px rgba(0, 0, 0, 0.02) !important;
        background: #ffffff;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .card-custom .card-header {
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        padding: 20px 24px;
    }
    
    /* Drag & Drop Upload Zone */
    .upload-zone-wrapper {
        position: relative;
    }
    
    .upload-zone {
        border: 2px dashed #cbd5e1;
        background: #f8fafc;
        border-radius: 16px;
        padding: 40px 24px;
        text-align: center;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
    }
    
    .upload-zone:hover, .upload-zone.dragover {
        border-color: #4f46e5;
        background: #f5f3ff;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }
    
    .upload-zone:hover .icon-upload i, .upload-zone.dragover .icon-upload i {
        color: #4f46e5 !important;
        transform: scale(1.05);
    }
    
    .icon-upload i {
        transition: all 0.25s ease;
        color: #94a3b8 !important;
    }
    
    /* Premium Toggle Radio Button Tiles */
    .toggle-tile-group {
        display: flex;
        gap: 12px;
    }
    
    .toggle-tile-input {
        display: none !important;
    }
    
    .toggle-tile-label {
        flex: 1;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 14px 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 0;
        user-select: none;
    }
    
    .toggle-tile-label:hover {
        background: #f8fafc;
        border-color: #94a3b8;
    }
    
    .toggle-tile-input:checked + .toggle-tile-label {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border-color: #3b82f6;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.05);
    }
    
    .toggle-tile-input:checked + .toggle-tile-label .tile-title {
        color: #1e3a8a !important;
        font-weight: 600;
    }
    
    .tile-circle {
        width: 18px;
        height: 18px;
        border: 2px solid #cbd5e1;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }
    
    .toggle-tile-input:checked + .toggle-tile-label .tile-circle {
        border-color: #3b82f6;
        background: #3b82f6;
    }
    
    .toggle-tile-input:checked + .toggle-tile-label .tile-circle::after {
        content: '';
        display: block;
        width: 6px;
        height: 6px;
        background: #ffffff;
        border-radius: 50%;
    }
    
    /* Soft badges for field schema details */
    .badge-required {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }
    
    .badge-wali {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
    }
    
    .badge-santri {
        background: #eff6ff;
        color: #2563eb;
        border: 1px solid #bfdbfe;
    }
    
    .badge-custom {
        background: #faf5ff;
        color: #7c3aed;
        border: 1px solid #e9d5ff;
    }
    
    .badge-kamar {
        background: #fffbeb;
        color: #d97706;
        border: 1px solid #fde68a;
    }
    
    .badge-orderly {
        display: inline-block;
        padding: 5px 10px;
        font-size: 11.5px;
        font-weight: 500;
        border-radius: 20px;
        margin: 3px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
    }
    
    .pulse-animation {
        animation: pulse-ring 2s infinite;
    }
    
    @keyframes pulse-ring {
        0% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.2); }
        70% { box-shadow: 0 0 0 6px rgba(79, 70, 229, 0); }
        100% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0); }
    }
    
    /* Custom button sizes and gradients */
    .btn-gradient-primary {
        background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%) !important;
        border: none !important;
        color: #ffffff !important;
        transition: all 0.3s ease;
    }
    
    .btn-gradient-primary:hover {
        background: linear-gradient(135deg, #4338ca 0%, #312e81 100%) !important;
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.25) !important;
        transform: translateY(-1px);
    }
    
    .btn-gradient-primary:active {
        transform: translateY(0);
    }
    
    /* Template details box styling */
    .template-info-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
    }
    
    .text-xs { font-size: 0.75rem; }
    .text-sm { font-size: 0.875rem; }
    .fs-8 { font-size: 0.72rem; }
    .me-1.5 { margin-right: 0.375rem; }
    .mt-2.5 { margin-top: 0.625rem; }

    .bg-soft-success { background-color: rgba(40, 167, 69, 0.08) !important; }
    .bg-soft-danger { background-color: rgba(220, 53, 69, 0.08) !important; }
    .border-soft-success { border-color: #a7f3d0 !important; }
    .border-soft-danger { border-color: #fecaca !important; }
    .bg-soft-success-light { background-color: #f6ffed !important; }
    .bg-soft-danger-light { background-color: #fff2f0 !important; }
</style>

{{-- MODAL DOWNLOAD FILTER --}}
<div class="modal fade" id="downloadFilterModal" tabindex="-1" aria-labelledby="downloadFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
            <div class="modal-header border-0 text-white py-3 px-4" style="border-top-left-radius: 15px; border-top-right-radius: 15px; background: linear-gradient(135deg, #1d7af3 0%, #1572e8 100%);">
                <h5 class="modal-title fw-bold" id="downloadFilterModalLabel">
                    <i class="fas fa-file-excel me-2"></i> Pengaturan Unduhan Data
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="downloadFilterForm" method="GET" action="">
                <input type="hidden" name="with_data" value="true">
                <div class="modal-body p-4" style="font-size: 13px;">
                    <p class="text-muted mb-3">Sesuaikan opsi filter dan nama file di bawah ini. Kosongkan filter jika ingin mengunduh seluruh data santri.</p>
                    
                    {{-- Nama Berkas --}}
                    <div class="form-group mb-3 px-0">
                        <label for="custom_filename" class="form-label fw-bold text-dark mb-1">Nama File Hasil Unduhan</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-success"><i class="fas fa-file-alt"></i></span>
                            <input type="text" class="form-control border-start-0" id="custom_filename" name="filename" placeholder="Contoh: Sensus_Santri_Komplek_A" style="font-size: 13px;">
                            <span class="input-group-text bg-light border-start-0 text-muted">.xlsx</span>
                        </div>
                        <small class="text-muted d-block mt-1">Nama file akan dibersihkan dari karakter ilegal.</small>
                    </div>

                    <div class="row">
                        {{-- Komplek --}}
                        <div class="col-md-6 mb-3">
                            <label for="filter_komplek" class="form-label fw-bold text-dark mb-1">Komplek</label>
                            <select class="form-select form-control" id="filter_komplek" name="komplek_id" style="font-size: 13px;">
                                <option value="">-- Semua Komplek --</option>
                                @foreach($kompleks as $komplek)
                                    <option value="{{ $komplek->id }}">{{ $komplek->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Kamar --}}
                        <div class="col-md-6 mb-3">
                            <label for="filter_kamar" class="form-label fw-bold text-dark mb-1">Kamar</label>
                            <select class="form-select form-control" id="filter_kamar" name="kamar_id" style="font-size: 13px;">
                                <option value="">-- Semua Kamar --</option>
                                @foreach($kamars as $kamar)
                                    <option value="{{ $kamar->id }}" data-komplek-id="{{ $kamar->komplek_id }}">{{ $kamar->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        {{-- Kelas --}}
                        <div class="col-md-6 mb-3">
                            <label for="filter_kelas" class="form-label fw-bold text-dark mb-1">Kelas</label>
                            <select class="form-select form-control" id="filter_kelas" name="kelas_id" style="font-size: 13px;">
                                <option value="">-- Semua Kelas --</option>
                                @foreach($kelas as $kls)
                                    <option value="{{ $kls->id }}">{{ $kls->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Jenis Kelamin --}}
                        <div class="col-md-6 mb-3">
                            <label for="filter_jk" class="form-label fw-bold text-dark mb-1">Jenis Kelamin</label>
                            <select class="form-select form-control" id="filter_jk" name="jenis_kelamin" style="font-size: 13px;">
                                <option value="">-- Semua Gender --</option>
                                <option value="L">Laki-laki (L)</option>
                                <option value="P">Perempuan (P)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="form-group mb-0 px-0">
                        <label for="filter_status" class="form-label fw-bold text-dark mb-1">Status Keaktifan</label>
                        <select class="form-select form-control" id="filter_status" name="status" style="font-size: 13px;">
                            <option value="">-- Semua Status --</option>
                            <option value="active">Active</option>
                            <option value="nonaktif">Nonaktif</option>
                            <option value="lulus">Lulus</option>
                            <option value="keluar">Keluar</option>
                            <option value="izin">Izin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light border btn-round btn-sm" data-bs-dismiss="modal" style="font-size: 12px; padding: 6px 16px;">Batal</button>
                    <button type="submit" class="btn btn-success btn-round btn-sm" style="font-size: 12px; padding: 6px 16px;">
                        <i class="fas fa-download me-1"></i> Download Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- SheetJS Library for Client-Side Excel Parsing -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
    $(document).ready(function() {
        // Eager serialized templates data
        const templates = {!! $templatesJson !!};
        const nisAutoGenerate = {{ auth()->user()->pondok->nis_auto_generate ? 'true' : 'false' }};
        var currentFile = null;

        // Initialize Select2 dropdown
        $('.select2-standard').select2({ 
            theme: "bootstrap-5", 
            width: '100%' 
        });

        // Template change listener
        $('#template-select').on('change', function() {
            updateTemplateDetails($(this).val());
            if (currentFile) {
                validateExcel(currentFile);
            }
        });

        // Initialize with default template details
        const initialVal = $('#template-select').val();
        if (initialVal) {
            updateTemplateDetails(initialVal);
        }

        function updateTemplateDetails(templateId) {
            const template = templates[templateId];
            if (!template) {
                $('#template-info-card').addClass('d-none');
                return;
            }

            $('#template-info-card').removeClass('d-none');
            $('#info-template-name').text(template.nama_template);

            // Construct download url
            const downloadBaseUrl = "{{ route('tenant.import-templates.download', ':id') }}";
            const downloadUrl = downloadBaseUrl.replace(':id', templateId);
            $('#download-blank-btn').attr('href', downloadUrl + '?with_data=false');

            // Simpan attribute data-template untuk memicu modal filter
            $('#download-data-btn')
                .attr('data-template-id', templateId)
                .attr('data-template-name', template.nama_template);

            // Generate field badges
            const requiredFields = [];
            const optionalFields = [];

            template.fields.forEach(field => {
                let badgeClass = 'badge-custom';
                if (field.entity === 'santri') badgeClass = 'badge-santri';
                else if (field.entity === 'wali') badgeClass = 'badge-wali';
                else if (['kelas', 'komplek', 'kamar', 'lemari', 'lemari_slot'].includes(field.entity)) badgeClass = 'badge-kamar';

                const fieldHtml = `<span class="badge ${badgeClass} badge-orderly">
                    ${field.label}
                </span>`;

                if (field.is_required) {
                    requiredFields.push(fieldHtml);
                } else {
                    optionalFields.push(fieldHtml);
                }
            });

            // Required section
            if (requiredFields.length > 0) {
                $('#required-fields-container').html(requiredFields.join(''));
                $('#required-fields-section').show();
            } else {
                $('#required-fields-container').html('');
                $('#required-fields-section').hide();
            }

            // Optional section
            if (optionalFields.length > 0) {
                $('#optional-fields-container').html(optionalFields.join(''));
                $('#optional-fields-section').show();
            } else {
                $('#optional-fields-container').html('<span class="text-muted small italic" style="font-size: 11px;">Tidak ada kolom opsional</span>');
                $('#optional-fields-section').show();
            }
        }

        // Drag & Drop Functionality
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('file-input');
        const fileNameDisplay = document.getElementById('file-name');
        const fileInfoDisplay = document.getElementById('file-info');
        const selectedNameDisplay = document.getElementById('selected-name');
        const clearFileBtn = document.getElementById('clear-file-btn');
        const uploadIcon = document.querySelector('.icon-upload i');

        // Drag events
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                handleFileSelect(e.dataTransfer.files[0]);
            }
        });

        // Input file change event
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });

        function handleFileSelect(file) {
            const name = file.name;
            const size = (file.size / 1024 / 1024).toFixed(2); // in MB
            const extension = name.split('.').pop().toLowerCase();
            
            if (!['xlsx', 'xls', 'csv'].includes(extension)) {
                alert('Format berkas tidak valid! Hanya mendukung file .xlsx, .xls, atau .csv');
                clearFile();
                return;
            }
            
            fileNameDisplay.innerHTML = `<span class="fw-bold text-dark">${name}</span>`;
            selectedNameDisplay.innerHTML = `<i class="fas fa-file-excel me-1.5"></i> ${name} (${size} MB)`;
            fileInfoDisplay.classList.remove('d-none');
            clearFileBtn.classList.remove('d-none');
            uploadIcon.className = "fas fa-file-excel text-success fa-4x pulse-animation";

            currentFile = file;
            validateExcel(file);
        }

        function clearFile() {
            fileInput.value = '';
            fileNameDisplay.innerHTML = 'Tarik & Lepas File Excel di Sini';
            fileInfoDisplay.classList.add('d-none');
            clearFileBtn.classList.add('d-none');
            uploadIcon.className = "fas fa-file-excel text-primary fa-4x opacity-50";
            
            currentFile = null;
            resetValidationUI();
        }

        // Click clear button
        if (clearFileBtn) {
            clearFileBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
                clearFile();
            });
        }

        // Form Submit spinner
        const form = document.querySelector('form');
        form.addEventListener('submit', (e) => {
            const btn = form.querySelector('button[type="submit"]');
            if (btn.disabled) {
                e.preventDefault();
                return false;
            }
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> MEMPROSES BERKAS...`;
        });

        // ==========================================
        // CLIENT-SIDE EXCEL VALIDATION LOGIC
        // ==========================================
        function validateExcel(file) {
            var templateId = $('#template-select').val();
            var template = templates[templateId];
            if (!template) return;

            var reader = new FileReader();
            reader.onload = function(e) {
                try {
                    var data = new Uint8Array(e.target.result);
                    var workbook = XLSX.read(data, {type: 'array', sheetRows: 1}); // Read only first row
                    if (workbook.SheetNames.length === 0) {
                        showValidationError(["Berkas Excel kosong atau tidak terbaca."]);
                        return;
                    }
                    
                    // Find target sheet (Data or first sheet that isn't Lookups)
                    var targetSheetName = workbook.SheetNames[0];
                    for (var i = 0; i < workbook.SheetNames.length; i++) {
                        var name = workbook.SheetNames[i];
                        if (name.toLowerCase().trim() === 'data') {
                            targetSheetName = name;
                            break;
                        }
                    }
                    if (targetSheetName.toLowerCase().trim() === 'lookups' || targetSheetName.toLowerCase().trim() === 'lookup') {
                        for (var i = 0; i < workbook.SheetNames.length; i++) {
                            var name = workbook.SheetNames[i];
                            if (name.toLowerCase().trim() !== 'lookups' && name.toLowerCase().trim() !== 'lookup') {
                                targetSheetName = name;
                                break;
                            }
                        }
                    }

                    var worksheet = workbook.Sheets[targetSheetName];
                    if (!worksheet || !worksheet['!ref']) {
                        showValidationError(["Lembar '" + targetSheetName + "' kosong."]);
                        return;
                    }

                    // Extract headers
                    var range = XLSX.utils.decode_range(worksheet['!ref']);
                    var headers = [];
                    var R = range.s.r; // First row
                    for (var C = range.s.c; C <= range.e.c; ++C) {
                        var cell = worksheet[XLSX.utils.encode_cell({c: C, r: R})];
                        var val = "";
                        if (cell && cell.t) {
                            val = XLSX.utils.format_cell(cell);
                        }
                        headers.push(val.toString().trim());
                    }

                    // Normalize functions
                    var normalize = function(str) {
                        return str.toString().toLowerCase().replace(/[\*\s_\-]/g, '');
                    };

                    // Check if 2-header style (db keys on row 1, labels on row 2)
                    var validKeys = template.fields.map(function(f) { return f.field_key.toLowerCase(); });
                    var matchingKeysCount = 0;
                    headers.forEach(function(h) {
                        if (validKeys.includes(h.toLowerCase())) {
                            matchingKeysCount++;
                        }
                    });

                    var matchThreshold = Math.min(2, validKeys.length);
                    var uploadedHeadersNormalized = [];
                    
                    if (matchingKeysCount >= matchThreshold) {
                        uploadedHeadersNormalized = headers.map(function(h) { return normalize(h); });
                    } else {
                        uploadedHeadersNormalized = headers.map(function(h) { return normalize(h); });
                    }

                    var errors = [];
                    var templateFieldsNormalized = template.fields.map(function(f) {
                        return {
                            labelNormalized: normalize(f.label),
                            fieldKeyNormalized: normalize(f.field_key),
                            is_required: f.is_required,
                            label: f.label,
                            field_key: f.field_key
                        };
                    });

                    // 1. Check required fields
                    templateFieldsNormalized.forEach(function(field) {
                        if (field.is_required) {
                            // Bypass NIS if nisAutoGenerate is true
                            if (field.field_key === 'nis' && nisAutoGenerate) {
                                return;
                            }
                            var isPresent = uploadedHeadersNormalized.some(function(h) {
                                return h === field.labelNormalized || h === field.fieldKeyNormalized;
                            });
                            if (!isPresent) {
                                errors.push("Kolom wajib '" + field.label + "' tidak ditemukan.");
                            }
                        }
                    });

                    // 2. Check Hierarchy/Dependencies
                    var hasFieldInExcel = function(fieldKey) {
                        var targetNorm = normalize(fieldKey);
                        return uploadedHeadersNormalized.some(function(h) {
                            var fMatch = templateFieldsNormalized.find(function(f) {
                                return f.field_key === fieldKey;
                            });
                            if (!fMatch) return false;
                            return h === fMatch.labelNormalized || h === fMatch.fieldKeyNormalized;
                        });
                    };

                    // - Kamar -> Komplek
                    if (hasFieldInExcel('kamar') && !hasFieldInExcel('komplek')) {
                        errors.push("Kolom 'Komplek' wajib ada jika kolom 'Kamar' disertakan.");
                    }
                    // - Lemari -> Kamar
                    if (hasFieldInExcel('lemari') && !hasFieldInExcel('kamar')) {
                        errors.push("Kolom 'Kamar' wajib ada jika kolom 'Lemari' disertakan.");
                    }
                    // - Slot Lemari -> Lemari
                    if (hasFieldInExcel('slot') && !hasFieldInExcel('lemari')) {
                        errors.push("Kolom 'Lemari' wajib ada jika kolom 'Slot Lemari' disertakan.");
                    }

                    if (errors.length > 0) {
                        showValidationError(errors);
                    } else {
                        showValidationSuccess();
                    }

                } catch (err) {
                    showValidationError(["Gagal membaca struktur Excel: " + err.message]);
                }
            };
            reader.readAsArrayBuffer(file);
        }

        function showValidationError(errors) {
            // Update drop-zone borders/bg
            $('#drop-zone').removeClass('border-soft-success bg-soft-success-light')
                           .addClass('border-soft-danger bg-soft-danger-light');
            
            // Show card
            $('#validation-feedback-card').removeClass('d-none');
            $('#feedback-card-inner').removeClass('border-soft-success bg-soft-success-light')
                                     .addClass('border-soft-danger bg-soft-danger-light');
            
            // Icon
            $('#feedback-icon-container').removeClass('bg-soft-success text-success')
                                         .addClass('bg-soft-danger text-danger');
            $('#feedback-icon').removeClass('fa-check-circle').addClass('fa-times-circle');
            
            // Texts
            $('#feedback-title').text('Struktur Kolom Tidak Valid').removeClass('text-success').addClass('text-danger');
            $('#feedback-message').text('Ditemukan beberapa ketidaksesuaian kolom dengan template yang Anda pilih.');
            
            // List errors
            var listHtml = '';
            errors.forEach(function(err) {
                listHtml += '<li>' + err + '</li>';
            });
            $('#feedback-errors-list').html(listHtml);
            $('#feedback-errors-section').removeClass('d-none');
            
            // Submit button
            var submitBtn = $('form button[type="submit"]');
            submitBtn.prop('disabled', true);
            submitBtn.attr('title', 'Perbaiki kesalahan pada file Excel sebelum melanjutkan.');
        }

        function showValidationSuccess() {
            // Update drop-zone borders/bg
            $('#drop-zone').removeClass('border-soft-danger bg-soft-danger-light')
                           .addClass('border-soft-success bg-soft-success-light');
            
            // Show card
            $('#validation-feedback-card').removeClass('d-none');
            $('#feedback-card-inner').removeClass('border-soft-danger bg-soft-danger-light')
                                     .addClass('border-soft-success bg-soft-success-light');
            
            // Icon
            $('#feedback-icon-container').removeClass('bg-soft-danger text-danger')
                                         .addClass('bg-soft-success text-success');
            $('#feedback-icon').removeClass('fa-times-circle').addClass('fa-check-circle');
            
            // Texts
            $('#feedback-title').text('Struktur Kolom Sesuai').removeClass('text-danger').addClass('text-success');
            var templateName = $('#template-select option:selected').text().trim();
            $('#feedback-message').text('Berkas siap! Struktur kolom sesuai dengan template "' + templateName + '".');
            
            // Clear errors
            $('#feedback-errors-section').addClass('d-none');
            $('#feedback-errors-list').html('');
            
            // Submit button
            var submitBtn = $('form button[type="submit"]');
            submitBtn.prop('disabled', false);
            submitBtn.removeAttr('title');
        }

        function resetValidationUI() {
            // Reset drop-zone
            $('#drop-zone').removeClass('border-soft-success bg-soft-success-light border-soft-danger bg-soft-danger-light');
            
            // Hide card
            $('#validation-feedback-card').addClass('d-none');
            $('#feedback-card-inner').removeClass('border-soft-success bg-soft-success-light border-soft-danger bg-soft-danger-light');
            
            // Clear list
            $('#feedback-errors-section').addClass('d-none');
            $('#feedback-errors-list').html('');
            
            // Submit button
            var submitBtn = $('form button[type="submit"]');
            submitBtn.prop('disabled', false);
            submitBtn.removeAttr('title');
        }

        // Tampilkan Modal Download dengan Filter
        $('#download-data-btn').on('click', function(e) {
            e.preventDefault();
            var templateId = $(this).attr('data-template-id');
            var templateName = $(this).attr('data-template-name');
            if (!templateId) return;
            
            // Set action URL pada form
            var urlPattern = "{{ route('tenant.import-templates.download', ':id') }}";
            $('#downloadFilterForm').attr('action', urlPattern.replace(':id', templateId));
            
            // Set default nama berkas
            var cleanTemplateName = templateName.replace(/\s+/g, '_');
            $('#custom_filename').val(cleanTemplateName + '_dengan_data');
            
            // Reset filters
            $('#filter_komplek').val('');
            $('#filter_kamar').val('');
            $('#filter_kelas').val('');
            $('#filter_jk').val('');
            $('#filter_status').val('');
            
            // Show all rooms initially
            $('#filter_kamar option').show();
            
            // Tampilkan modal
            $('#downloadFilterModal').modal('show');
        });

        // Filter Kamar secara dinamis berdasarkan Komplek yang dipilih
        $('#filter_komplek').on('change', function() {
            var komplekId = $(this).val();
            var $kamarSelect = $('#filter_kamar');
            
            // Reset pilihan kamar
            $kamarSelect.val('');
            
            if (komplekId === '') {
                // Tampilkan semua kamar jika komplek tidak dipilih
                $kamarSelect.find('option').show();
            } else {
                // Sembunyikan kamar yang tidak sesuai komplek, tampilkan yang sesuai
                $kamarSelect.find('option').each(function() {
                    var optionKomplekId = $(this).attr('data-komplek-id');
                    if (optionKomplekId === undefined || optionKomplekId === '' || optionKomplekId === komplekId) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
        });
    });
</script>
@endpush