@extends('layouts.tenant')

@section('title', 'Verifikasi Data Import')

@section('content')
<div class="container-fluid" style="background: #f4f7f6; min-height: 90vh;">
    <div class="page-inner py-5">
        
        {{-- HEADER & STATS --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h2 class="fw-extrabold text-dark mb-1"><i class="fas fa-microscope text-success me-2"></i> Analisa Validasi Data</h2>
                <p class="text-muted fw-medium">Mohon periksa kembali data sebelum melakukan proses <strong>Commit</strong>.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('tenant.import-templates.index') }}" class="btn btn-white btn-round shadow-sm border">
                    <i class="fas fa-times me-2"></i> Batalkan
                </a>
                @if($batch->status === 'preview' && $batch->valid_rows > 0)
                <form method="POST" action="{{ route('tenant.import.commit', $batch->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-round px-4 shadow-lg shadow-success-light">
                        <i class="fas fa-cloud-upload-alt me-2"></i> Simpan ke Database
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- INFO CARDS --}}
        <div class="row mb-4">
            <div class="col-6 col-md-3">
                <div class="card card-stats card-round border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-primary bubble-shadow-small"><i class="fas fa-list"></i></div>
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
            <div class="col-6 col-md-3">
                <div class="card card-stats card-round border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-check-circle"></i></div>
                            </div>
                            <div class="col col-stats ms-3 ms-sm-0">
                                <div class="numbers">
                                    <p class="card-category">Siap Simpan</p>
                                    <h4 class="card-title">{{ $batch->valid_rows }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-stats card-round border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-danger bubble-shadow-small"><i class="fas fa-exclamation-triangle"></i></div>
                            </div>
                            <div class="col col-stats ms-3 ms-sm-0">
                                <div class="numbers">
                                    <p class="card-category">Bermasalah</p>
                                    <h4 class="card-title text-danger">{{ $batch->invalid_rows }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-stats card-round border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-secondary bubble-shadow-small"><i class="fas fa-hourglass-half"></i></div>
                            </div>
                            <div class="col col-stats ms-3 ms-sm-0">
                                <div class="numbers">
                                    <p class="card-category">Status</p>
                                    <h4 class="card-title">{{ strtoupper($batch->status) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MAIN TABLE CARD --}}
        <div class="card card-round border-0 shadow-sm mt-2">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="previewTable" class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-dark fw-bold">
                            <tr>
                                <th class="ps-4" style="width: 80px;">Baris</th>
                                <th style="width: 150px;">Aksi Sistem</th>
                                <th class="text-center" style="width: 100px;">Validitas</th>
                                <th>Data (Payload)</th>
                                <th class="pe-4">Pesan Masalah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batch->rows as $row)
                            <tr class="{{ !$row->is_valid ? 'bg-soft-danger' : '' }}">
                                <td class="ps-4 fw-bold text-muted">#{{ $row->row_number }}</td>
                                <td>
                                    @if($row->mode === 'insert')
                                        <span class="badge bg-success text-white px-3"><i class="fas fa-plus-circle me-1"></i> BARU</span>
                                    @elseif($row->mode === 'update')
                                        <span class="badge bg-info text-white px-3"><i class="fas fa-edit me-1"></i> UPDATE</span>
                                    @else
                                        <span class="badge bg-danger text-white px-3"><i class="fas fa-times-circle me-1"></i> ERROR</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($row->is_valid)
                                        <div class="icon-status text-success bg-soft-success mx-auto rounded-circle"><i class="fas fa-check"></i></div>
                                    @else
                                        <div class="icon-status text-danger bg-soft-danger mx-auto rounded-circle"><i class="fas fa-exclamation"></i></div>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-round" 
                                            onclick="showPayload({{ json_encode($row->payload) }})">
                                        <i class="fas fa-search-plus me-1"></i> Intip Data
                                    </button>
                                </td>
                                <td class="pe-4">
                                    @if($row->errors)
                                        <span class="text-danger small fw-bold">
                                            <i class="fas fa-info-circle me-1"></i> 
                                            {{ implode(', ', (array)$row->errors) }}
                                        </span>
                                    @else
                                        <span class="text-muted small italic">Sesuai Syariat (Valid)</span>
                                    @endif
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

{{-- MODAL UNTUK PREVIEW PAYLOAD --}}
<div class="modal fade" id="payloadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-round border-0">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="fw-bold">Rincian Data Baris</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="payloadContainer" class="bg-light p-3 rounded-4 border">
                    {{-- JSON CONTENT HERE --}}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .fw-extrabold { font-weight: 800; }
    .bg-soft-danger { background-color: rgba(242, 89, 97, 0.05) !important; }
    .bg-soft-success { background-color: rgba(49, 206, 54, 0.1) !important; }
    .icon-status { width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-size: 14px; }
    .rounded-4 { border-radius: 12px; }
    .card-round { border-radius: 15px !important; }
    .shadow-success-light { box-shadow: 0 10px 20px rgba(40, 167, 69, 0.2) !important; }
    
    /* DataTables Custom UI */
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #1572e8 !important; color: white !important; border: none; border-radius: 8px;
    }
    #previewTable thead th { border-top: none; padding: 15px 10px; }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#previewTable').DataTable({
            "pageLength": 10,
            "ordering": false, // Agar urutan baris excel tetap terjaga
            "language": {
                "search": "",
                "searchPlaceholder": "Cari data hasil analisa...",
                "lengthMenu": "_MENU_",
                "info": "Menampilkan _START_ - _END_ dari _TOTAL_ baris",
                "paginate": {
                    "next": '<i class="fa fa-chevron-right"></i>',
                    "previous": '<i class="fa fa-chevron-left"></i>'
                }
            },
            "dom": '<"d-flex justify-content-between align-items-center p-3"lf>rt<"d-flex justify-content-between align-items-center p-3"ip>'
        });
    });

    function showPayload(data) {
        let content = '<ul class="list-group list-group-flush bg-transparent">';
        for (let key in data) {
            content += `<li class="list-group-item bg-transparent px-0 py-1 d-flex justify-content-between">
                            <span class="text-muted small text-uppercase fw-bold">${key.replace('_', ' ')}</span>
                            <span class="text-dark fw-bold">${data[key] || '-'}</span>
                        </li>`;
        }
        content += '</ul>';
        $('#payloadContainer').html(content);
        $('#payloadModal').modal('show');
    }
</script>
@endpush