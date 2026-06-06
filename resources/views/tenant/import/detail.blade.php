@extends('layouts.tenant')

@section('title', 'Detail Transaksi Import')

@section('content')
<div class="container-fluid" style="background: #f8fafc; min-height: 90vh;">
    <div class="page-inner py-4" style="padding-top: 15px !important;">
        
        {{-- TOP BAR: STATUS & ACTIONS --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 flex-wrap gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">Detail Transaksi Batch #{{ $batch->id }}</h3>
                <p class="text-muted small mb-0"><i class="fas fa-file-excel me-2 text-success"></i> Sumber Berkas: <strong>{{ $batch->filename }}</strong></p>
            </div>
            <div class="d-flex gap-2">
                {{-- Tombol Download Error (Jika ada) --}}
                @if($batch->invalid_rows > 0)
                    <a href="{{ route('tenant.import.errors.download', $batch->id) }}" class="btn btn-danger btn-border btn-round btn-sm shadow-sm">
                        <i class="fas fa-download me-2"></i> Unduh Baris Gagal ({{ $batch->invalid_rows }})
                    </a>
                @endif
            
                {{-- Tombol COMMIT (Hanya muncul jika status PREVIEW) --}}
                @if($batch->status == 'preview')
                    <button type="button" onclick="confirmCommit()" class="btn btn-success btn-round btn-sm shadow-sm">
                        <i class="fas fa-check-circle me-2"></i> Commit Data Sekarang
                    </button>
                    <form id="formCommit" method="POST" action="{{ route('tenant.import.commit', $batch->id) }}" class="d-none">
                        @csrf
                    </form>
                @endif
                
                {{-- Tombol ROLLBACK (Hanya muncul jika status COMMITTED) --}}
                @if($batch->status == 'committed')
                    <button type="button" onclick="confirmRollback()" class="btn btn-danger btn-round btn-sm shadow-sm">
                        <i class="fas fa-undo me-2"></i> Batalkan Import (Rollback)
                    </button>
                    <form id="formRollback" method="POST" action="{{ route('tenant.import.rollback', $batch->id) }}" class="d-none">
                        @csrf
                    </form>
                @endif
            </div>
        </div>

        <div class="row g-4">
            {{-- SUMMARY CARDS --}}
            <div class="col-md-4">
                <div class="card card-round border-0 shadow-sm mb-0">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="fw-bold mb-0 text-dark" style="font-size: 14px;"><i class="fas fa-info-circle me-2 text-primary"></i>Ringkasan Statistik</h5>
                    </div>
                    <div class="card-body py-2">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                <span class="text-muted small">Status Sistem</span>
                                @php
                                    $statusClasses = [
                                        'committed' => 'badge-status-committed',
                                        'preview' => 'badge-status-preview',
                                        'failed' => 'badge-status-failed',
                                        'rolled_back' => 'badge-status-rolledback',
                                    ];
                                    $statusClass = $statusClasses[strtolower($batch->status)] ?? 'badge-status-rolledback';
                                @endphp
                                <span class="badge {{ $statusClass }} px-3 py-1.5 rounded-pill" style="font-size: 10px; font-weight: bold;">{{ strtoupper($batch->status) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                <span class="text-muted small">Total Baris Excel</span>
                                <span class="fw-bold text-dark">{{ $batch->total_rows }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                <span class="text-muted text-success small">Baris Valid (Sukses)</span>
                                <span class="fw-bold text-success">{{ $batch->valid_rows }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                <span class="text-muted text-danger small">Baris Error (Gagal)</span>
                                <span class="fw-bold text-danger">{{ $batch->invalid_rows }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- AUDIT LOG: PERUBAHAN DATA --}}
            <div class="col-md-8">
                <div class="card card-round border-0 shadow-sm overflow-hidden mb-0 h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark" style="font-size: 14px;"><i class="fas fa-history me-2 text-primary"></i>Log Perubahan Data (Audit)</h5>
                        <span class="badge badge-primary-light px-2 py-1" style="font-size: 11px;">{{ count($batch->changes) }} Perubahan</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 290px;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light sticky-top">
                                    <tr style="font-size: 11px; color: #475569;">
                                        <th class="ps-4">Entitas</th>
                                        <th>Kolom</th>
                                        <th>Data Lama</th>
                                        <th class="pe-4">Data Baru</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($batch->changes as $change)
                                    <tr class="log-change-row">
                                        <td class="ps-4">
                                            @php
                                                $entityClasses = [
                                                    'santri' => 'badge-entity-santri',
                                                    'wali' => 'badge-entity-wali',
                                                    'custom' => 'badge-entity-custom'
                                                ];
                                                $entityClass = $entityClasses[strtolower($change->entity)] ?? 'badge-entity-other';
                                            @endphp
                                            <span class="badge {{ $entityClass }} px-2 py-1" style="font-size: 10px;">{{ strtoupper($change->entity) }}</span>
                                        </td>
                                        <td class="fw-bold text-muted" style="font-size: 12px;">{{ $change->column_name }}</td>
                                        <td>
                                            @if(is_null($change->old_value) || $change->old_value === '' || $change->old_value === 'null')
                                                <span class="text-muted small">-</span>
                                            @else
                                                <span class="old-val-badge small">{{ Str::limit($change->old_value, 40) }}</span>
                                            @endif
                                        </td>
                                        <td class="pe-4">
                                            @if(is_null($change->new_value) || $change->new_value === '' || $change->new_value === 'null')
                                                <span class="text-muted small">-</span>
                                            @else
                                                <span class="new-val-badge small">{{ Str::limit($change->new_value, 40) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted small">
                                            <i class="fas fa-info-circle fa-2x mb-2 text-muted" style="opacity: 0.5;"></i>
                                            <p class="mb-0">Tidak ada perubahan nilai kolom (hanya penambahan record baru).</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ROWS DETAIL --}}
            <div class="col-12 mt-4">
                <div class="card card-round border-0 shadow-sm overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="fw-bold mb-0 text-dark" style="font-size: 14px;"><i class="fas fa-database me-2 text-primary"></i>Detail Per Baris (Raw Data)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="rowsTable" class="table table-hover mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr style="font-size: 11px; color: #475569;">
                                        <th class="ps-4">No. Baris</th>
                                        <th>Mode</th>
                                        <th>Status Validasi</th>
                                        <th class="pe-4">Detail Data & Masalah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($batch->rows as $row)
                                    <tr class="raw-data-row {{ !$row->is_valid ? 'bg-soft-danger' : '' }}">
                                        <td class="ps-4 fw-bold text-dark" style="font-size: 13px;">Baris #{{ $row->row_number }}</td>
                                        <td>
                                            @php
                                                $modeClasses = [
                                                    'insert' => 'badge-entity-wali',
                                                    'update' => 'badge-entity-santri'
                                                ];
                                                $modeClass = $modeClasses[strtolower($row->mode)] ?? 'badge-entity-other';
                                            @endphp
                                            <span class="badge {{ $modeClass }} px-2.5 py-1" style="font-size: 10px;">
                                                {{ strtoupper($row->mode) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($row->is_valid)
                                                <span class="badge bg-success-light text-success px-2 py-1 border border-success" style="font-size: 10px; font-weight: bold; background-color: #f6ffed !important;">
                                                    <i class="fas fa-check-circle me-1"></i> VALID
                                                </span>
                                            @else
                                                <span class="badge bg-danger-light text-danger px-2 py-1 border border-danger" style="font-size: 10px; font-weight: bold; background-color: #fff2e8 !important;">
                                                    <i class="fas fa-exclamation-circle me-1"></i> ERROR
                                                </span>
                                            @endif
                                        </td>
                                        <td class="pe-4 py-3">
                                            @if($row->errors)
                                                <div class="alert alert-danger py-1.5 px-3 mb-2 small rounded border-0" style="background-color: #fff2f0; color: #a61d24;">
                                                    <strong>Masalah:</strong> {{ implode(', ', (array)$row->errors) }}
                                                </div>
                                            @endif
                                            <button class="btn btn-link btn-xs p-0 text-primary fw-bold text-decoration-none" onclick="viewPayload({{ json_encode($row->payload) }})" style="font-size: 11px;">
                                                <i class="fas fa-code me-1"></i> Lihat Payload JSON
                                            </button>
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

{{-- MODAL UNTUK JSON VIEW --}}
<div class="modal fade" id="payloadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content card-round">
            <div class="modal-header">
                <h5 class="fw-bold mb-0">Raw Payload Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <pre id="jsonViewer" class="bg-dark text-light p-3 rounded-3" style="font-size: 12px; max-height: 400px; overflow: auto;"></pre>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-soft-danger { background-color: rgba(220, 53, 69, 0.03) !important; }
    .card-round { border-radius: 12px !important; }
    pre { white-space: pre-wrap; word-wrap: break-word; }
    
    #rowsTable thead th {
        background-color: #f8fafc !important;
        color: #475569 !important;
        border-bottom: 2px solid #cbd5e1 !important;
        font-weight: 600 !important;
        text-transform: uppercase;
        font-size: 10px !important;
        letter-spacing: 0.5px;
        padding: 12px 16px !important;
    }
    #rowsTable tbody td {
        padding: 12px 16px !important;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9 !important;
    }
    
    .badge-status-committed { background-color: #e6f4ed; color: #1e7e34; border: 1px solid #c3e6cb; }
    .badge-status-preview { background-color: #fffbeb; color: #b78103; border: 1px solid #ffeeba; }
    .badge-status-failed { background-color: #fdf2f2; color: #dc3545; border: 1px solid #f5c6cb; }
    .badge-status-rolledback { background-color: #f8f9fa; color: #6c757d; border: 1px solid #e2e3e5; }
    
    .badge-primary-light {
        background-color: #e1f0ff !important;
        color: #1572e8 !important;
    }

    .badge-entity-santri { background-color: #e6f4ff; color: #0958d9; border: 1px solid #91caee; }
    .badge-entity-wali { background-color: #f6ffed; color: #389e0d; border: 1px solid #b7eb8f; }
    .badge-entity-custom { background-color: #f9f0ff; color: #531dab; border: 1px solid #d3adf7; }
    .badge-entity-other { background-color: #f5f5f5; color: #595959; border: 1px solid #d9d9d9; }
    
    .old-val-badge { 
        background-color: #fff1f0; 
        color: #cf1322; 
        border: 1px solid #ffa39e; 
        padding: 2px 8px; 
        border-radius: 4px; 
        text-decoration: line-through;
        display: inline-block;
        max-width: 100%;
        word-break: break-all;
    }
    .new-val-badge { 
        background-color: #f6ffed; 
        color: #389e0d; 
        border: 1px solid #b7eb8f; 
        padding: 2px 8px; 
        border-radius: 4px; 
        font-weight: bold;
        display: inline-block;
        max-width: 100%;
        word-break: break-all;
    }
    
    .log-change-row, .raw-data-row {
        transition: background-color 0.2s ease;
    }
    .log-change-row:hover, .raw-data-row:hover {
        background-color: #f8fafc !important;
    }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#rowsTable').DataTable({
            "pageLength": 5,
            "dom": '<"p-3 d-flex justify-content-between"lf>rt<"p-3 d-flex justify-content-between"ip>'
        });
    });

    function viewPayload(json) {
        $('#jsonViewer').text(JSON.stringify(json, null, 4));
        $('#payloadModal').modal('show');
    }

    function confirmRollback() {
        Swal.fire({
            title: 'Batalkan Import Data?',
            text: "Seluruh data yang telah masuk akan dihapus/dikembalikan ke kondisi semula. Tindakan ini tidak dapat dibatalkan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Batalkan Semua!',
            cancelButtonText: 'Kembali'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#formRollback').submit();
            }
        })
    }

    function confirmCommit() {
        Swal.fire({
            title: 'Konfirmasi Commit Data',
            text: "Apakah Anda sudah yakin data ini benar? Data akan segera dimasukkan ke sistem utama pondok.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1572e8', // Warna biru khas primary
            cancelButtonColor: '#6861ce',
            confirmButtonText: 'Ya, Masukkan Data!',
            cancelButtonText: 'Cek Lagi',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                // Menampilkan loading saat proses commit berlangsung
                Swal.showLoading();
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $('#formCommit').submit();
            }
        })
    }
</script>
@endpush