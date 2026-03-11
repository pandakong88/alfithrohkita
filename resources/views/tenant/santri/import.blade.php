@extends('layouts.tenant')

@section('title', 'Import Santri')

@section('content')
<div class="container">
    <div class="page-inner py-5">
        
        <div class="max-w-4xl mx-auto">
            {{-- HEADER --}}
            <div class="text-center mb-5">
                <h1 class="fw-bold text-dark mb-2" style="letter-spacing: -0.02em; font-size: 2.25rem;">Import Master Data Santri</h1>
                <p class="text-muted text-lg">Unggah file Excel untuk memperbarui database santri secara massal.</p>
            </div>

            {{-- STEP 1: DOWNLOAD TEMPLATE --}}
            <div class="card card-round border-0 shadow-sm overflow-hidden mb-4 bg-primary-gradient-premium">
                <div class="card-body p-4 position-relative">
                    <div class="d-flex align-items-center justify-content-between position-relative" style="z-index: 2;">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-white-transparent me-4">
                                <i class="fas fa-file-excel text-white fa-lg"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold text-white mb-1">Gunakan Template Excel</h4>
                                <p class="text-white-50 mb-0">Download template agar format data sesuai dengan sistem kami.</p>
                            </div>
                        </div>
                        <a href="{{ route('tenant.santri.template.download') }}" class="btn btn-white btn-round px-4 py-2 fw-bold text-primary shadow-sm hover-push">
                            Download Template <i class="fas fa-arrow-down ms-2"></i>
                        </a>
                    </div>
                    <div class="deco-circle"></div>
                </div>
            </div>

            {{-- STEP 2: UPLOAD AREA --}}
            <div class="card card-round border-0 shadow-lg mb-4">
                <div class="card-body p-5">
                    <form method="POST" action="{{ route('tenant.santri.import.preview') }}" enctype="multipart/form-data" id="importForm">
                        @csrf
                        
                        <div class="upload-container">
                            <label for="fileInput" class="upload-box border-2 border-dashed rounded-xxl p-5 d-flex flex-column align-items-center justify-content-center transition-all cursor-pointer bg-light-soft hover-bg-white border-muted-strong hover-border-primary">
                                <input type="file" name="file" id="fileInput" class="d-none" required onchange="handleFileSelect(this)">
                                
                                <div id="upload-content-default" class="text-center">
                                    <div class="pulse-animation mb-4">
                                        <i class="fas fa-cloud-upload-alt text-primary fa-4x"></i>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-2">Seret berkas ke sini</h4>
                                    <p class="text-muted">atau <span class="text-primary fw-bold">Pilih berkas</span> dari komputer Anda</p>
                                    <div class="mt-3">
                                        <span class="badge badge-light-primary text-primary px-3 py-2 rounded-pill font-xs">Excel (.xlsx) / CSV</span>
                                    </div>
                                </div>

                                <div id="upload-content-selected" class="text-center d-none animate__animated animate__zoomIn">
                                    <div class="bg-success-light icon-circle-lg mx-auto mb-3">
                                        <i class="fas fa-check text-success fa-2x"></i>
                                    </div>
                                    <h5 id="file-name" class="fw-bold text-dark mb-1 text-truncate" style="max-width: 300px;">File_name.xlsx</h5>
                                    <p class="text-muted small mb-3" id="file-size">0 KB</p>
                                    <button type="button" class="btn btn-link text-danger fw-bold btn-sm" onclick="resetUpload(event)">Ganti Berkas</button>
                                </div>
                            </label>
                        </div>

                        @error('file')
                        <div class="alert alert-danger-light border-0 mt-4 rounded-xl d-flex align-items-center">
                            <i class="fas fa-times-circle me-3"></i> {{ $message }}
                        </div>
                        @enderror

                        <div class="mt-5 d-flex justify-content-end border-top pt-4">
                            <button type="submit" class="btn btn-primary btn-round btn-lg px-5 shadow-primary transform-active">
                                <span class="fw-bold">Preview Import</span> <i class="fas fa-chevron-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- STEP 3: FORMAT GUIDE (FULL COLUMNS) --}}
            <div class="card card-round border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark mb-0"><i class="fas fa-list text-info me-2"></i>Format Kolom Excel</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light-soft text-uppercase font-xs fw-bold text-muted">
                                <tr>
                                    <th class="ps-4 py-3">Kolom</th>
                                    <th class="py-3">Wajib</th>
                                    <th class="pe-4 py-3">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-4 py-3 font-monospace text-primary fw-bold">nis</td>
                                    <td><span class="badge badge-success-light text-success fw-bold px-3">YA</span></td>
                                    <td class="pe-4 text-dark small">Nomor induk santri</td>
                                </tr>
                                <tr>
                                    <td class="ps-4 py-3 font-monospace text-primary fw-bold">nama_lengkap</td>
                                    <td><span class="badge badge-success-light text-success fw-bold px-3">YA</span></td>
                                    <td class="pe-4 text-dark small">Nama lengkap santri</td>
                                </tr>
                                <tr>
                                    <td class="ps-4 py-3 font-monospace text-primary fw-bold">jenis_kelamin</td>
                                    <td><span class="badge badge-light-dark text-muted fw-bold px-3">OPSIONAL</span></td>
                                    <td class="pe-4 text-dark small italic">Isi dengan L atau P</td>
                                </tr>
                                <tr>
                                    <td class="ps-4 py-3 font-monospace text-primary fw-bold">alamat</td>
                                    <td><span class="badge badge-light-dark text-muted fw-bold px-3">OPSIONAL</span></td>
                                    <td class="pe-4 text-dark small">Alamat lengkap santri</td>
                                </tr>
                                <tr>
                                    <td class="ps-4 py-3 font-monospace text-primary fw-bold">wali_nama</td>
                                    <td><span class="badge badge-light-dark text-muted fw-bold px-3">OPSIONAL</span></td>
                                    <td class="pe-4 text-dark small">Nama orang tua/wali santri</td>
                                </tr>
                                <tr>
                                    <td class="ps-4 py-3 font-monospace text-primary fw-bold">wali_no_hp</td>
                                    <td><span class="badge badge-light-dark text-muted fw-bold px-3">OPSIONAL</span></td>
                                    <td class="pe-4 text-dark small">Nomor HP/WhatsApp wali</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    /* Premium UI Components */
    .bg-primary-gradient-premium { background: linear-gradient(135deg, #1269db 0%, #03a9f4 100%) !important; }
    .bg-light-soft { background-color: #f8fbff; }
    .bg-success-light { background-color: #ecfdf5; }
    .badge-success-light { background-color: #d1fae5; color: #065f46; }
    .badge-light-primary { background-color: #e0f2fe; }
    .rounded-xxl { border-radius: 2rem !important; }
    .upload-box { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border-color: #e5e7eb !important; }
    .upload-box:hover { background: #fff; border-color: #1269db !important; box-shadow: 0 10px 20px -5px rgba(0,0,0,0.05); }
    .hover-push:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important; }
    .shadow-primary { box-shadow: 0 8px 20px -6px rgba(18, 105, 219, 0.4) !important; }
    .icon-circle { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
    .bg-white-transparent { background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(4px); }
    .icon-circle-lg { width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; border-radius: 50%; }
    .font-xs { font-size: 0.7rem; }
    .max-w-4xl { max-width: 850px; margin: auto; }
    .deco-circle { position: absolute; width: 140px; height: 140px; background: rgba(255, 255, 255, 0.05); border-radius: 50%; top: -40px; right: -40px; z-index: 1; }
    @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } }
    .pulse-animation { animation: pulse 2s infinite ease-in-out; }
</style>

<script>
    function handleFileSelect(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            document.getElementById('file-name').innerText = file.name;
            document.getElementById('file-size').innerText = (file.size / 1024).toFixed(2) + ' KB';
            document.getElementById('upload-content-default').classList.add('d-none');
            document.getElementById('upload-content-selected').classList.remove('d-none');
        }
    }
    function resetUpload(e) {
        e.stopPropagation();
        e.preventDefault();
        document.getElementById('fileInput').value = '';
        document.getElementById('upload-content-default').classList.remove('d-none');
        document.getElementById('upload-content-selected').classList.add('d-none');
    }
</script>
@endsection