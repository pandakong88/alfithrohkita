@extends('layouts.tenant')

@section('content')
<div class="page-inner" style="padding-top: 15px !important;">
    {{-- HEADER --}}
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row mb-3">
        <div>
            <h3 class="fw-bold mb-1">Template Survey</h3>
            <h6 class="op-7 mb-0">Manajemen format kustom untuk import data Excel.</h6>
        </div>
        <div class="ms-md-auto py-2 py-md-0">
            <a href="{{ route('tenant.import-templates.create') }}" class="btn btn-primary btn-round btn-sm shadow-sm text-white">
                <i class="fas fa-plus-circle me-2"></i> Buat Template
            </a>
        </div>
    </div>

    @if(session('success'))
        <div id="success-trigger" data-message="{{ session('success') }}"></div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="card card-round border-0 shadow-none mt-n1">
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="templateTable" class="display table table-hover mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 45%">Nama Template</th>
                                    <th style="width: 25%">Tanggal Dibuat</th>
                                    <th class="text-center" style="width: 15%">Jumlah Kolom</th>
                                    <th class="text-end pe-3" style="width: 15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($templates as $template)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3" style="width: 32px; height: 32px;">
                                                    <span class="avatar-title rounded-circle border border-white bg-primary text-white" style="font-size: 11px;">
                                                        <i class="fas fa-file-excel"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-1">
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 13px;">{{ $template->nama_template }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted small fw-bold">
                                                <i class="far fa-calendar-alt me-1 text-muted"></i> {{ $template->created_at->format('d/m/Y') }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" 
                                                    class="badge badge-primary rounded-pill border-0 py-1.5 px-3" 
                                                    style="cursor: pointer; background-color: #e1f0ff !important; color: #1572e8 !important; font-weight: bold;"
                                                    data-bs-toggle="popover" 
                                                    data-bs-trigger="hover focus"
                                                    data-bs-placement="top"
                                                    data-bs-html="true"
                                                    title="Struktur Kolom Excel" 
                                                    data-bs-content="
                                                        <div class='d-flex flex-column gap-1 text-start'>
                                                            @foreach($template->fields as $field)
                                                                <div>
                                                                    <span class='badge bg-light text-dark border small'>
                                                                        {{ $field->pivot->order + 1 }}. {{ $field->label }} 
                                                                        @if($field->is_required) <span class='text-danger'>*</span> @endif
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    ">
                                                {{ count($template->fields ?? []) }} Kolom <i class="fas fa-info-circle ms-1" style="font-size: 10px;"></i>
                                            </button>
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="d-flex justify-content-end align-items-center gap-1">
                                                
                                                <div class="dropdown d-inline-block">
                                                    <button type="button" 
                                                            class="btn btn-icon btn-round btn-success btn-xs" 
                                                            data-bs-toggle="dropdown" 
                                                            aria-expanded="false"
                                                            title="Unduh Excel">
                                                        <i class="fa fa-download" style="font-size: 11px;"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="font-size: 13px; min-width: 240px; margin-top: 5px;">
                                                        <li>
                                                            <a class="dropdown-item py-2" href="{{ route('tenant.import-templates.download', [$template->id, 'with_data' => 'false']) }}">
                                                                <i class="fas fa-file-excel text-success me-2" style="width: 16px;"></i> 
                                                                <strong>Download Template Kosong</strong>
                                                                <small class="text-muted d-block mt-0.5">Untuk tambah santri baru massal</small>
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item py-2" href="{{ route('tenant.import-templates.download', [$template->id, 'with_data' => 'true']) }}">
                                                                <i class="fas fa-database text-primary me-2" style="width: 16px;"></i> 
                                                                <strong>Download + Data Santri</strong>
                                                                <small class="text-muted d-block mt-0.5">Untuk edit / update massal data lama</small>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                         
                                                <a href="{{ route('tenant.import-templates.show', $template->id) }}" 
                                                   class="btn btn-icon btn-round btn-info btn-xs" 
                                                   data-bs-toggle="tooltip" title="Lihat Detail">
                                                    <i class="fa fa-eye" style="font-size: 11px;"></i>
                                                </a>
                                         
                                                <a href="{{ route('tenant.import-templates.edit', $template->id) }}" 
                                                   class="btn btn-icon btn-round btn-warning btn-xs text-white" 
                                                   style="background-color: #ff9800 !important; border-color: #ff9800 !important;"
                                                   data-bs-toggle="tooltip" title="Edit Struktur Template">
                                                    <i class="fa fa-edit" style="font-size: 11px;"></i>
                                                </a>
                                         
                                                <form action="{{ route('tenant.import-templates.destroy', $template->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-icon btn-round btn-danger btn-xs btn-delete" data-bs-toggle="tooltip" title="Hapus Template">
                                                        <i class="fa fa-times" style="font-size: 11px;"></i>
                                                    </button>
                                                </form>
                                            </div>
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

<style>
    .card-round { border-radius: 12px !important; }
    .mt-n1 { margin-top: -10px !important; }
    .gap-1 { gap: 0.25rem !important; }
    
    #templateTable thead th {
        background-color: #f8fafc !important;
        color: #475569 !important;
        border-bottom: 2px solid #cbd5e1 !important;
        font-weight: 600 !important;
        text-transform: uppercase;
        font-size: 10px !important;
        letter-spacing: 0.5px;
        padding: 12px 16px !important;
    }
    #templateTable tbody td {
        padding: 14px 16px !important;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9 !important;
    }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Inisialisasi DataTables (Sudah diperbaiki dari duplikasi baris)
        var table = $('#templateTable').DataTable({
            "order": [], 
            "pageLength": 10,
            "language": {
                "search": "",
                "searchPlaceholder": "Cari template...",
                "lengthMenu": "_MENU_",
                "info": "Menampilkan _START_ - _END_ dari _TOTAL_ template",
                "paginate": {
                    "next": '<i class="fa fa-chevron-right"></i>',
                    "previous": '<i class="fa fa-chevron-left"></i>'
                }
            },
            "dom": '<"d-flex flex-wrap justify-content-between align-items-center mb-3"lf>rt<"d-flex flex-wrap justify-content-between align-items-center mt-3"ip>'
        });

        // Event Delegation untuk tombol Delete (SweetAlert)
        $('body').on('click', '.btn-delete', function(e) {
            e.preventDefault();
            
            var form = $(this).closest("form");
            
            swal({
                title: "Hapus Template?",
                text: "Template yang dihapus tidak bisa dikembalikan.",
                icon: "warning",
                buttons: ["Batal", "Ya, Hapus"], 
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) { 
                    form.submit(); 
                }
            });
        });

        // Re-init Tooltip dan Popover setelah ganti page datatable atau draw ulang
        table.on('draw', function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
            
            // Tambahkan inisialisasi popover ini
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl)
            });
        });

        // Jalankan inisialisasi popover pertama kali saat halaman di-load
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl)
        });

        // Notify Alert Success
        let msg = $('#success-trigger').data('message');
        if(msg) {
            $.notify({
                icon: 'fas fa-check-circle',
                title: 'Berhasil',
                message: msg,
            },{
                type: 'primary',
                placement: { from: "bottom", align: "right" },
                time: 1000, delay: 3000,
            });
        }
    });
</script>
@endpush