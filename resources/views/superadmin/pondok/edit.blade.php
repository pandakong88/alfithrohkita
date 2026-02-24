@extends('layouts.superadmin')

@section('title', 'Edit Pondok')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Pengaturan Pondok</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent p-0 small">
                <li class="breadcrumb-item"><a href="{{ route('superadmin.pondok.index') }}">Manajemen Pondok</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Pondok</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('superadmin.pondok.index') }}" class="btn btn-outline-secondary btn-round">
        <i class="fas fa-arrow-left me-2"></i> Kembali
    </a>
</div>

<form method="POST" action="{{ route('superadmin.pondok.update', $pondok) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Informasi Umum</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Nama Pondok <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $pondok->name) }}" class="form-control form-control-lg @error('name') is-invalid @enderror" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Alamat Lengkap</label>
                        <textarea name="address" rows="4" class="form-control">{{ old('address', $pondok->address) }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold text-dark">No. Telepon / WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fab fa-whatsapp"></i></span>
                                <input type="text" name="phone" value="{{ old('phone', $pondok->phone) }}" class="form-control border-start-0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold text-danger">Area Sensitif</h5>
                </div>
                <div class="card-body">
                    <label class="form-label fw-bold text-dark">Reset Password Admin</label>
                    <input type="password" name="admin_password" class="form-control" placeholder="Kosongkan jika tidak ingin mengganti">
                </div>
            </div>
        </div> <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Logo Pondok</h5>
                </div>
                <div class="card-body text-center">
                    <div class="preview-wrapper p-3 border border-2 border-dashed rounded-3 bg-light d-flex align-items-center justify-content-center" style="min-height: 200px; position: relative;">
                        <img id="main-preview" 
                             src="{{ $pondok->logo ? asset('storage/' . $pondok->logo) : asset('assets/img/no-image.png') }}" 
                             class="img-fluid rounded shadow-sm" 
                             style="max-height: 150px; object-fit: contain;">
                        
                        <div id="new-badge" class="position-absolute top-0 start-0 m-2" style="display: none;">
                            <span class="badge bg-primary shadow">Preview Baru</span>
                        </div>
                    </div>

                    <div class="mt-3 text-start">
                        <input type="file" name="logo" id="logo-input" class="d-none" accept="image/*">
                        <label for="logo-input" class="btn btn-sm btn-white shadow-sm border w-100 py-2">
                            <i class="fas fa-camera me-2"></i> Ganti Logo
                        </label>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">
                        <i class="fas fa-save me-2"></i> Update Data
                    </button>
                </div>
            </div>
        </div> </div>
</form>

<style>
    .card { border-radius: 12px; }
    .btn-round { border-radius: 50px; }
    .form-control { border-radius: 8px; border: 1px solid #e0e0e0; }
    .border-dashed { border-style: dashed !important; border-color: #d1d8e0 !important; }
</style>

@endsection

{{-- PENTING: Gunakan push jika di layouts pakai stack --}}
@push('scripts') 
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const logoInput = document.getElementById('logo-input');
        const mainPreview = document.getElementById('main-preview');
        const newBadge = document.getElementById('new-badge');

        if (logoInput) {
            logoInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        mainPreview.src = e.target.result;
                        newBadge.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>
@endpush