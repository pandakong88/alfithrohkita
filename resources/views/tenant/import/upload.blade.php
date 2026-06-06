@php
    $templatesJson = $templates->map(function($t) {
        return [
            'id' => $t->id,
            'nama_template' => $t->nama_template,
            'fields' => $t->fields->map(function($f) {
                return [
                    'label' => $f->label,
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
                                                    <a class="dropdown-item py-2" id="download-data-btn" href="#">
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
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Eager serialized templates data
        const templates = {!! $templatesJson !!};

        // Initialize Select2 dropdown
        $('.select2-standard').select2({ 
            theme: "bootstrap-5", 
            width: '100%' 
        });

        // Template change listener
        $('#template-select').on('change', function() {
            updateTemplateDetails($(this).val());
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
            $('#download-data-btn').attr('href', downloadUrl + '?with_data=true');

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
        }

        function clearFile() {
            fileInput.value = '';
            fileNameDisplay.innerHTML = 'Tarik & Lepas File Excel di Sini';
            fileInfoDisplay.classList.add('d-none');
            clearFileBtn.classList.add('d-none');
            uploadIcon.className = "fas fa-file-excel text-primary fa-4x opacity-50";
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
        form.addEventListener('submit', () => {
            const btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> MEMPROSES BERKAS...`;
        });
    });
</script>
@endpush