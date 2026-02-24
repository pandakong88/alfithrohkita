@extends('layouts.superadmin')

@section('title', 'Edit Role')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Edit Role: {{ $role->name }}</h3>
        <p class="text-muted small mb-0">Perbarui nama role atau sesuaikan ulang hak aksesnya.</p>
    </div>
    <a href="{{ route('tenant.role.index') }}" class="btn btn-outline-secondary btn-round">
        <i class="fas fa-arrow-left me-2"></i> Kembali
    </a>
</div>

<form method="POST" action="{{ route('tenant.role.update', $role) }}">
    @csrf
    @method('PUT')

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
                        <input type="text" name="name" value="{{ format_role_name($role->name) }}" 
                               class="form-control @error('name') is-invalid @enderror" 
                               placeholder="Contoh: Bendahara, Pengasuh" required>
                        <small class="text-muted mt-2 d-block text-warning">
                            <i class="fas fa-info-circle me-1"></i> Perubahan nama role mungkin berdampak pada cek akses di kode aplikasi.
                        </small>
                    </div>
                    
                    <hr class="my-4">
                    
                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                        <i class="fas fa-sync-alt me-2"></i> Update Role
                    </button>
                    <a href="{{ route('tenant.role.index') }}" class="btn btn-link btn-sm w-100 mt-2 text-muted">Batal</a>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">Sesuaikan Permissions</h5>
                    <div class="form-check small">
                        <input type="checkbox" class="form-check-input" id="checkAll">
                        <label class="form-check-label fw-bold text-primary" for="checkAll" style="cursor: pointer;">Pilih Semua</label>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($permissions as $permission)
                            <div class="col-md-6 col-xl-4 mb-3">
                                <div class="permission-card border rounded p-3 h-100 transition-all shadow-sm">
                                    <div class="form-check custom-checkbox">
                                        <input type="checkbox" 
                                               name="permissions[]" 
                                               value="{{ $permission->name }}" 
                                               class="form-check-input permission-checkbox" 
                                               id="perm-{{ $loop->index }}"
                                               {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                        <label class="form-check-label d-block ms-2" for="perm-{{ $loop->index }}" style="cursor: pointer;">
                                            <span class="fw-semibold text-dark d-block text-capitalize">{{ str_replace('_', ' ', $permission->name) }}</span>
                                            <small class="text-muted">Izin akses untuk modul terkait</small>
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
    .card { border-radius: 12px; }
    .btn-round { border-radius: 50px; }
    .form-control { border-radius: 8px; padding: 0.6rem 1rem; border: 1px solid #e0e0e0; }
    
    /* Permission Card Style */
    .permission-card { background-color: #ffffff; transition: all 0.2s ease-in-out; }
    .permission-card:hover { border-color: #435ebe !important; transform: translateY(-2px); }
    .permission-card.selected { background-color: #f0f3ff; border-color: #435ebe !important; }

    .custom-checkbox .form-check-input {
        width: 1.25em;
        height: 1.25em;
        cursor: pointer;
    }
</style>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll = document.getElementById('checkAll');
        const checkboxes = document.querySelectorAll('.permission-checkbox');

        function updateCardStyle(checkbox) {
            const card = checkbox.closest('.permission-card');
            if (checkbox.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
        }

        // Jalankan saat load untuk check yang sudah aktif
        checkboxes.forEach(cb => {
            updateCardStyle(cb);
            cb.addEventListener('change', function() {
                updateCardStyle(this);
            });
        });

        // Pilih Semua
        if (checkAll) {
            checkAll.addEventListener('change', function() {
                checkboxes.forEach(cb => {
                    cb.checked = this.checked;
                    updateCardStyle(cb);
                });
            });
        }
    });
</script>
@endpush