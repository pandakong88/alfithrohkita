@extends('layouts.tenant')

@section('title', 'Preview Import Santri')

@section('content')
<div class="container">
    <div class="page-inner py-5">
        
        <div class="max-w-7xl mx-auto">
            {{-- PAGE HEADER --}}
            <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row mb-4">
                <div>
                    <h2 class="text-dark fw-bold mb-1">Pratinjau Import Data</h2>
                    <h6 class="text-muted mb-0">Silakan periksa kembali data Anda sebelum disimpan secara permanen.</h6>
                </div>
                <div class="ms-md-auto py-2 py-md-0">
                    <a href="{{ route('tenant.santri.import') }}" class="btn btn-label-danger btn-round me-2">
                        <i class="fas fa-undo me-1"></i> Batalkan
                    </a>
                    @if($batch->status == 'preview' && $batch->invalid_rows == 0)
                        <form method="POST" action="{{ route('tenant.santri.import.commit', $batch->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-round shadow-sm">
                                <span class="btn-label"><i class="fa fa-check-circle"></i></span>
                                Konfirmasi & Simpan
                            </button>
                        </form>
                    @elseif($batch->invalid_rows > 0)
                        <button class="btn btn-success btn-round opacity-50 cursor-not-allowed" disabled title="Perbaiki data yang error terlebih dahulu">
                            Konfirmasi & Simpan
                        </button>
                    @endif
                </div>
            </div>

            {{-- SUMMARY STATS CARD --}}
            <div class="row mb-4">
                <div class="col-sm-6 col-md-3">
                    <div class="card card-stats card-round border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-secondary bubble-shadow-small">
                                        <i class="fas fa-list-ol"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Total Baris</p>
                                        <h4 class="card-title">{{ $batch->total_rows }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card card-stats card-round border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-success bubble-shadow-small">
                                        <i class="fas fa-check-double"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Data Valid</p>
                                        <h4 class="card-title text-success">{{ $batch->valid_rows }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card card-stats card-round border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-danger bubble-shadow-small">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Data Error</p>
                                        <h4 class="card-title text-danger">{{ $batch->invalid_rows }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card card-stats card-round border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-info bubble-shadow-small">
                                        <i class="fas fa-info-circle"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Status</p>
                                        <span class="badge {{ $batch->status == 'preview' ? 'badge-warning' : ($batch->status == 'committed' ? 'badge-success' : 'badge-danger') }}">
                                            {{ strtoupper($batch->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($batch->invalid_rows > 0)
            <div class="alert alert-label-danger border-0 shadow-sm mb-4 d-flex align-items-center">
                <i class="fas fa-exclamation-circle fa-lg me-3"></i>
                <div>
                    <span class="fw-bold">Perhatian:</span> Ditemukan <strong>{{ $batch->invalid_rows }}</strong> baris data bermasalah. Sistem tidak dapat melanjutkan simpan data sebelum file diperbaiki dan diupload ulang.
                </div>
            </div>
            @endif

            {{-- DATA TABLE CARD --}}
            <div class="card card-round border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="card-title fw-bold text-dark mb-0">Rincian Data Import</h5>
                        <div class="badge badge-count">{{ count($batch->rows) }} Item Terdeteksi</div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-head-bg-light mb-0 align-middle">
                            <thead>
                                <tr class="text-uppercase font-xs">
                                    <th class="ps-4">Baris</th>
                                    <th>NIS</th>
                                    <th>Nama Lengkap</th>
                                    <th class="text-center">Mode</th>
                                    <th>Status Validasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($batch->rows as $row)
                                @php $payload = $row->payload ?? []; @endphp
                                <tr>
                                    <td class="ps-4">
                                        <span class="text-muted fw-bold">#{{ $row->row_number }}</span>
                                    </td>
                                    <td><code class="text-primary fw-bold">{{ $payload['nis'] ?? '-' }}</code></td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $payload['nama_lengkap'] ?? '-' }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if($row->mode == 'insert')
                                            <span class="badge badge-pill badge-success-light text-success px-3">
                                                <i class="fas fa-plus-circle me-1"></i> INSERT
                                            </span>
                                        @elseif($row->mode == 'update')
                                            <span class="badge badge-pill badge-primary-light text-primary px-3">
                                                <i class="fas fa-edit me-1"></i> UPDATE
                                            </span>
                                        @else
                                            <span class="badge badge-pill badge-danger-light text-danger px-3">
                                                <i class="fas fa-times-circle me-1"></i> ERROR
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->errors)
                                            <div class="d-flex flex-column">
                                                @foreach($row->errors as $error)
                                                    <span class="text-danger small"><i class="fas fa-minus me-1"></i> {{ $error }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-success small fw-bold">
                                                <i class="fas fa-check-circle me-1"></i> Siap Diimport
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-4">
                    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between">
                        {{-- Info Status --}}
                        <div class="text-muted small mb-3 mb-md-0">
                            Menampilkan <span class="fw-bold text-dark">{{ $rows->firstItem() }}</span> 
                            sampai <span class="fw-bold text-dark">{{ $rows->lastItem() }}</span> 
                            dari <span class="fw-bold text-dark">{{ $rows->total() }}</span> data santri
                        </div>
                
                        {{-- Pagination Nav --}}
                        <div class="pagination-modern">
                            {{ $rows->links() }}
                        </div>
                    </div>
                </div>
                
                <style>
                    /* Merapikan Pagination agar senada dengan Kai Admin */
                    .pagination-modern .pagination {
                        margin-bottom: 0;
                        gap: 5px;
                    }
                    .pagination-modern .page-item .page-link {
                        border: none;
                        border-radius: 8px !important;
                        padding: 8px 16px;
                        color: #4b5563;
                        font-weight: 600;
                        transition: all 0.2s;
                        background-color: #f3f4f6;
                    }
                    .pagination-modern .page-item.active .page-link {
                        background-color: #1572e8 !important; /* Warna Primary Kai Admin */
                        color: white !important;
                        box-shadow: 0 4px 12px rgba(21, 114, 232, 0.4);
                    }
                    .pagination-modern .page-item.disabled .page-link {
                        background-color: #f9fafb;
                        color: #d1d5db;
                    }
                    .pagination-modern .page-item:not(.active):hover .page-link {
                        background-color: #e5e7eb;
                        color: #111827;
                    }
                </style>
            </div>
        </div>

    </div>
</div>

<style>
    /* SaaS Colors & Helpers */
    .badge-success-light { background-color: #d1fae5; color: #065f46; border: none; }
    .badge-primary-light { background-color: #e0f2fe; color: #0369a1; border: none; }
    .badge-danger-light { background-color: #fee2e2; color: #b91c1c; border: none; }
    .alert-label-danger { background-color: #fff5f5; color: #c53030; border-left: 4px solid #fc8181; }
    
    .table-head-bg-light thead th {
        background: #f8fbff !important;
        color: #4b5563 !important;
        font-weight: 700;
        border-bottom: 1px solid #e5e7eb;
    }
    .card-round { border-radius: 1rem !important; }
    .font-xs { font-size: 0.75rem; letter-spacing: 0.05em; }
    .max-w-7xl { max-width: 1200px; margin: auto; }
    
    /* Stats Styling */
    .icon-big { font-size: 2rem; width: 60px; height: 60px; line-height: 60px; display: inline-block; border-radius: 12px; }
    .bubble-shadow-small { box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    .card-stats .card-category { color: #8d9498; margin-bottom: 0; font-size: 13px; }
</style>
@endsection