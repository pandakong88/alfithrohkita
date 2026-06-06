@extends('layouts.tenant')

@section('content')
<div class="page-inner">
    <!-- Header Page -->
    <div class="page-header">
        <h3 class="fw-bold mb-1">Tambah Buku Pedoman</h3>
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
                <a href="#" class="text-muted">Tambah Versi</a>
            </li>
        </ul>
    </div>

    <!-- Centered Form Card -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-round shadow-sm">
                <div class="card-header bg-light">
                    <div class="card-title m-0"><i class="fas fa-file-upload me-2 text-primary"></i>Formulir Tambah Buku Pedoman</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('tenant.santri.handbook.store') }}" 
                          method="POST" 
                          enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <!-- Versi -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nomor Versi <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="version" 
                                       value="{{ old('version') }}"
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
                                       value="{{ old('release_date', date('Y-m-d')) }}"
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
                                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft (Hanya terlihat di admin panel)</option>
                                <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published (Ditampilkan di laman publik & mengarsipkan versi lain)</option>
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
                                      class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- File PDF -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Dokumen Buku Pedoman (PDF) <span class="text-danger">*</span></label>
                            <input type="file" 
                                   name="file" 
                                   class="form-control @error('file') is-invalid @enderror" 
                                   accept="application/pdf"
                                   required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format berkas harus PDF, maksimal ukuran 10 MB.</small>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="{{ route('tenant.santri.handbook.index') }}" class="btn btn-light btn-round">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary btn-round">
                                <i class="fas fa-save me-1"></i> Simpan Versi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection