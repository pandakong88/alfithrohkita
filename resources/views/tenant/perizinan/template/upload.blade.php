@extends('layouts.tenant')

@section('content')
<div class="page-inner">

    {{-- HEADER --}}
    <div class="page-header">
        <h4 class="page-title">Konfigurasi Template</h4>
        <ul class="breadcrumbs">
            <li class="nav-home"><a href="#"><i class="flaticon-home"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a>Template Perizinan</a></li>
        </ul>
    </div>

    @if(!$file)
    {{-- ========================= --}}
    {{-- STEP 1: UPLOAD ZONE --}}
    {{-- ========================= --}}
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card card-round shadow-none border">
                <div class="card-body p-5 text-center">
                    <form method="POST" action="{{ route('tenant.template-perizinan.upload.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="upload-drop-zone p-5 border-2 border-dashed rounded-3 mb-0"
                             onclick="document.getElementById('file_pdf_input').click()"
                             style="cursor: pointer; border-color: #ebedf2;">
                            
                            <div class="icon-shape bg-light-primary text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="fas fa-cloud-upload-alt fa-2x"></i>
                            </div>
                            <h4 class="fw-bold">Unggah Dokumen PDF</h4>
                            <p class="text-muted small">Klik untuk menelusuri file (Maks. 2MB)</p>

                            <input type="file" name="file_pdf" class="d-none"
                                   id="file_pdf_input" required
                                   onchange="this.form.submit()">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @else
    {{-- ========================= --}}
    {{-- STEP 2: KONFIGURASI --}}
    {{-- ========================= --}}
    <div class="row">

        {{-- PREVIEW PDF --}}
        <div class="col-lg-7">
            <div class="card card-round">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title"><i class="fas fa-file-pdf text-danger me-2"></i>Pratinjau Dokumen</div>
                    <span class="badge badge-success">File Terunggah</span>
                </div>
                <div class="card-body bg-light p-0">
                    <div class="pdf-container" style="background: #525659; padding: 20px;">
                        <iframe src="{{ asset('storage/'.$file) }}#toolbar=0"
                                width="100%" height="650px" class="shadow-lg border-0"></iframe>
                    </div>
                </div>
            </div>
        </div>

        {{-- FORM KONFIGURASI --}}
        <div class="col-lg-5">
            <div class="card card-round">
                <form method="POST" action="{{ route('tenant.template-perizinan.store') }}">
                    @csrf
                    <input type="hidden" name="file_pdf" value="{{ $file }}">

                    <div class="card-header">
                        <div class="card-title">Pengaturan Template</div>
                    </div>

                    <div class="card-body">
                        {{-- NAMA TEMPLATE --}}
                        <div class="form-group p-0 mb-3">
                            <label class="fw-bold">Nama Template</label>
                            <input type="text" name="nama" class="form-control" placeholder="Contoh: Surat Izin Keluar" required>
                        </div>
                    
                        {{-- DESKRIPSI (Tambahkan kembali di sini) --}}
                        <div class="form-group p-0 mb-4">
                            <label class="fw-bold">Deskripsi Operasional</label>
                            <textarea name="deskripsi" class="form-control" rows="2" placeholder="Tulis catatan singkat penggunaan template ini..."></textarea>
                        </div>
                    
                        {{-- STATUS DEFAULT --}}
                        <div class="mb-4">
                            <div class="custom-control custom-checkbox border rounded p-3 bg-light shadow-none">
                                <input type="checkbox" class="custom-control-input" id="is_default" name="is_default" value="1">
                                <label class="custom-control-label fw-bold mb-0" for="is_default" style="cursor:pointer; color: #1572e8;">
                                    <i class="fas fa-star me-1"></i> Jadikan Template Utama (Default)
                                </label>
                                <small class="d-block text-muted mt-1" style="font-size: 10px; margin-left: 1.5rem;">
                                    Jika dicentang, template ini akan otomatis terpilih saat mencetak surat baru.
                                </small>
                            </div>
                        </div>
                    
                        {{-- LAYOUT SELECTION --}}
                        <div class="form-group p-0 mb-4">
                            <label class="fw-bold mb-2">Pilih Layout Cetak</label>
                            <div class="row g-2">
                                @foreach([1 => 'A4 (Full)', 2 => 'A5 (Setengah)', 4 => 'A6 (Kecil)'] as $val => $text)
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="layout_print" id="layout{{$val}}" value="{{$val}}" {{ $val == 1 ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center" for="layout{{$val}}">
                                        <i class="fas {{ $val == 1 ? 'fa-file' : ($val == 2 ? 'fa-columns' : 'fa-th-large') }} mb-1"></i>
                                        <span style="font-size: 10px">{{ $text }}</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    
                        {{-- VARIABLES GRID --}}
                        <div class="form-group p-0">
                            <label class="fw-bold mb-2">Mapping Variabel (Data Tag)</label>
                            <div class="variable-grid bg-light p-3 rounded-3 mb-2" style="max-height: 200px; overflow-y: auto;">
                                <div class="row g-2">
                                    @foreach($variables as $var)
                                    <div class="col-6">
                                        <div class="form-check p-0 m-0">
                                            <div class="custom-control custom-checkbox bg-white border rounded p-2 h-100 shadow-none">
                                                <input type="checkbox" class="custom-control-input" name="variables[]" value="{{ $var->key }}" id="v-{{ $loop->index }}">
                                                <label class="custom-control-label ms-2" for="v-{{ $loop->index }}" style="cursor: pointer;">
                                                    <span class="d-block fw-bold text-primary" style="font-size: 11px;">{{ $var->label }}</span>
                                                    <small class="text-muted" style="font-size: 9px;">{{ $var->key }}</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-transparent border-0 px-4 pb-4">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-round fw-bold shadow-sm">
                                <i class="fas fa-check-circle me-2"></i>Simpan Template
                            </button>
                            <a href="{{ route('tenant.template-perizinan.upload') }}" class="btn btn-link text-danger fw-bold mt-1">
                                <i class="fas fa-times me-1"></i> Batalkan & Hapus File
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
    @endif

</div>

<style>
    /* Custom Styling */
    .upload-drop-zone:hover {
        border-color: #1572e8 !important;
        background-color: #f8f9fa;
    }
    .bg-light-primary { background-color: #e8f1fd; }
    .btn-check:checked + label {
        background-color: #1572e8 !important;
        color: white !important;
        border-color: #1572e8 !important;
    }
    .custom-control-label::before, .custom-control-label::after {
        top: 0.5rem;
        left: -1.5rem;
    }
    /* Scrollbar */
    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-thumb { background: #dcdde1; border-radius: 10px; }
</style>
@endsection