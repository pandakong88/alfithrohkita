@extends('layouts.tenant')

@section('title', 'Riwayat Import Data')

@section('content')
<div class="container-fluid" style="background: #f8fafc; min-height: 90vh;">
    <div class="page-inner py-4" style="padding-top: 15px !important;">
        
        {{-- HEADER --}}
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">
                    <i class="fas fa-history text-success me-2"></i> Riwayat Integrasi Data
                </h3>
                <p class="text-muted small mb-0">Catatan seluruh aktivitas unggah berkas dan status kompilasi data pondok Anda.</p>
            </div>
        </div>

        {{-- SUMMARY STATS --}}
        <div class="row mb-4">
            <div class="col-6 col-md-3">
                <div class="card card-round border-0 shadow-sm mb-0 h-100">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center bg-primary-gradient text-white shadow-sm" style="width: 44px; height: 44px; min-width: 44px;">
                            <i class="fas fa-history fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0 small" style="font-size: 11px;">Total Transaksi</h6>
                            <h4 class="fw-bold text-dark mb-0">{{ count($batches) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                @php
                    $completedCount = $batches->filter(function($b) { return $b->status === 'committed'; })->count();
                @endphp
                <div class="card card-round border-0 shadow-sm mb-0 h-100">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center bg-success-gradient text-white shadow-sm" style="width: 44px; height: 44px; min-width: 44px;">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0 small" style="font-size: 11px;">Selesai (Commit)</h6>
                            <h4 class="fw-bold text-dark mb-0">{{ $completedCount }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mt-3 mt-md-0">
                @php
                    $previewCount = $batches->filter(function($b) { return $b->status === 'preview'; })->count();
                @endphp
                <div class="card card-round border-0 shadow-sm mb-0 h-100">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center bg-warning-gradient text-white shadow-sm" style="width: 44px; height: 44px; min-width: 44px;">
                            <i class="fas fa-eye fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0 small" style="font-size: 11px;">Menunggu Review</h6>
                            <h4 class="fw-bold text-dark mb-0">{{ $previewCount }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mt-3 mt-md-0">
                @php
                    $failedCount = $batches->filter(function($b) { return in_array($b->status, ['failed', 'rolled_back']); })->count();
                @endphp
                <div class="card card-round border-0 shadow-sm mb-0 h-100">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center bg-danger-gradient text-white shadow-sm" style="width: 44px; height: 44px; min-width: 44px;">
                            <i class="fas fa-exclamation-triangle fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0 small" style="font-size: 11px;">Gagal / Rollback</h6>
                            <h4 class="fw-bold text-dark mb-0">{{ $failedCount }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card card-round border-0 shadow-sm overflow-hidden">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="historyTable" class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small text-uppercase">
                                        <th class="ps-4">ID & Berkas</th>
                                        <th class="text-center">Statistik Baris</th>
                                        <th class="text-center">Status</th>
                                        <th>Pengunggah</th>
                                        <th>Waktu Transaksi</th>
                                        <th class="text-end pe-4">Opsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($batches as $batch)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3" style="width: 32px; height: 32px;">
                                                    <span class="avatar-title rounded-3 bg-soft-success text-success fw-bold" style="font-size: 10px;">
                                                        #{{ $batch->id }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="text-dark fw-bold d-block mb-0" style="font-size: 13px;">{{ Str::limit($batch->filename, 25) }}</span>
                                                    <small class="text-muted" style="font-size: 10px;">Excel Spreadsheet</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="min-width: 180px;">
                                            <div class="d-flex justify-content-between mb-1" style="font-size: 11px;">
                                                <span class="text-success fw-bold">{{ $batch->valid_rows }} Sukses</span>
                                                <span class="text-danger fw-bold">{{ $batch->invalid_rows }} Gagal</span>
                                            </div>
                                            <div class="progress" style="height: 6px; border-radius: 10px; background: #eee;">
                                                @php 
                                                    $percent = $batch->total_rows > 0 ? ($batch->valid_rows / $batch->total_rows) * 100 : 0;
                                                @endphp
                                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%"></div>
                                                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ 100 - $percent }}%"></div>
                                            </div>
                                            <small class="text-center d-block mt-1 text-muted" style="font-size: 10px;">Total: {{ $batch->total_rows }} Baris</small>
                                        </td>
                                        <td class="text-center">
                                            @if($batch->status === 'committed')
                                                <span class="badge badge-status-committed px-3 py-1.5 rounded-pill" style="font-size: 10px; font-weight: bold;">
                                                    <i class="fas fa-check-double me-1"></i> SELESAI
                                                </span>
                                            @elseif($batch->status === 'preview')
                                                <span class="badge badge-status-preview px-3 py-1.5 rounded-pill" style="font-size: 10px; font-weight: bold;">
                                                    <i class="fas fa-eye me-1"></i> PREVIEW
                                                </span>
                                            @elseif($batch->status === 'failed')
                                                <span class="badge badge-status-failed px-3 py-1.5 rounded-pill" style="font-size: 10px; font-weight: bold;">
                                                    <i class="fas fa-times-circle me-1"></i> GAGAL
                                                </span>
                                            @elseif($batch->status === 'rolled_back')
                                                <span class="badge badge-status-rolledback px-3 py-1.5 rounded-pill" style="font-size: 10px; font-weight: bold;">
                                                    <i class="fas fa-undo me-1"></i> ROLLBACK
                                                </span>
                                            @else
                                                <span class="badge badge-status-rolledback px-3 py-1.5 rounded-pill" style="font-size: 10px; font-weight: bold;">
                                                    {{ strtoupper($batch->status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-circle text-muted me-2 fa-lg"></i>
                                                <span class="fw-medium text-dark" style="font-size: 12px;">{{ $batch->uploader->name ?? 'Sistem' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted small fw-bold">
                                                <i class="far fa-clock me-1 text-success"></i> 
                                                {{ $batch->created_at->translatedFormat('d M Y, H:i') }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('tenant.import.detail', $batch->id) }}" 
                                               class="btn btn-icon btn-round btn-info btn-xs" 
                                               data-bs-toggle="tooltip" title="Lihat Rincian">
                                                <i class="fas fa-external-link-alt" style="font-size: 10px;"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    .fw-extrabold { font-weight: 800; }
    .bg-soft-success { background: rgba(40, 167, 69, 0.08); }
    .card-round { border-radius: 15px !important; }
    .gap-3 { gap: 1rem !important; }

    .bg-primary-gradient {
        background: linear-gradient(135deg, #1572e8 0%, #04befe 100%) !important;
    }
    .bg-warning-gradient {
        background: linear-gradient(135deg, #ffa534 0%, #ffc107 100%) !important;
    }
    .bg-success-gradient {
        background: linear-gradient(135deg, #2bb930 0%, #66bb6a 100%) !important;
    }
    .bg-danger-gradient {
        background: linear-gradient(135deg, #f25961 0%, #f3545d 100%) !important;
    }

    #historyTable thead th {
        background-color: #f8fafc !important;
        color: #475569 !important;
        border-bottom: 2px solid #cbd5e1 !important;
        font-weight: 600 !important;
        text-transform: uppercase;
        font-size: 10px !important;
        letter-spacing: 0.5px;
        padding: 12px 16px !important;
    }

    #historyTable tbody tr {
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
    }
    
    #historyTable tbody tr:hover {
        background-color: #f8fafc !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02) !important;
    }

    #historyTable tbody td {
        padding: 14px 16px !important;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9 !important;
    }

    .badge-status-committed { background-color: #e6f4ed; color: #1e7e34; border: 1px solid #c3e6cb; }
    .badge-status-preview { background-color: #fffbeb; color: #b78103; border: 1px solid #ffeeba; }
    .badge-status-failed { background-color: #fdf2f2; color: #dc3545; border: 1px solid #f5c6cb; }
    .badge-status-rolledback { background-color: #f8f9fa; color: #6c757d; border: 1px solid #e2e3e5; }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#historyTable').DataTable({
            "order": [[4, "desc"]], // Urutkan berdasarkan waktu terbaru (kolom ke-5)
            "pageLength": 10,
            "language": {
                "search": "",
                "searchPlaceholder": "Cari riwayat import...",
                "lengthMenu": "_MENU_",
                "info": "Data ke _START_ sampai _END_ dari _TOTAL_ arsip",
                "paginate": {
                    "next": '<i class="fa fa-chevron-right"></i>',
                    "previous": '<i class="fa fa-chevron-left"></i>'
                }
            },
            "dom": '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
        });
        
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
@endpush