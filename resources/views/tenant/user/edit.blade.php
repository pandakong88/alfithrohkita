@extends('layouts.tenant')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Edit Pengguna</h3>
        <p class="text-muted small mb-0">Perbarui profil, akses role, atau atur ulang kata sandi user.</p>
    </div>
    <a href="{{ route('tenant.user.index') }}" class="btn btn-outline-secondary btn-round">
        <i class="fas fa-arrow-left me-2"></i> Kembali
    </a>
</div>

<form method="POST" action="{{ route('tenant.user.update', $user) }}">
    @csrf
    @method('PUT')

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <div class="d-flex">
                <i class="fas fa-exclamation-circle mt-1 me-3"></i>
                <div>
                    <strong class="d-block">Terjadi kesalahan input:</strong>
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
                    <h5 class="card-title mb-0 fw-bold">Data Profil</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Nama Lengkap</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   placeholder="Nama lengkap" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   placeholder="email@contoh.com" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm border-top border-warning border-3">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Keamanan (Ganti Password)</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label fw-bold text-dark">Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-key text-muted"></i></span>
                            <input type="password" name="password" id="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   placeholder="Biarkan kosong jika tidak diubah">
                            <button class="btn btn-outline-secondary border-start-0" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <small class="text-warning fw-semibold">
                        <i class="fas fa-info-circle me-1"></i> Kosongkan jika password tetap sama.
                    </small>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Otoritas User</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Role / Peran Sistem</label>
                        <select name="role_id" class="form-select form-control-lg @error('role_id') is-invalid @enderror" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" 
                                    {{ old('role_id', $user->roles->first()?->id) == $role->id ? 'selected' : '' }}>
                                    {{ format_role_name($role->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="d-flex">
                            <i class="fas fa-user-shield text-primary mt-1 me-2"></i>
                            <small class="text-muted">Perubahan role akan langsung mengubah izin akses user saat ini setelah mereka memuat ulang halaman.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold mb-3 shadow-sm">
                        <i class="fas fa-sync-alt me-2"></i> Update Data User
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
    .form-control, .form-select { border-radius: 8px; padding: 0.65rem 1rem; border: 1px solid #e0e0e0; }
    .form-control:focus, .form-select:focus { box-shadow: 0 0 0 4px rgba(67, 94, 190, 0.1); border-color: #435ebe; }
    .input-group-text { border-radius: 8px 0 0 8px; border: 1px solid #e0e0e0; border-right: none; }
</style>

@endsection

@push('scripts')
<script>
    // Toggle Password Visibility
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function () {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
</script>
@endpush