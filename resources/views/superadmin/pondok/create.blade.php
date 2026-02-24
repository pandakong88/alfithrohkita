@extends('layouts.superadmin')

@section('title', 'Buat Pondok Baru')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Tambah Pondok Baru</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent p-0 small">
                <li class="breadcrumb-item"><a href="{{ route('superadmin.pondok.index') }}">Manajemen Pondok</a></li>
                <li class="breadcrumb-item active" aria-current="page">Buat Baru</li>
            </ol>
        </nav>
    </div>

    <a href="{{ route('superadmin.pondok.index') }}" class="btn btn-outline-secondary btn-round">
        <i class="fas fa-arrow-left me-2"></i> Kembali
    </a>
</div>

<form method="POST" action="{{ route('superadmin.pondok.store') }}" enctype="multipart/form-data">
    @csrf

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <div class="d-flex">
                <i class="fas fa-exclamation-circle mt-1 me-3"></i>
                <div>
                    <strong class="d-block">Mohon periksa kembali:</strong>
                    <ul class="mb-0 mt-1 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Informasi Pondok</h5>
                    <p class="text-muted small mb-0">Lengkapi identitas utama pondok pesantren.</p>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Nama Pondok <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" 
                               class="form-control form-control-lg @error('name') is-invalid @enderror" 
                               placeholder="Contoh: Pondok Pesantren Al-Fitrah" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Alamat Lengkap</label>
                        <textarea name="address" rows="4" 
                                  class="form-control @error('address') is-invalid @enderror" 
                                  placeholder="Tuliskan alamat lengkap..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold text-dark">No. Telepon / WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fab fa-whatsapp"></i></span>
                                <input type="text" name="phone" value="{{ old('phone') }}" 
                                       class="form-control border-start-0 @error('phone') is-invalid @enderror" 
                                       placeholder="0812xxxx"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Akun Administrator Pondok</h5>
                    <p class="text-muted small mb-0">Akun ini akan digunakan untuk mengelola data di level pondok.</p>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Nama Lengkap Admin <span class="text-danger">*</span></label>
                        <input type="text" name="admin_name" value="{{ old('admin_name') }}" 
                               class="form-control @error('admin_name') is-invalid @enderror" 
                               placeholder="Nama pengelola" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold text-dark">Email Admin <span class="text-danger">*</span></label>
                            <input type="email" name="admin_email" value="{{ old('admin_email') }}" 
                                   class="form-control @error('admin_email') is-invalid @enderror" 
                                   placeholder="email@pondok.com" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold text-dark">Password <span class="text-danger">*</span></label>
                            <input type="password" name="admin_password" 
                                   class="form-control @error('admin_password') is-invalid @enderror" 
                                   placeholder="Minimal 8 karakter" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Logo Pondok</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="preview-wrapper p-3 border border-2 border-dashed rounded-3 bg-light d-flex align-items-center justify-content-center" style="min-height: 200px; position: relative;">
                            
                            <img id="main-preview" 
                                 src="{{ asset('assets/img/no-image.png') }}" 
                                 class="img-fluid rounded shadow-sm" 
                                 style="max-height: 150px; object-fit: contain;">

                            <div id="new-badge" class="position-absolute top-0 start-0 m-2" style="display: none;">
                                <span class="badge bg-primary shadow">Gambar Dipilih</span>
                            </div>
                        </div>

                        <div class="mt-3 text-start">
                            <input type="file" name="logo" id="logo-input" class="d-none" accept="image/*">
                            <label for="logo-input" class="btn btn-sm btn-white shadow-sm border w-100 py-2">
                                <i class="fas fa-cloud-upload-alt me-2"></i> Pilih Logo Pondok
                            </label>
                            <small class="text-muted d-block mt-2 text-center">Format: JPG, PNG. Maks 2MB.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4 bg-success text-white" id="status-card" style="transition: all 0.3s ease;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-1 opacity-75">Status Pondok</h6>
                            <h5 class="fw-bold mb-0" id="status-label">Aktif</h5>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input custom-switch" type="checkbox" name="is_active" value="1" 
                                   id="is_active" checked style="transform: scale(1.5); cursor: pointer;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold mb-3">
                        <i class="fas fa-check-circle me-2"></i> Simpan Pondok
                    </button>
                    <a href="{{ route('superadmin.pondok.index') }}" class="btn btn-light w-100">Batal</a>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
    /* SaaS Style Customization */
    .card { border-radius: 12px; }
    .btn-round { border-radius: 50px; }
    .form-control { border-radius: 8px; padding: 0.6rem 1rem; border: 1px solid #e0e0e0; }
    
    /* MODIFIKASI SWITCH AGAR PUTIH TERANG & KONTRAS */
    .form-check-input.custom-switch {
        cursor: pointer;
        background-color: rgba(255, 255, 255, 0.3); /* Warna saat off (agak transparan) */
        border-color: rgba(255, 255, 255, 0.5);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='white'/%3e%3c/svg%3e"); /* Lingkaran dalam warna putih */
    }

    .form-check-input.custom-switch:checked {
        background-color: #fff !important; /* Latar belakang switch jadi putih saat aktif */
        border-color: #fff !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23198754'/%3e%3c/svg%3e"); /* Lingkaran dalam jadi hijau saat aktif */
    }

    /* Efek Glow pada Card Status */
    #status-card.bg-success {
        box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3);
    }
</style>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Logic Preview Logo
        const logoInput = document.getElementById('logo-input');
        const mainPreview = document.getElementById('main-preview');
        const newBadge = document.getElementById('new-badge');

        if (logoInput) {
            logoInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    // Validasi ukuran 2MB
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Ukuran file maksimal 2MB');
                        this.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        mainPreview.src = e.target.result;
                        newBadge.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // 2. Logic Toggle Status Card
        const statusSwitch = document.getElementById('is_active');
        const statusCard = document.getElementById('status-card');
        const statusLabel = document.getElementById('status-label');

        if (statusSwitch) {
            statusSwitch.addEventListener('change', function() {
                if(this.checked) {
                    statusCard.classList.replace('bg-dark', 'bg-success');
                    statusLabel.innerText = 'Aktif';
                } else {
                    statusCard.classList.replace('bg-success', 'bg-dark');
                    statusLabel.innerText = 'Nonaktif';
                }
            });
        }
    });
</script>
@endpush