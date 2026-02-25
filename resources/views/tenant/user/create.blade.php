@extends('layouts.tenant')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Tambah User Baru</h3>
        <p class="text-muted small mb-0">Berikan akses masuk ke sistem untuk staf atau pengelola.</p>
    </div>
    <a href="{{ route('tenant.user.index') }}" class="btn btn-outline-secondary btn-round">
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
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Informasi Profil</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Nama Lengkap <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                            <input type="text" name="name" value="{{ old('name') }}" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   placeholder="Masukkan nama lengkap user" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Alamat Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" value="{{ old('email') }}" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   placeholder="email@contoh.com" required>
                        </div>
                        <small class="text-muted mt-2 d-block">Email akan digunakan untuk login ke aplikasi.</small>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Keamanan Akun</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" id="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   placeholder="Minimal 8 karakter" required>
                            <button class="btn btn-outline-secondary border-start-0" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Hak Akses (Role)</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Pilih Role User <span class="text-danger">*</span></label>
                        <select name="role_id" class="form-select form-control-lg @error('role_id') is-invalid @enderror" required>
                            <option value="" selected disabled>-- Pilih Role --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ format_role_name($role->name) }}
                                </option>
                            @endforeach
                        </select>
                        <div class="mt-3 p-3 bg-soft-info rounded-3">
                            <div class="d-flex">
                                <i class="fas fa-shield-alt text-info mt-1 me-2"></i>
                                <small class="text-info">Setiap role memiliki batasan akses yang berbeda sesuai pengaturan pada modul Manajemen Role.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold mb-3">
                        <i class="fas fa-save me-2"></i> Simpan User
                    </button>
                    <a href="{{ route('tenant.user.index') }}" class="btn btn-light w-100">Batal</a>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
    .card { border-radius: 12px; }
    .btn-round { border-radius: 50px; }
    .form-control, .form-select { border-radius: 8px; padding: 0.6rem 1rem; border: 1px solid #e0e0e0; }
    .form-control:focus, .form-select:focus { box-shadow: 0 0 0 4px rgba(67, 94, 190, 0.1); border-color: #435ebe; }
    .input-group-text { border-radius: 8px 0 0 8px; border: 1px solid #e0e0e0; border-right: none; }
    .bg-soft-info { background-color: #e0f7fa; }
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