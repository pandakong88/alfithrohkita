@extends('layouts.tenant')

@section('title', 'Tong Sampah Santri')

@section('content')
<div class="container" style="min-height: 90vh;">
    <div class="page-inner py-4">
        
        {{-- BREADCRUMB --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style-1 mb-0" style="background: transparent; padding: 0;">
                <li class="breadcrumb-item"><a href="{{ route('tenant.santri.index') }}">Database Santri</a></li>
                <li class="breadcrumb-item active" aria-current="page">Kotak Sampah</li>
            </ol>
        </nav>

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div class="d-flex align-items-center">
                <div class="icon-avatar bg-danger-gradient text-white me-3 shadow-sm">
                    <i class="fas fa-trash-alt fa-lg"></i>
                </div>
                <div>
                    <h3 class="text-dark fw-bold mb-0" style="font-size: 1.6rem;">Kotak Sampah Santri</h3>
                    <p class="text-muted mb-0 small">Kumpulan data profil santri yang dihapus sementara dari sistem.</p>
                </div>
            </div>
            <a href="{{ route('tenant.santri.index') }}" class="btn btn-light btn-round border shadow-sm btn-sm">
                <i class="fas fa-arrow-left me-1.5"></i> Kembali ke Daftar Aktif
            </a>
        </div>

        {{-- Alert Info Banner --}}
        <div class="alert alert-info border-0 shadow-sm p-4 mb-4 d-flex align-items-start gap-3 rounded-4" style="background-color: #eff6ff; border-left: 5px solid #2563eb !important;">
            <div class="icon-avatar bg-primary-soft text-primary shadow-xs mt-0.5" style="width: 40px; height: 40px;">
                <i class="fas fa-info-circle fa-md"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="fw-bold text-dark mb-1">Panduan Pengelolaan Sampah</h6>
                <p class="text-muted text-sm mb-0">Data santri di halaman ini tidak terhapus sepenuhnya. Anda dapat memilih <strong>Pulihkan</strong> untuk mengembalikannya ke database aktif, atau memilih <strong>Hapus Permanen</strong> untuk menghapus data tersebut secara permanen dari server selamanya.</p>
            </div>
        </div>

        {{-- ALERT MESSAGES --}}
        @if(session('success'))
            <div id="success-trigger" data-message="{{ session('success') }}"></div>
        @endif

        {{-- TABLE CARD --}}
        <div class="card card-custom">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="trashTable" class="table table-custom align-middle mb-0" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 15%;">NIS</th>
                                <th style="width: 45%;">Nama Lengkap</th>
                                <th style="width: 20%;">Dihapus Pada</th>
                                <th class="text-center" style="width: 20%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($santris as $santri)
                                <tr>
                                    <td class="fw-bold text-danger">#{{ $santri->nis }}</td>
                                    <td>
                                        <div class="fw-bold text-dark" style="font-size: 13.5px;">{{ $santri->nama_lengkap }}</div>
                                        <small class="text-muted">{{ $santri->jenis_kelamin == 'L' ? 'Laki-laki (Putra)' : 'Perempuan (Putri)' }}</small>
                                    </td>
                                    <td>
                                        <span class="text-slate small fw-semibold">
                                            <i class="far fa-clock me-1"></i> {{ $santri->deleted_at->translatedFormat('d M Y, H:i') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-success btn-round px-3 py-1.5" 
                                                    onclick="handleRestore('{{ $santri->id }}', '{{ $santri->nama_lengkap }}')" style="font-size: 11.5px; font-weight: 600;">
                                                <i class="fas fa-undo me-1"></i> Pulihkan
                                            </button>

                                            <button type="button" class="btn btn-sm btn-outline-danger btn-round px-3 py-1.5" 
                                                    onclick="handleForceDelete('{{ $santri->id }}', '{{ $santri->nama_lengkap }}')" style="font-size: 11.5px; font-weight: 600;">
                                                <i class="fas fa-times me-1"></i> Hapus Permanen
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="fas fa-trash-restore fa-3x mb-3 opacity-25"></i>
                                        <p class="mb-0">Tidak ada data santri di dalam kotak sampah.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- DYNAMIC ACTION FORM --}}
<form id="trash-action-form" method="POST" action="" style="display:none;">
    @csrf
    <input type="hidden" name="_method" id="trash-action-method" value="PATCH">
</form>

<style>
    /* CSS Utility Variables */
    .page-inner {
        padding-top: 15px !important;
    }
    
    /* Icon Avatar Styles */
    .icon-avatar {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .bg-danger-gradient {
        background: linear-gradient(135deg, #f25961 0%, #c62828 100%) !important;
    }
    
    /* Card design */
    .card-custom {
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03), 0 1px 3px rgba(0, 0, 0, 0.02) !important;
        background: #ffffff;
        overflow: hidden;
    }

    /* Soft colors */
    .bg-primary-soft {
        background-color: #eff6ff !important;
        color: #2563eb !important;
        border-color: #bfdbfe !important;
    }

    .bg-danger-soft {
        background-color: #fef2f2 !important;
        color: #dc2626 !important;
        border-color: #fecaca !important;
    }

    /* Table styles */
    .table-custom {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .table-custom thead th {
        background-color: #f8fafc;
        color: #475569;
        font-weight: 600;
        font-size: 11px;
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .table-custom tbody td {
        padding: 14px 20px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13.5px;
    }

    .table-custom tbody tr:hover td {
        background-color: #f8fafc;
    }
    
    .text-slate { color: #64748b; }
    .btn-round { border-radius: 50px; }
</style>
@endsection

@push('scripts')
<script>
    // SweetAlert restore trigger
    function handleRestore(id, name) {
        swal({
            title: "Pulihkan Santri?",
            text: "Kembalikan data " + name + " ke daftar santri aktif.",
            icon: "info",
            buttons: {
                cancel: { visible: true, text: "Batal", className: 'btn btn-focus btn-round' },
                confirm: { text: "Ya, Pulihkan", className: 'btn btn-success btn-round' }
            }
        }).then((willRestore) => {
            if (willRestore) {
                const form = $('#trash-action-form');
                $('#trash-action-method').val('PATCH');
                form.attr('action', "{{ url('dashboard/santri') }}/" + id + "/restore");
                form.submit();
            }
        });
    }

    // SweetAlert permanent delete trigger
    function handleForceDelete(id, name) {
        swal({
            title: "Hapus Permanen?",
            text: "Peringatan! Data santri " + name + " akan dihapus selamanya dari sistem.",
            icon: "warning",
            buttons: {
                cancel: { visible: true, text: "Batal", className: 'btn btn-focus btn-round' },
                confirm: { text: "Ya, Hapus Permanen", className: 'btn btn-danger btn-round' }
            }
        }).then((willDelete) => {
            if (willDelete) {
                const form = $('#trash-action-form');
                $('#trash-action-method').val('DELETE');
                form.attr('action', "{{ url('dashboard/santri') }}/" + id + "/force-delete");
                form.submit();
            }
        });
    }

    $(document).ready(function() {
        // Initialize simple datatable on trash
        var table = $('#trashTable').DataTable({
            "pageLength": 15,
            "language": {
                "search": "",
                "searchPlaceholder": "Cari nama / NIS...",
                "lengthMenu": "Tampilkan _MENU_",
                "info": "Menampilkan _START_ - _END_ dari _TOTAL_ santri",
                "paginate": {
                    "next": '<i class="fa fa-chevron-right"></i>',
                    "previous": '<i class="fa fa-chevron-left"></i>'
                }
            },
            "dom": '<"d-flex flex-wrap justify-content-between align-items-center mb-3"lf>rt<"d-flex flex-wrap justify-content-between align-items-center mt-3"ip>'
        });

        // Fire bottom right notification on success
        let msg = $('#success-trigger').data('message');
        if(msg) {
            $.notify({
                icon: 'fas fa-check-circle',
                title: 'Berhasil',
                message: msg,
            },{
                type: 'info',
                placement: { from: "bottom", align: "right" },
                time: 1000,
                delay: 3000,
                animate: { enter: 'animated fadeInUp', exit: 'animated fadeOutDown' }
            });
        }
    });
</script>
@endpush