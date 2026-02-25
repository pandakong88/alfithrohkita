@extends('layouts.tenant')

@section('content')
<div class="page-inner" style="padding-top: 15px !important;">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row mb-3">
        <div>
            <h3 class="fw-bold mb-1 text-danger"><i class="fas fa-trash-alt me-2"></i>Kotak Sampah Wali</h3>
            <h6 class="op-7 mb-0">Data yang dihapus sementara dapat dipulihkan di sini.</h6>
        </div>
        <div class="ms-md-auto py-2 py-md-0">
            <a href="{{ route('tenant.wali.index') }}" class="btn btn-outline-primary btn-round btn-sm shadow-sm">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-round border-0 shadow-none mt-n1">
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="datatable-trash" class="display table table-head-bg-danger table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 40%">Nama Wali</th>
                                    <th>No HP</th>
                                    <th>Dihapus Pada</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($walis as $wali)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3">
                                                    <span class="avatar-title rounded-circle border border-white bg-danger opacity-75" style="font-size: 12px;">
                                                        {{ strtoupper(substr($wali->nama, 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div class="flex-1">
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 13px;">{{ $wali->nama }}</h6>
                                                    <small class="text-muted" style="font-size: 10px;">ID: #WAL-{{ $wali->id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark" style="font-size: 13px;">{{ $wali->no_hp }}</div>
                                        </td>
                                        <td>
                                            <span class="text-muted small">
                                                {{ $wali->deleted_at->format('d M Y, H:i') }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-0">
                                            <form action="{{ route('tenant.wali.restore', $wali->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="button" class="btn btn-link btn-success btn-lg p-2 btn-restore" 
                                                        data-bs-toggle="tooltip" title="Pulihkan Data">
                                                    <i class="fas fa-undo-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .mt-n1 { margin-top: -10px !important; }
    .table-head-bg-danger thead th {
        background: #f25961 !important; /* Warna merah soft */
        color: #fff !important;
        padding: 12px 15px !important;
        font-size: 10px !important;
        border: none !important;
    }
    .card-round { border-radius: 12px !important; }
    
    /* Overrides DataTables */
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 8px !important;
        height: 35px;
    }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#datatable-trash').DataTable({
            "pageLength": 10,
            "language": {
                "search": "",
                "searchPlaceholder": "Cari di sampah...",
                "emptyTable": "Tidak ada data di kotak sampah",
                "lengthMenu": "_MENU_",
                "info": "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                "paginate": {
                    "next": '<i class="fa fa-chevron-right"></i>',
                    "previous": '<i class="fa fa-chevron-left"></i>'
                }
            },
            "dom": '<"d-flex flex-wrap justify-content-between align-items-center mb-3"lf>rt<"d-flex flex-wrap justify-content-between align-items-center mt-3"ip>'
        });

        // Konfirmasi Restore
        $(document).on('click', '.btn-restore', function() {
            var form = $(this).closest("form");
            swal({
                title: "Pulihkan data?",
                text: "Data wali akan kembali aktif di daftar utama.",
                icon: "info",
                buttons: {
                    cancel: { visible: true, text: "Batal", className: 'btn btn-focus' },
                    confirm: { text: "Ya, Pulihkan", className: 'btn btn-success' }
                }
            }).then((willRestore) => {
                if (willRestore) {
                    form.submit();
                }
            });
        });

        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
@endpush