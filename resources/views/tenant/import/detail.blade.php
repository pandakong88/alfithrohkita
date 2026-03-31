@extends('layouts.tenant')

@section('title', 'Detail Transaksi Import')

@section('content')
<div class="container-fluid" style="background: #f4f7fa; min-height: 90vh;">
    <div class="page-inner py-5">
        
        {{-- TOP BAR: STATUS & ACTIONS --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h2 class="fw-extrabold text-dark mb-1">Detail Batch #{{ $batch->id }}</h2>
                <p class="text-muted"><i class="fas fa-file-excel me-2"></i> Sumber Berkas: <strong>{{ $batch->filename }}</strong></p>
            </div>
            <div class="d-flex gap-2">
                {{-- Tombol Download Error (Jika ada) --}}
                @if($batch->invalid_rows > 0)
                    <a href="{{ route('tenant.import.errors.download', $batch->id) }}" class="btn btn-outline-danger btn-round">
                        <i class="fas fa-download me-2"></i> Unduh Baris Gagal ({{ $batch->invalid_rows }})
                    </a>
                @endif
            
                {{-- Tombol COMMIT (Hanya muncul jika status PREVIEW) --}}
                @if($batch->status == 'preview')
                    <button type="button" onclick="confirmCommit()" class="btn btn-primary btn-round shadow-lg">
                        <i class="fas fa-check-double me-2"></i> Commit Data Sekarang
                    </button>
                    <form id="formCommit" method="POST" action="{{ route('tenant.import.commit', $batch->id) }}" class="d-none">
                        @csrf
                    </form>
                @endif
                
                {{-- Tombol ROLLBACK (Hanya muncul jika status COMMITTED) --}}
                @if($batch->status == 'committed')
                    <button type="button" onclick="confirmRollback()" class="btn btn-danger btn-round shadow-lg">
                        <i class="fas fa-history me-2"></i> Batalkan Import (Rollback)
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
                <div class="card card-round border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-4">
                        <h5 class="fw-bold mb-0">Ringkasan Statistik</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Status Sistem</span>
                                <span class="badge {{ $batch->status == 'committed' ? 'bg-success' : 'bg-warning' }}">{{ strtoupper($batch->status) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Total Baris</span>
                                <span class="fw-bold">{{ $batch->total_rows }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted text-success">Baris Berhasil</span>
                                <span class="fw-bold text-success">{{ $batch->valid_rows }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted text-danger">Baris Gagal</span>
                                <span class="fw-bold text-danger">{{ $batch->invalid_rows }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- AUDIT LOG: PERUBAHAN DATA --}}
            <div class="col-md-8">
                <div class="card card-round border-0 shadow-sm overflow-hidden">
                    <div class="card-header bg-white border-0 pt-4 d-flex justify-content-between">
                        <h5 class="fw-bold mb-0">Log Perubahan Data (Audit)</h5>
                        <span class="badge bg-light text-dark">{{ count($batch->changes) }} Perubahan</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light sticky-top">
                                    <tr>
                                        <th class="ps-4">Entitas</th>
                                        <th>Kolom</th>
                                        <th>Data Lama</th>
                                        <th class="pe-4">Data Baru</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($batch->changes as $change)
                                    <tr>
                                        <td class="ps-4"><span class="badge bg-soft-primary text-primary">{{ strtoupper($change->entity) }}</span></td>
                                        <td class="fw-bold text-muted">{{ $change->column_name }}</td>
                                        <td><span class="text-danger decoration-strike small">{{ $change->old_value ?? '-' }}</span></td>
                                        <td class="pe-4"><span class="text-success fw-bold">{{ $change->new_value ?? '-' }}</span></td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small">Tidak ada perubahan data pada batch ini.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ROWS DETAIL --}}
            <div class="col-12">
                <div class="card card-round border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">Detail Per Baris (Raw Data)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="rowsTable" class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">No. Baris</th>
                                        <th>Mode</th>
                                        <th>Status</th>
                                        <th>Detail Data & Masalah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($batch->rows as $row)
                                    <tr class="{{ !$row->is_valid ? 'bg-soft-danger' : '' }}">
                                        <td class="ps-4 fw-bold">#{{ $row->row_number }}</td>
                                        <td>
                                            <span class="badge {{ $row->mode == 'insert' ? 'bg-success' : ($row->mode == 'update' ? 'bg-info' : 'bg-danger') }}">
                                                {{ strtoupper($row->mode) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($row->is_valid)
                                                <span class="text-success small fw-bold"><i class="fas fa-check-circle me-1"></i> VALID</span>
                                            @else
                                                <span class="text-danger small fw-bold"><i class="fas fa-exclamation-circle me-1"></i> ERROR</span>
                                            @endif
                                        </td>
                                        <td class="pe-4">
                                            @if($row->errors)
                                                <div class="alert alert-danger py-1 px-2 mb-2 small">
                                                    <strong>Masalah:</strong> {{ implode(', ', (array)$row->errors) }}
                                                </div>
                                            @endif
                                            <button class="btn btn-link btn-sm p-0 text-primary fw-bold" onclick="viewPayload({{ json_encode($row->payload) }})">
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
    .decoration-strike { text-decoration: line-through; opacity: 0.6; }
    .bg-soft-danger { background-color: rgba(220, 53, 69, 0.05); }
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
    .card-round { border-radius: 12px !important; }
    pre { white-space: pre-wrap; word-wrap: break-word; }
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