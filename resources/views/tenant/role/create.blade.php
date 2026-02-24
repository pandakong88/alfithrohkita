@extends('layouts.tenant') {{-- Sesuaikan dengan layout Anda --}}

@section('title', 'Buat Role Baru')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Manajemen Akses</h3>
        <p class="text-muted small mb-0">Definisikan role baru dan hak akses yang diberikan.</p>
    </div>
    <a href="{{ route('tenant.role.index') }}" class="btn btn-outline-secondary btn-round">
        <i class="fas fa-arrow-left me-2"></i> Kembali
    </a>
</div>

<form method="POST" action="{{ route('tenant.role.store') }}">
    @csrf

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <div class="d-flex">
                <i class="fas fa-exclamation-circle mt-1 me-3"></i>
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Detail Role</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Nama Role <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" 
                               class="form-control @error('name') is-invalid @enderror" 
                               placeholder="Contoh: Bendahara, Pengasuh" required>
                        <small class="text-muted mt-2 d-block">Gunakan nama yang deskriptif untuk fungsi jabatan.</small>
                    </div>
                    
                    <hr class="my-4">
                    
                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                        <i class="fas fa-save me-2"></i> Simpan Role
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">Pilih Permissions</h5>
                    <div class="form-check small">
                        <input type="checkbox" class="form-check-input" id="checkAll">
                        <label class="form-check-label fw-bold text-primary" for="checkAll" style="cursor: pointer;">Pilih Semua</label>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($permissions as $permission)
                            <div class="col-md-6 col-xl-4 mb-3">
                                <div class="permission-card border rounded p-3 h-100 transition-all">
                                    <div class="form-check custom-checkbox">
                                        <input type="checkbox" 
                                               name="permissions[]" 
                                               value="{{ $permission->name }}" 
                                               class="form-check-input permission-checkbox" 
                                               id="perm-{{ $loop->index }}"
                                               {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label d-block ms-2" for="perm-{{ $loop->index }}" style="cursor: pointer;">
                                            <span class="fw-semibold text-dark d-block text-capitalize">{{ str_replace('_', ' ', $permission->name) }}</span>
                                            <span class="text-muted small">Hak akses untuk modul ini</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
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
    .form-control:focus { box-shadow: 0 0 0 4px rgba(67, 94, 190, 0.1); border-color: #435ebe; }

    /* Permission Card Effect */
    .permission-card {
        background-color: #fcfcfc;
        transition: all 0.2s ease-in-out;
    }
    .permission-card:hover {
        background-color: #f8f9ff;
        border-color: #435ebe !important;
        transform: translateY(-2px);
    }
    
    /* Highlight if checked (Optional via JS) */
    .permission-card.selected {
        background-color: #f0f3ff;
        border-color: #435ebe !important;
        box-shadow: 0 4px 8px rgba(67, 94, 190, 0.1);
    }

    .custom-checkbox .form-check-input {
        width: 1.2em;
        height: 1.2em;
        margin-top: 0.15em;
        cursor: pointer;
    }
</style>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll = document.getElementById('checkAll');
        const checkboxes = document.querySelectorAll('.permission-checkbox');

        // Logic Pilih Semua
        if (checkAll) {
            checkAll.addEventListener('change', function() {
                checkboxes.forEach(cb => {
                    cb.checked = this.checked;
                    updateCardStyle(cb);
                });
            });
        }

        // Logic Highlight Card saat diklik
        checkboxes.forEach(cb => {
            // Update style saat halaman load (jika old value ada)
            updateCardStyle(cb);

            cb.addEventListener('change', function() {
                updateCardStyle(this);
            });
        });

        function updateCardStyle(checkbox) {
            const card = checkbox.closest('.permission-card');
            if (checkbox.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
        }
    });
</script>
@endpush