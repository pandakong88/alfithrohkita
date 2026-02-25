@extends('layouts.tenant')

@section('content')
<div class="page-inner" style="padding-top: 15px !important;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Edit Data Wali</h3>
            <p class="text-muted small mb-0">Perbarui informasi profil atau kontak dari <strong>{{ $wali->nama }}</strong>.</p>
        </div>
        <a href="{{ route('tenant.wali.index') }}" class="btn btn-outline-secondary btn-round btn-sm">
            <i class="fas fa-arrow-left me-2"></i> Kembali
        </a>
    </div>

    <form method="POST" action="{{ route('tenant.wali.update', $wali) }}">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold text-primary">
                            <i class="fas fa-user-edit me-2"></i>Data Profil Wali
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Nama Lengkap</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                                <input type="text" name="nama" value="{{ old('nama', $wali->nama) }}" 
                                       class="form-control @error('nama') is-invalid @enderror" 
                                       placeholder="Nama lengkap wali" required>
                                @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold text-dark">NIK</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-id-card text-muted"></i></span>
                                    <input type="text" name="nik" value="{{ old('nik', $wali->nik) }}" 
                                           class="form-control @error('nik') is-invalid @enderror" 
                                           placeholder="16 digit NIK">
                                    @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold text-dark">No. WhatsApp</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fab fa-whatsapp text-muted"></i></span>
                                    <input type="text" name="no_hp" value="{{ old('no_hp', $wali->no_hp) }}" 
                                           class="form-control @error('no_hp') is-invalid @enderror" 
                                           placeholder="Nomor aktif" required>
                                    @error('no_hp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold text-dark">Alamat</label>
                            <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" 
                                      rows="3">{{ old('alamat', $wali->alamat) }}</textarea>
                            @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold text-primary">
                            <i class="fas fa-info-circle me-2"></i>Pekerjaan & Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Pekerjaan</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-tools text-muted"></i></span>
                                <input type="text" name="pekerjaan" value="{{ old('pekerjaan', $wali->pekerjaan) }}" 
                                       class="form-control @error('pekerjaan') is-invalid @enderror">
                                @error('pekerjaan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="p-3 bg-light rounded-3 border">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-clock text-muted me-2"></i>
                                <small class="text-muted">Terdaftar pada: {{ $wali->created_at->format('d M Y') }}</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-link text-primary me-2"></i>
                                <small class="fw-bold text-primary">{{ $wali->santris()->count() }} Santri Terhubung</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm border-top border-primary border-3">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold mb-3 shadow-sm">
                            <i class="fas fa-sync-alt me-2"></i> Update Data Wali
                        </button>
                        <a href="{{ route('tenant.wali.index') }}" class="btn btn-light w-100 btn-round">Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .card { border-radius: 12px; }
    .btn-round { border-radius: 50px; }
    .form-control { border-radius: 8px; padding: 0.65rem 1rem; border: 1px solid #e0e0e0; font-size: 0.9rem; }
    .form-control:focus { box-shadow: 0 0 0 4px rgba(67, 94, 190, 0.1); border-color: #435ebe; }
    .input-group-text { border-radius: 8px 0 0 8px; border: 1px solid #e0e0e0; border-right: none; }
    .input-group .form-control { border-radius: 0 8px 8px 0; }
</style>
@endsection