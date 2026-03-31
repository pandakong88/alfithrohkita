@extends('layouts.tenant')

@section('title', 'Gerbang Import Data Al-Fitroh')

@section('content')
<div class="container-fluid" style="background: #f0f4f1; min-height: 90vh;">
    <div class="page-inner py-5">
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                {{-- HEADER: NUANSA PESANTREN MODERN --}}
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
                    <div>
                        <h1 class="fw-extrabold text-success mb-1" style="letter-spacing: -1px; font-size: 2.2rem;">
                            <i class="fas fa-mosque me-2"></i> Khidmah <span class="text-dark">Data Import</span>
                        </h1>
                        <p class="text-muted fw-medium">
                            <i class="fas fa-sync-alt fa-spin me-2"></i> Integrasi Data Excel ke Sistem Manajemen Al-Fitroh
                        </p>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="stats-box bg-white p-3 rounded-4 shadow-sm border-start border-4 border-success d-flex align-items-center">
                            <div class="icon-shape bg-soft-success text-success me-3">
                                <i class="fas fa-file-signature"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Template Tersedia</small>
                                <span class="fw-bold text-dark">{{ $templates->count() }} Format Surat</span>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('tenant.import.preview') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row g-4">
                        {{-- KOLOM KIRI: SUMBER DATA --}}
                        <div class="col-md-7">
                            <div class="card card-round border-0 shadow-lg overflow-hidden h-100">
                                <div class="card-header bg-white border-0 pt-4 px-4">
                                    <h5 class="fw-bold mb-0 text-success"><i class="fas fa-copy me-2"></i> 1. Berkas Excel</h5>
                                </div>
                                <div class="card-body p-4">
                                    {{-- AREA DROPZONE --}}
                                    <div class="upload-zone border-2 border-dashed rounded-4 p-5 text-center position-relative transition-all" id="drop-zone">
                                        <input type="file" name="file" id="file-input" class="position-absolute w-100 h-100 top-0 start-0 opacity-0 cursor-pointer" accept=".xlsx, .xls" required>
                                        <div class="icon-upload mb-3">
                                            <i class="fas fa-file-excel text-success fa-5x"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark" id="file-name">Letakkan Berkas Excel di Sini</h6>
                                        <p class="text-muted small">Atau klik untuk memilih berkas dari komputer (Maks. 10MB)</p>
                                        <div id="file-info" class="mt-3 d-none">
                                            <span class="badge bg-success text-white px-3 py-2 rounded-pill shadow-sm">
                                                <i class="fas fa-check-double me-1"></i> <span id="selected-name">Berkas Terpilih</span>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <label class="form-label small fw-bold text-uppercase text-muted mb-2">Gunakan Struktur Mapping</label>
                                        <select name="template_id" class="form-control select2-pesantren" required>
                                            <option value=""></option>
                                            @foreach($templates as $template)
                                                <option value="{{ $template->id }}">
                                                    📋 {{ $template->nama_template }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- KOLOM KANAN: ATURAN IMPORT --}}
                        <div class="col-md-5">
                            <div class="card card-round border-0 shadow-lg h-100">
                                <div class="card-header bg-white border-0 pt-4 px-4">
                                    <h5 class="fw-bold mb-0 text-success"><i class="fas fa-cog me-2"></i> 2. Aturan & Validasi</h5>
                                </div>
                                <div class="card-body p-4">
                                    
                                    {{-- MODE JIKA TIDAK ADA --}}
                                    <div class="config-group mb-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="step-num bg-success me-3">A</div>
                                            <h6 class="fw-bold mb-0">Jika NIS Belum Terdaftar</h6>
                                        </div>
                                        <div class="ps-5">
                                            <div class="custom-radio-group">
                                                <input type="radio" name="mode_missing_nis" value="create" id="m1" checked>
                                                <label for="m1" class="radio-card shadow-none">
                                                    <i class="fas fa-user-plus text-success"></i>
                                                    <span>Daftarkan Baru</span>
                                                </label>

                                                <input type="radio" name="mode_missing_nis" value="skip" id="m2">
                                                <label for="m2" class="radio-card shadow-none">
                                                    <i class="fas fa-forward text-muted"></i>
                                                    <span>Lewati Saja</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- MODE JIKA SUDAH ADA --}}
                                    <div class="config-group">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="step-num bg-success me-3">B</div>
                                            <h6 class="fw-bold mb-0">Jika NIS Sudah Terpakai</h6>
                                        </div>
                                        <div class="ps-5">
                                            <select name="mode_existing_nis" class="form-select border-0 bg-light fw-bold rounded-3 py-3">
                                                <option value="update">📝 PERBARUI (Update Data)</option>
                                                <option value="skip">🛡️ PERTAHANKAN (Abaikan Excel)</option>
                                            </select>
                                        </div>
                                    </div>

                                </div>
                                <div class="card-footer bg-white border-0 p-4 pt-0 text-center">
                                    <button type="submit" class="btn btn-success w-100 py-3 rounded-4 fw-bold shadow-lg shadow-success-light transition-all btn-pesantren">
                                        MULAI ANALISA DATA <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- FOOTER/TIPS --}}
                <div class="mt-4 text-center">
                    <p class="text-muted small">
                        <i class="fas fa-shield-alt me-1 text-success"></i> 
                        Keamanan data santri adalah amanah kami. Sistem akan memvalidasi setiap baris sebelum disimpan.
                    </p>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    /* CUSTOM PESANTREN ERP STYLE */
    .bg-soft-success { background: rgba(40, 167, 69, 0.1); }
    .text-success { color: #28a745 !important; }
    .btn-success { background-color: #28a745 !important; border-color: #28a745 !important; }
    .shadow-success-light { box-shadow: 0 10px 20px rgba(40, 167, 69, 0.2) !important; }
    
    .upload-zone {
        background: #fdfdfd;
        border-color: #c3e6cb !important;
        transition: all 0.3s ease;
    }
    .upload-zone:hover {
        background: #ffffff;
        border-color: #28a745 !important;
        transform: translateY(-3px);
    }

    .step-num {
        width: 32px; height: 32px;
        color: white; border-radius: 50%; 
        display: flex; align-items: center; justify-content: center;
        font-weight: bold; font-size: 13px;
    }

    .radio-card {
        flex: 1; padding: 15px 10px;
        background: #f8f9fa; border: 2px solid transparent;
        border-radius: 12px; cursor: pointer;
        text-align: center; transition: all 0.2s;
    }
    .radio-card i { display: block; margin-bottom: 5px; font-size: 1.4rem; }
    .radio-card span { font-size: 11px; font-weight: bold; }
    
    input:checked + .radio-card {
        background: #fff;
        border-color: #28a745;
        color: #28a745;
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.1) !important;
    }

    /* Select2 Pesantren Style */
    .select2-pesantren + .select2-container .select2-selection {
        background: #f8f9fa !important;
        border: 1px solid #e0e0e0 !important;
        border-radius: 12px !important;
        height: 55px !important;
        display: flex; align-items: center;
    }

    .btn-pesantren:hover {
        transform: scale(1.02);
        box-shadow: 0 15px 25px rgba(40, 167, 69, 0.3) !important;
    }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2-pesantren').select2({
            theme: "bootstrap-5",
            placeholder: "🔍 Cari Format Mapping...",
            width: '100%'
        });

        $('#file-input').change(function(e) {
            if (e.target.files.length > 0) {
                var fileName = e.target.files[0].name;
                $('#file-name').html('<span class="text-success">Alhamdulillah, Berkas Siap!</span>');
                $('#selected-name').text(fileName);
                $('#file-info').removeClass('d-none');
                $('.upload-zone').css('border-style', 'solid');
            }
        });
    });
</script>
@endpush