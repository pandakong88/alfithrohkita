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
                        {{-- ID Tabel disamakan dengan script di bawah --}}
                        <table id="templateTable" class="display table table-head-bg-primary table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 40%">Nama Template</th>
                                    <th style="width: 25%">Tanggal Dibuat</th>
                                    <th class="text-center">Field</th>
                                    <th class="text-end pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($templates as $template)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3">
                                                    <span class="avatar-title rounded-circle border border-white bg-primary">
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
                                                <i class="far fa-calendar-alt me-1"></i> {{ $template->created_at->format('d/m/Y') }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-primary rounded-pill">
                                                {{ count($template->fields ?? []) }} Kolom
                                            </span>
                                        </td>
                                        <td class="text-end pe-0">
                                            <div class="form-button-action">
                                                <a href="{{ route('tenant.import-templates.download', $template->id) }}" 
                                                   class="btn btn-link btn-success btn-lg p-2" 
                                                   data-bs-toggle="tooltip" title="Download Excel">
                                                    <i class="fa fa-download"></i>
                                                </a>
                                                <a href="{{ route('tenant.import-templates.show', $template->id) }}" 
                                                   class="btn btn-link btn-info btn-lg p-2" 
                                                   data-bs-toggle="tooltip" title="Lihat Detail">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <form action="{{ route('tenant.import-templates.destroy', $template->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-link btn-danger btn-lg p-2 btn-delete" data-bs-toggle="tooltip" title="Hapus Template">
                                                        <i class="fa fa-times"></i>
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
    /* Copas Style dari Santri biar Konsisten */
    .form-button-action { display: flex !important; justify-content: flex-end; gap: 2px; }
    .form-button-action i { font-size: 16px !important; }
    .card-round { border-radius: 12px !important; }
    .mt-n1 { margin-top: -10px !important; }
    .table-head-bg-primary thead th {
        background: #1572e8 !important;
        color: #fff !important;
        padding: 12px 15px !important;
        font-size: 10px !important;
        text-transform: uppercase;
    }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Inisialisasi DataTables (Samakan dengan Logic Santri)
        var table = $('#templateTable').DataTable({
            var table = $('#templateTable').DataTable({
            "order": [], // Tambahkan ini: Mematikan auto-sort saat load pertama kali
            "pageLength": 10,
            "language": {
                // ... (sisanya tetap sama)
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
                buttons: {
                    cancel: { visible: true, text: "Batal", className: 'btn btn-focus' },
                    confirm: { text: "Ya, Hapus", className: 'btn btn-danger' }
                }
            }).then((willDelete) => {
                if (willDelete) { form.submit(); }
            });
        });

        // Re-init Tooltip setelah ganti page datatable
        table.on('draw', function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });

        // Notify
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