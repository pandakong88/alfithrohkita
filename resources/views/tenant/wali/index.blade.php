@extends('layouts.tenant')

@section('content')
<div class="page-inner" style="padding-top: 15px !important;"> 
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row mb-3"> 
        <div>
            <h3 class="fw-bold mb-1">Manajemen Wali Murid</h3>
            <h6 class="op-7 mb-0">Pusat data informasi orang tua dan wali santri.</h6>
        </div>
        <div class="ms-md-auto py-2 py-md-0">
            <a href="{{ route('tenant.wali.trash') }}" class="btn btn-label-danger btn-round btn-sm me-2">
                <i class="fas fa-trash-alt me-2"></i> Sampah
            </a>
            <a href="{{ route('tenant.wali.create') }}" class="btn btn-primary btn-round btn-sm shadow-sm">
                <i class="fas fa-plus-circle me-2"></i> Registrasi Wali
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
                        <table id="datatable-wali" class="display table table-head-bg-primary table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 30%">Wali</th>
                                    <th>Kontak</th>
                                    <th>Pekerjaan</th>
                                    <th class="text-center">Koneksi Santri</th>
                                    <th class="text-end pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($walis as $wali)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3">
                                                    <span class="avatar-title rounded-circle border border-white bg-primary" style="font-size: 12px;">
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
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $wali->no_hp) }}" target="_blank" class="text-success fw-bold" style="font-size: 11px;">
                                                <i class="fab fa-whatsapp"></i> WhatsApp
                                            </a>
                                        </td>
                                        <td>
                                            <span class="text-muted fw-bold" style="font-size: 12px;">
                                                <i class="fas fa-briefcase me-1 opacity-50"></i> {{ $wali->pekerjaan ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="badge badge-count bg-light text-primary border-primary border">
                                                {{ $wali->santris_count ?? $wali->santris()->count() }} Santri
                                            </div>
                                        </td>
                                        <td class="text-end pe-0">
                                            <div class="form-button-action">
                                                <a href="{{ route('tenant.wali.edit', $wali) }}" 
                                                   class="btn btn-link btn-primary btn-lg p-2" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Ubah Data">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <form action="{{ route('tenant.wali.destroy', $wali) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" 
                                                            class="btn btn-link btn-danger btn-lg p-2 btn-delete" 
                                                            data-bs-toggle="tooltip" 
                                                            title="Buang ke Sampah">
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
    .mt-n1 { margin-top: -10px !important; }
    
    .table-head-bg-primary thead th {
        background: #1572e8 !important;
        padding: 12px 15px !important;
        font-size: 10px !important;
    }

    .card-round { border-radius: 12px !important; }
    
    .badge-count {
        border-radius: 20px;
        padding: 4px 10px;
        font-weight: 700;
        font-size: 10px;
    }

    .dataTables_wrapper .dataTables_filter input {
        border-radius: 8px !important;
        padding: 5px 12px !important;
        height: 35px;
    }

    /* CSS Tambahan biar notif kanan bawah ga mepet banget */
    .bootstrap-notify-container {
        padding: 15px 20px !important;
        border-radius: 10px !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
    }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#datatable-wali').DataTable({
            "pageLength": 10,
            "order": [[0, "asc"]],
            "language": {
                "search": "",
                "searchPlaceholder": "Cari data wali...",
                "lengthMenu": "_MENU_",
                "info": "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                "paginate": {
                    "next": '<i class="fa fa-chevron-right"></i>',
                    "previous": '<i class="fa fa-chevron-left"></i>'
                }
            },
            "dom": '<"d-flex flex-wrap justify-content-between align-items-center mb-3"lf>rt<"d-flex flex-wrap justify-content-between align-items-center mt-3"ip>'
        });

        $(document).on('click', '.btn-delete', function() {
            var form = $(this).closest("form");
            swal({
                title: "Hapus Data?",
                text: "Data akan dipindahkan ke kotak sampah.",
                icon: "warning",
                buttons: {
                    cancel: { visible: true, text: "Batal", className: 'btn btn-focus' },
                    confirm: { text: "Ya, Hapus", className: 'btn btn-danger' }
                }
            }).then((willDelete) => {
                if (willDelete) {
                    form.submit();
                }
            });
        });

        // Notifikasi Pojok Kanan Bawah
        let msg = $('#success-trigger').data('message');
        if(msg) {
            $.notify({
                icon: 'fas fa-check-circle',
                title: 'Berhasil',
                message: msg,
            },{
                type: 'success',
                placement: {
                    from: "bottom", 
                    align: "right"  
                },
                time: 1000,
                delay: 3000,
                animate: {
                    enter: 'animated fadeInUp', 
                    exit: 'animated fadeOutDown'
                },
                offset: {
                    x: 20,
                    y: 20
                }
            });
        }

        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
@endpush