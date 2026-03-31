@extends('layouts.tenant')

@section('title', 'Riwayat Import Data')

@section('content')
<div class="container-fluid" style="background: #f8faf9; min-height: 90vh;">
    <div class="page-inner py-5">
        
        {{-- HEADER --}}
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row mb-4">
            <div>
                <h2 class="fw-extrabold text-dark mb-1">
                    <i class="fas fa-history text-success me-2"></i> Riwayat Khidmah Data
                </h2>
                <p class="text-muted fw-medium">Catatan seluruh aktivitas unggah berkas ke sistem manajemen.</p>
            </div>
            {{-- <div class="ms-md-auto py-2 py-md-0">
                <a href="{{ route('tenant.import.survey') }}" class="btn btn-success btn-round shadow-sm">
                    <i class="fas fa-plus me-2"></i> Import Baru
                </a>
            </div> --}}
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card card-round border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table id="historyTable" class="table table-hover align-middle">
                                <thead class="text-muted small text-uppercase" style="background: #fafafa;">
                                    <tr>
                                        <th class="ps-3">ID & Berkas</th>
                                        <th class="text-center">Statistik Baris</th>
                                        <th class="text-center">Status</th>
                                        <th>Pengunggah</th>
                                        <th>Waktu Transaksi</th>
                                        <th class="text-end pe-3">Opsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($batches as $batch)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3">
                                                    <span class="avatar-title rounded-3 bg-soft-success text-success fw-bold" style="font-size: 10px;">
                                                        #{{ $batch->id }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="text-dark fw-bold d-block mb-0">{{ Str::limit($batch->filename, 25) }}</span>
                                                    <small class="text-muted" style="font-size: 11px;">Excel Spreadsheet</small>
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
                                            @if($batch->status === 'completed')
                                                <span class="badge badge-success px-3 py-2 rounded-pill shadow-sm">
                                                    <i class="fas fa-check-double me-1"></i> SELESAI
                                                </span>
                                            @elseif($batch->status === 'preview')
                                                <span class="badge badge-warning px-3 py-2 rounded-pill shadow-sm text-dark">
                                                    <i class="fas fa-eye me-1"></i> PREVIEW
                                                </span>
                                            @else
                                                <span class="badge badge-secondary px-3 py-2 rounded-pill shadow-sm">
                                                    {{ strtoupper($batch->status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-circle text-muted me-2 fa-lg"></i>
                                                <span class="fw-medium text-dark">{{ $batch->uploader->name ?? 'Sistem' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted small fw-bold">
                                                <i class="far fa-clock me-1 text-success"></i> 
                                                {{ $batch->created_at->translatedFormat('d M Y, H:i') }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('tenant.import.detail', $batch->id) }}" 
                                               class="btn btn-icon btn-link btn-success btn-lg" 
                                               data-bs-toggle="tooltip" title="Lihat Rincian">
                                                <i class="fas fa-external-link-alt"></i>
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
    .bg-soft-success { background: rgba(40, 167, 69, 0.1); }
    .card-round { border-radius: 15px !important; }
    
    /* DataTable Styling */
    #historyTable { border-collapse: separate; border-spacing: 0 8px; }
    #historyTable tbody tr { 
        background-color: white !important; 
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        transition: transform 0.2s;
    }
    #historyTable tbody tr:hover { 
        transform: scale(1.005);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    #historyTable td { border: none !important; padding: 15px 10px; }
    #historyTable td:first-child { border-radius: 12px 0 0 12px; }
    #historyTable td:last-child { border-radius: 0 12px 12px 0; }

    .badge-success { background: #28a745 !important; }
    .badge-warning { background: #ffc107 !important; }
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