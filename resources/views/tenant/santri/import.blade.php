@extends('layouts.tenant')

@section('content')
<div class="page-inner">

    <div class="page-header">
        <h3 class="fw-bold mb-3">Import Batch Santri</h3>
        <small class="text-muted">
            Upload file Excel untuk preview sebelum disimpan ke database
        </small>
    </div>

    <div class="card">
        <div class="card-body">

            {{-- Success --}}
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Validation Errors --}}
            @if($errors->any())
                <div class="alert alert-danger">
                    <strong>Terjadi kesalahan:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Upload Form --}}
            <form action="{{ route('tenant.santri.import.preview') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        File Excel (.xlsx / .csv)
                    </label>

                    <input type="file"
                           name="file"
                           class="form-control"
                           accept=".xlsx,.csv"
                           required>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload & Preview
                    </button>

                    <a href="{{ route('tenant.santri.index') }}"
                       class="btn btn-secondary">
                        Kembali
                    </a>
                </div>

            </form>

            <hr>

            {{-- Format Info --}}
            <div class="mt-3">
                <h6 class="fw-bold">Format Excel yang Dibutuhkan:</h6>

                <div class="alert alert-light border">
                    <ul class="mb-0">
                        <li><strong>Kolom A:</strong> NIS (wajib)</li>
                        <li><strong>Kolom B:</strong> Nama Lengkap (wajib)</li>
                        <li><strong>Kolom C:</strong> Jenis Kelamin (L / P)</li>
                        <li><strong>Kolom D:</strong> Status (active / nonaktif / lulus / keluar)</li>
                    </ul>
                </div>

                <small class="text-muted">
                    Data tidak akan langsung masuk database. Sistem akan menampilkan preview terlebih dahulu.
                </small>
            </div>

        </div>
    </div>
</div>
@endsection