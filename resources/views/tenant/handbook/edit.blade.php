@extends('layouts.tenant')

@section('content')
<div class="page-inner">
    <!-- Header Page -->
    <div class="page-header">
        <h3 class="fw-bold mb-1">Ubah Buku Pedoman</h3>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('tenant.dashboard') }}">
                    <i class="icon-home text-primary"></i>
                </a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="{{ route('tenant.santri.handbook.index') }}" class="text-primary">Buku Pedoman Santri</a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="#" class="text-muted">Ubah Versi</a>
            </li>
        </ul>
    </div>

    <!-- Centered Form Card -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-round shadow-sm">
                <div class="card-header bg-light">
                    <div class="card-title m-0"><i class="fas fa-edit me-2 text-primary"></i>Formulir Ubah Buku Pedoman</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('tenant.santri.handbook.update', $handbook->id) }}" 
                          method="POST" 
                          enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Versi -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nomor Versi <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="version" 
                                       value="{{ old('version', $handbook->version) }}"
                                       placeholder="Contoh: 1.0.0" 
                                       class="form-control @error('version') is-invalid @enderror" 
                                       required>
                                @error('version')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Gunakan penomoran unik per versi.</small>
                            </div>

                            <!-- Tanggal Rilis -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tanggal Rilis <span class="text-danger">*</span></label>
                                <input type="date" 
                                       name="release_date" 
                                       value="{{ old('release_date', $handbook->release_date->format('Y-m-d')) }}"
                                       class="form-control @error('release_date') is-invalid @enderror" 
                                       required>
                                @error('release_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status Rilis <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="draft" {{ old('status', $handbook->status) === 'draft' ? 'selected' : '' }}>Draft (Hanya terlihat di admin panel)</option>
                                <option value="published" {{ old('status', $handbook->status) === 'published' ? 'selected' : '' }}>Published (Ditampilkan di laman publik & mengarsipkan versi lain)</option>
                                <option value="archived" {{ old('status', $handbook->status) === 'archived' ? 'selected' : '' }}>Archived (Arsip dokumen versi lama)</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Keterangan -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Keterangan / Changelog</label>
                            <textarea name="description" 
                                      rows="3" 
                                      placeholder="Tuliskan ringkasan pembaruan pada versi ini..."
                                      class="form-control @error('description') is-invalid @enderror">{{ old('description', trim($handbook->description)) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- File PDF saat ini -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Berkas PDF Saat Ini</label>
                            <div class="d-flex align-items-center bg-light p-3 rounded-3 border">
                                <i class="fas fa-file-pdf fa-2x text-danger me-3"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-bold small text-dark">{{ basename($handbook->file_path) }}</div>
                                    <a href="{{ asset($handbook->file_path) }}" target="_blank" class="small text-primary fw-bold text-decoration-none">
                                        <i class="fas fa-external-link-alt me-1"></i> Buka PDF di Tab Baru
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Ganti File PDF -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Ganti Berkas PDF <span class="text-muted small">(Opsional)</span></label>
                            <input type="file" 
                                   name="file" 
                                   class="form-control @error('file') is-invalid @enderror" 
                                   accept="application/pdf">
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Kosongkan jika tidak ingin mengganti berkas PDF. Format harus PDF, maksimal 10 MB.</small>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="{{ route('tenant.santri.handbook.index') }}" class="btn btn-light btn-round">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary btn-round">
                                <i class="fas fa-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection