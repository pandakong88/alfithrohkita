@extends('layouts.tenant')

@section('content')
<div class="page-inner" style="padding-top: 15px !important;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Registrasi Wali Baru</h3>
            <p class="text-muted small mb-0">Tambahkan data orang tua atau wali santri ke dalam sistem.</p>
        </div>
        <a href="{{ route('tenant.wali.index') }}" class="btn btn-outline-secondary btn-round btn-sm">
            <i class="fas fa-arrow-left me-2"></i> Kembali
        </a>
    </div>

    <form method="POST" action="{{ route('tenant.wali.store') }}">
        @csrf

        <div class="row">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold text-primary">
                            <i class="fas fa-user-circle me-2"></i>Informasi Pribadi
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Nama Lengkap Wali</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                                <input type="text" name="nama" value="{{ old('nama') }}" 
                                       class="form-control @error('nama') is-invalid @enderror" 
                                       placeholder="Masukkan nama lengkap sesuai KTP" required>
                                @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">NIK (Nomor Induk Kependudukan)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-id-card text-muted"></i></span>
                                    <input type="text" name="nik" value="{{ old('nik') }}" 
                                           class="form-control @error('nik') is-invalid @enderror" 
                                           placeholder="16 digit NIK">
                                    @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">Nomor WhatsApp/HP</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fab fa-whatsapp text-muted"></i></span>
                                    <input type="text" name="no_hp" value="{{ old('no_hp') }}" 
                                           class="form-control @error('no_hp') is-invalid @enderror" 
                                           placeholder="Contoh: 08123456789" required>
                                    @error('no_hp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold">Alamat Lengkap</label>
                            <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" 
                                      rows="3" placeholder="Jl. Nama Jalan, No. Rumah, RT/RW, Desa, Kecamatan">{{ old('alamat') }}</textarea>
                            @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold text-primary">
                            <i class="fas fa-briefcase me-2"></i>Pekerjaan
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="form-label fw-bold">Pekerjaan Saat Ini</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-tools text-muted"></i></span>
                                <input type="text" name="pekerjaan" value="{{ old('pekerjaan') }}" 
                                       class="form-control @error('pekerjaan') is-invalid @enderror" 
                                       placeholder="Contoh: Petani, Guru, Wiraswasta">
                                @error('pekerjaan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i> Data ini digunakan untuk pemetaan ekonomi wali murid.
                        </small>
                    </div>
                </div>

                <div class="card border-0 shadow-sm bg-primary text-white">
                    <div class="card-body">
                        <p class="small opacity-75">Pastikan semua data sudah benar sebelum menyimpan. Data wali akan langsung terhubung dengan manajemen santri.</p>
                        <button type="submit" class="btn btn-white fw-bold w-100 shadow-sm">
                            <i class="fas fa-save me-2 text-primary"></i> Simpan Data Wali
                        </button>
                        <a href="{{ route('tenant.wali.index') }}" class="btn btn-link text-white w-100 mt-2 btn-sm opacity-75">Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .card { border-radius: 12px; }
    .btn-round { border-radius: 50px; }
    .form-control { border-radius: 8px; padding: 0.65rem 1rem; border: 1px solid #e0e0e0; }
    .form-control:focus { box-shadow: 0 0 0 4px rgba(67, 94, 190, 0.1); border-color: #435ebe; }
    .input-group-text { border-radius: 8px 0 0 8px; border: 1px solid #e0e0e0; border-right: none; }
    .input-group .form-control { border-radius: 0 8px 8px 0; }
    .btn-white { background: white; color: #1572e8; border: none; }
    .btn-white:hover { background: #f8f9fa; color: #1572e8; }
</style>
@endsection