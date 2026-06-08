@extends('layouts.tenant')

@section('title', 'Manajemen Wali Murid')

@section('content')
@php
    // Calculate dashboard statistics from collection
    $totalWali = $walis->count();
    $waliAktif = $walis->filter(fn($w) => ($w->santris_count ?? $w->santris->count()) > 0)->count();
    $waliMandiri = $walis->filter(fn($w) => ($w->santris_count ?? $w->santris->count()) == 0)->count();
    $jobsCount = $walis->whereNotNull('pekerjaan')->filter(fn($w) => trim($w->pekerjaan) !== '')->unique('pekerjaan')->count();
@endphp

{{-- HEADER SECTION --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div class="d-flex align-items-center">
        <div class="icon-avatar bg-primary-gradient text-white me-3 shadow-sm">
            <i class="fas fa-user-shield fa-lg"></i>
        </div>
        <div>
            <h3 class="text-dark fw-bold mb-0" style="font-size: 1.6rem;">Manajemen Wali Murid</h3>
            <p class="text-muted mb-0 small">Pusat data informasi orang tua, kontak WhatsApp, dan hubungan santri.</p>
        </div>
    </div>
    @can('manage_wali')
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <a href="{{ route('tenant.wali.import.history') }}" class="btn btn-light btn-round border shadow-sm btn-sm">
            <i class="fas fa-history text-info me-1.5"></i> Riwayat Import
        </a>
        <a href="{{ route('tenant.wali.import') }}" class="btn btn-success btn-round shadow-sm btn-sm text-white">
            <i class="fas fa-file-excel me-1.5"></i> Import Excel
        </a>
        <a href="{{ route('tenant.wali.trash') }}" class="btn btn-light btn-round border shadow-sm btn-sm">
            <i class="fas fa-trash-alt text-danger me-1.5"></i> Kotak Sampah
        </a>
        <a href="{{ route('tenant.wali.create') }}" class="btn btn-primary btn-round shadow-sm btn-sm">
            <i class="fas fa-plus-circle me-1.5"></i> Registrasi Wali
        </a>
    </div>
    @endcan
</div>

{{-- ALERT MESSAGES --}}
@if(session('success'))
    <div id="success-trigger" data-message="{{ session('success') }}"></div>
@endif

{{-- SUMMARY STATS --}}
<div class="row g-3 mb-4">
    {{-- Total Wali --}}
    <div class="col-6 col-lg-3">
        <div class="card card-stat-custom h-100 mb-0">
            <div class="card-body p-3.5 d-flex align-items-center">
                <div class="icon-avatar bg-primary-soft text-primary me-3">
                    <i class="fas fa-user-shield fa-lg"></i>
                </div>
                <div>
                    <span class="text-xs fw-semibold text-muted d-block">TOTAL WALI</span>
                    <h4 class="fw-bold mb-0 text-dark mt-0.5">{{ $totalWali }}</h4>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Wali Aktif --}}
    <div class="col-6 col-lg-3">
        <div class="card card-stat-custom h-100 mb-0">
            <div class="card-body p-3.5 d-flex align-items-center">
                <div class="icon-avatar bg-success-soft text-success me-3">
                    <i class="fas fa-link fa-lg"></i>
                </div>
                <div>
                    <span class="text-xs fw-semibold text-muted d-block">WALI TERHUBUNG</span>
                    <h4 class="fw-bold mb-0 text-success mt-0.5">{{ $waliAktif }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Wali Mandiri --}}
    <div class="col-6 col-lg-3">
        <div class="card card-stat-custom h-100 mb-0">
            <div class="card-body p-3.5 d-flex align-items-center">
                <div class="icon-avatar bg-warning-soft text-warning me-3">
                    <i class="fas fa-user-slash fa-lg"></i>
                </div>
                <div>
                    <span class="text-xs fw-semibold text-muted d-block">BELUM TERHUBUNG</span>
                    <h4 class="fw-bold mb-0 text-warning mt-0.5">{{ $waliMandiri }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Ragam Pekerjaan --}}
    <div class="col-6 col-lg-3">
        <div class="card card-stat-custom h-100 mb-0">
            <div class="card-body p-3.5 d-flex align-items-center">
                <div class="icon-avatar bg-info-soft text-info me-3">
                    <i class="fas fa-briefcase fa-lg"></i>
                </div>
                <div>
                    <span class="text-xs fw-semibold text-muted d-block">RAGAM PEKERJAAN</span>
                    <h4 class="fw-bold mb-0 text-info mt-0.5">{{ $jobsCount }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TABLE CARD --}}
<div class="card card-round border-0 shadow-none mt-n2">
    <div class="card-body p-3">
        <div class="table-responsive">
            <table id="datatable-wali" class="display table table-hover align-middle mb-0" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="text-nowrap" style="width: 30%;">Wali</th>
                        <th class="text-nowrap" style="width: 25%;">Kontak WhatsApp</th>
                        <th class="text-nowrap" style="width: 20%;">Pekerjaan</th>
                        <th class="text-center text-nowrap" style="width: 15%;">Koneksi Santri</th>
                        <th class="text-end text-nowrap pe-3" style="width: 10%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($walis as $wali)
                        <tr>
                            <td class="text-nowrap">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-3">
                                        <span class="avatar-title rounded-circle border border-white bg-primary text-white fw-bold" style="font-size: 12px;">
                                            {{ strtoupper(substr($wali->nama, 0, 1)) }}
                                        </span>
                                    </div>
                                    <div class="flex-1">
                                        <h6 class="fw-bold mb-0 text-dark text-nowrap" style="font-size: 13px;">{{ $wali->nama }}</h6>
                                        <small class="text-muted" style="font-size: 10px;">ID: #WAL-{{ $wali->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-nowrap">
                                <div class="fw-bold text-dark" style="font-size: 13px;">{{ $wali->no_hp }}</div>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $wali->no_hp) }}" target="_blank" class="text-success fw-semibold small d-block mt-0.5">
                                    <i class="fab fa-whatsapp text-success me-1"></i> WhatsApp
                                </a>
                            </td>
                            <td class="text-nowrap">
                                <span class="text-dark fw-medium" style="font-size: 13px;">
                                    <i class="fas fa-briefcase text-muted me-1.5 opacity-75" style="font-size: 12px;"></i> {{ $wali->pekerjaan ?? '-' }}
                                </span>
                            </td>
                            <td class="text-center text-nowrap">
                                <div class="badge badge-count bg-light text-primary border-primary border">
                                    {{ $wali->santris_count ?? $wali->santris->count() }} Santri
                                </div>
                            </td>
                            <td class="text-end text-nowrap pe-0">
                                <div class="form-button-action">
                                    <a href="{{ route('tenant.wali.show', $wali) }}" 
                                       class="btn btn-link btn-info p-2" 
                                       data-bs-toggle="tooltip" 
                                       title="Lihat Detail">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    @can('manage_wali')
                                    <a href="{{ route('tenant.wali.edit', $wali) }}" 
                                       class="btn btn-link btn-primary p-2" 
                                       data-bs-toggle="tooltip" 
                                       title="Ubah Data">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form action="{{ route('tenant.wali.destroy', $wali) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                class="btn btn-link btn-danger p-2 btn-delete" 
                                                data-bs-toggle="tooltip" 
                                                title="Buang ke Sampah">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    /* Icon Avatar Styles */
    .icon-avatar {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .bg-primary-gradient {
        background: linear-gradient(135deg, #1572e8 0%, #064095 100%) !important;
    }
    
    .card-stat-custom {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.02) !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: #ffffff;
    }
    
    .card-stat-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05) !important;
    }

    /* Soft colors */
    .bg-success-soft {
        background-color: #ecfdf5 !important;
        color: #059669 !important;
        border-color: #a7f3d0 !important;
    }

    .bg-danger-soft {
        background-color: #fef2f2 !important;
        color: #dc2626 !important;
        border-color: #fecaca !important;
    }
    
    .bg-primary-soft {
        background-color: #eff6ff !important;
        color: #2563eb !important;
        border-color: #bfdbfe !important;
    }
    
    .bg-warning-soft {
        background-color: #fffbeb !important;
        color: #d97706 !important;
        border-color: #fde68a !important;
    }

    .bg-info-soft {
        background-color: #eff6ff !important;
        color: #0284c7 !important;
        border-color: #bae6fd !important;
    }

    .bg-secondary-soft {
        background-color: #f8fafc !important;
        color: #64748b !important;
        border-color: #e2e8f0 !important;
    }

    .text-slate { color: #64748b; }
    .text-xs { font-size: 0.75rem; }
    .text-sm { font-size: 0.875rem; }
    .btn-round { border-radius: 50px; }
    .avatar-sm { width: 34px; height: 34px; }
    .avatar-title { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; }
    
    .mt-n2 { margin-top: -15px !important; }
    
    .badge-count {
        border-radius: 20px;
        padding: 4px 10px;
        font-weight: 700;
        font-size: 10px;
    }
    
    /* DataTable customization overrides */
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #cbd5e1 !important;
        border-radius: 20px !important;
        padding: 6px 16px !important;
        font-size: 13px !important;
    }
    
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        padding: 4px 8px !important;
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
                "lengthMenu": "Tampilkan _MENU_",
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
                    cancel: { visible: true, text: "Batal", className: 'btn btn-focus btn-round' },
                    confirm: { text: "Ya, Hapus", className: 'btn btn-danger btn-round' }
                }
            }).then((willDelete) => {
                if (willDelete) {
                    form.submit();
                }
            });
        });

        let msg = $('#success-trigger').data('message');
        if(msg) {
            $.notify({
                icon: 'fas fa-check-circle',
                title: 'Berhasil',
                message: msg,
            },{
                type: 'success',
                placement: { from: "bottom", align: "right" },
                time: 1000,
                delay: 3000,
                animate: { enter: 'animated fadeInUp', exit: 'animated fadeOutDown' }
            });
        }

        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
@endpush