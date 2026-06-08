@extends('layouts.tenant')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark">Tambah User Baru</h3>
            <p class="text-muted small mb-0">Berikan akses masuk ke sistem untuk staf atau pengelola pondok.</p>
        </div>
        <a href="{{ route('tenant.user.index') }}" class="btn btn-outline-secondary btn-round shadow-sm">
            <i class="fas fa-arrow-left me-2"></i> Kembali
        </a>
    </div>

    <form method="POST" action="{{ route('tenant.user.store') }}">
        @csrf

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4">
                <div class="d-flex">
                    <i class="fas fa-exclamation-circle mt-1 me-3"></i>
                    <div>
                        <strong class="d-block">Terjadi kesalahan:</strong>
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
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm mb-4 card-custom">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="card-title mb-0 fw-bold text-dark">Informasi Profil</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark">Nama Lengkap <span class="text-danger">*</span></label>
                            <div class="input-group input-group-custom">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="name" value="{{ old('name') }}" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       placeholder="Masukkan nama lengkap user" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark">Alamat Email <span class="text-danger">*</span></label>
                            <div class="input-group input-group-custom">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email" value="{{ old('email') }}" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       placeholder="email@contoh.com" required>
                            </div>
                            <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle me-1"></i> Email akan digunakan untuk login ke aplikasi.</small>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm card-custom mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="card-title mb-0 fw-bold text-dark">Keamanan Akun</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Password <span class="text-danger">*</span></label>
                            <div class="input-group input-group-custom">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password" id="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       placeholder="Minimal 6 karakter" required>
                                <button class="btn btn-outline-secondary btn-eye-toggle border-start-0" type="button" id="togglePassword">
                                    <i class="fas fa-eye text-muted"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm mb-4 card-custom">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="card-title mb-0 fw-bold text-dark">Hak Akses (Role)</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark">Pilih Role User <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2">
                                <select name="role_id" class="form-select flex-grow-1 @error('role_id') is-invalid @enderror" required>
                                    <option value="" selected disabled>-- Pilih Role --</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                            {{ format_role_name($role->name) }}
                                        </option>
                                    @endforeach
                                </select>
                                <a href="{{ route('tenant.role.create') }}" class="btn btn-outline-primary d-flex align-items-center justify-content-center px-3" style="border-radius: 10px; border: 1px solid #E2E8F0; color: #4F46E5;" title="Tambah Role Baru" target="_blank">
                                    <i class="fas fa-plus"></i>
                                </a>
                            </div>
                            <div class="mt-3 p-3 bg-soft-info rounded-3">
                                <div class="d-flex">
                                    <i class="fas fa-shield-alt mt-1 me-2" style="font-size: 15px;"></i>
                                    <small class="fw-medium">Setiap role memiliki batasan akses yang berbeda sesuai pengaturan pada modul Manajemen Role.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm card-custom">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold mb-3 shadow-sm btn-save">
                            <i class="fas fa-save me-2"></i> Simpan User
                        </button>
                        <a href="{{ route('tenant.user.index') }}" class="btn btn-light w-100 btn-cancel">Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    /* Card Custom Styling */
    .card-custom {
        border-radius: 16px !important;
        background: #ffffff;
    }
    
    /* Buttons Custom */
    .btn-round {
        border-radius: 50px;
        padding: 0.6rem 1.4rem;
        font-weight: 600;
    }
    .btn-save {
        border-radius: 12px;
        font-size: 1rem;
        padding: 0.8rem 1rem;
        background: linear-gradient(135deg, #4F46E5 0%, #3B82F6 100%);
        border: none;
        transition: all 0.2s ease;
    }
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
    }
    .btn-cancel {
        border-radius: 12px;
        padding: 0.8rem 1rem;
        font-weight: 600;
        background-color: #F3F4F6;
        color: #4B5563;
        border: none;
        transition: all 0.2s ease;
    }
    .btn-cancel:hover {
        background-color: #E5E7EB;
        color: #374151;
    }

    /* Form Inputs */
    .form-control, .form-select {
        border-radius: 10px;
        padding: 0.75rem 1rem;
        border: 1px solid #E2E8F0;
        font-size: 0.95rem;
        color: #1E293B;
    }
    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        border-color: #4F46E5;
    }
    
    /* Input Groups */
    .input-group-custom .input-group-text {
        border-radius: 10px 0 0 10px;
        border: 1px solid #E2E8F0;
        border-right: none;
        background-color: #FAFCFF;
        color: #64748B;
        padding-left: 1.2rem;
        padding-right: 1.2rem;
    }
    .input-group-custom .form-control {
        border-radius: 0 10px 10px 0;
    }
    .btn-eye-toggle {
        border-radius: 0 10px 10px 0 !important;
        border: 1px solid #E2E8F0;
        border-left: none;
        background-color: #FAFCFF;
    }
    .btn-eye-toggle:hover {
        background-color: #F1F5F9;
    }
    
    /* Soft Info Alert */
    .bg-soft-info {
        background-color: rgba(59, 130, 246, 0.08) !important;
        color: #3B82F6 !important;
        border: 1px solid rgba(59, 130, 246, 0.12);
    }
</style>

@endsection

@push('scripts')
<script>
    // Fitur Show/Hide Password
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function (e) {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
</script>
@endpush