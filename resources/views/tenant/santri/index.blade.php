@extends('layouts.tenant')

@section('title', 'Database Santri')

@section('content')
@php
    // Calculate dashboard statistics from collection
    $totalSantri = $santris->count();
    $aktifCount = $santris->filter(fn($s) => in_array(strtolower($s->status), ['active', 'aktif']))->count();
    $putraCount = $santris->filter(fn($s) => strtolower($s->jenis_kelamin) === 'l')->count();
    $putriCount = $santris->filter(fn($s) => strtolower($s->jenis_kelamin) === 'p')->count();

    // Friendly status styling maps
    $statusMap = [
        'active' => ['label' => 'Aktif', 'class' => 'bg-success-soft text-success border-success-subtle'],
        'Active' => ['label' => 'Aktif', 'class' => 'bg-success-soft text-success border-success-subtle'],
        'Aktif' => ['label' => 'Aktif', 'class' => 'bg-success-soft text-success border-success-subtle'],
        'nonaktif' => ['label' => 'Non-Aktif', 'class' => 'bg-secondary-soft text-secondary border-secondary-subtle'],
        'Nonaktif' => ['label' => 'Non-Aktif', 'class' => 'bg-secondary-soft text-secondary border-secondary-subtle'],
        'lulus' => ['label' => 'Lulus', 'class' => 'bg-primary-soft text-primary border-primary-subtle'],
        'Lulus' => ['label' => 'Lulus', 'class' => 'bg-primary-soft text-primary border-primary-subtle'],
        'keluar' => ['label' => 'Keluar', 'class' => 'bg-danger-soft text-danger border-danger-subtle'],
        'Keluar' => ['label' => 'Keluar', 'class' => 'bg-danger-soft text-danger border-danger-subtle'],
        'pindah' => ['label' => 'Pindah', 'class' => 'bg-warning-soft text-warning border-warning-subtle'],
        'Pindah' => ['label' => 'Pindah', 'class' => 'bg-warning-soft text-warning border-warning-subtle'],
    ];
@endphp

{{-- HEADER SECTION --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div class="d-flex align-items-center">
        <div class="icon-avatar bg-primary-gradient text-white me-3 shadow-sm">
            <i class="fas fa-user-graduate fa-lg"></i>
        </div>
        <div>
            <h3 class="text-dark fw-bold mb-0" style="font-size: 1.6rem;">Database Santri</h3>
            <p class="text-muted mb-0 small">Manajemen profil santri, data akademik, riwayat wali murid, dan status keaktifan.</p>
        </div>
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <a href="{{ route('tenant.santri.import') }}" class="btn btn-outline-primary btn-round shadow-sm btn-sm">
            <i class="fas fa-file-import me-1.5"></i> Import Excel
        </a>
        <a href="{{ route('tenant.santri.trash') }}" class="btn btn-light btn-round border shadow-sm btn-sm">
            <i class="fas fa-trash-alt text-danger me-1.5"></i> Kotak Sampah
        </a>
        <a href="{{ route('tenant.santri.create') }}" class="btn btn-primary btn-round shadow-sm btn-sm">
            <i class="fas fa-plus-circle me-1.5"></i> Tambah Santri
        </a>
    </div>
</div>

{{-- ALERT MESSAGES --}}
@if(session('success'))
    <div id="success-trigger" data-message="{{ session('success') }}"></div>
@endif

{{-- SUMMARY STATS --}}
<div class="row g-3 mb-4">
    {{-- Total Santri --}}
    <div class="col-6 col-lg-3">
        <div class="card card-stat-custom h-100 mb-0">
            <div class="card-body p-3.5 d-flex align-items-center">
                <div class="icon-avatar bg-primary-soft text-primary me-3">
                    <i class="fas fa-users fa-lg"></i>
                </div>
                <div>
                    <span class="text-xs fw-semibold text-muted d-block">TOTAL SANTRI</span>
                    <h4 class="fw-bold mb-0 text-dark mt-0.5">{{ $totalSantri }}</h4>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Santri Keaktifan --}}
    <div class="col-6 col-lg-3">
        <div class="card card-stat-custom h-100 mb-0">
            <div class="card-body p-3.5 d-flex align-items-center">
                <div class="icon-avatar bg-success-soft text-success me-3">
                    <i class="fas fa-toggle-on fa-lg"></i>
                </div>
                <div>
                    <span class="text-xs fw-semibold text-muted d-block">SANTRI AKTIF</span>
                    <h4 class="fw-bold mb-0 text-success mt-0.5">{{ $aktifCount }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Santri Putra --}}
    <div class="col-6 col-lg-3">
        <div class="card card-stat-custom h-100 mb-0">
            <div class="card-body p-3.5 d-flex align-items-center">
                <div class="icon-avatar bg-info-soft text-info me-3">
                    <i class="fas fa-mars fa-lg"></i>
                </div>
                <div>
                    <span class="text-xs fw-semibold text-muted d-block">PUTRA (LAKI-LAKI)</span>
                    <h4 class="fw-bold mb-0 text-info mt-0.5">{{ $putraCount }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Santri Putri --}}
    <div class="col-6 col-lg-3">
        <div class="card card-stat-custom h-100 mb-0">
            <div class="card-body p-3.5 d-flex align-items-center">
                <div class="icon-avatar bg-danger-soft text-danger me-3">
                    <i class="fas fa-venus fa-lg"></i>
                </div>
                <div>
                    <span class="text-xs fw-semibold text-muted d-block">PUTRI (PEREMPUAN)</span>
                    <h4 class="fw-bold mb-0 text-danger mt-0.5">{{ $putriCount }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TABLE CARD --}}
<div class="card card-round border-0 shadow-none mt-n2">
    <div class="card-body p-3">
        <div class="table-responsive">
            <table id="santriTable" class="display table table-hover align-middle mb-0" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="text-nowrap" style="width: 10%;">NIS</th>
                        <th class="text-nowrap" style="width: 25%;">Nama Santri</th>
                        <th class="text-center text-nowrap" style="width: 10%;">Grup (JK)</th>
                        <th class="text-nowrap" style="width: 15%;">Kelas</th>
                        <th class="text-nowrap" style="width: 20%;">Kamar & Komplek</th>
                        <th class="text-nowrap" style="width: 20%;">Wali Murid</th>
                        <th class="text-center text-nowrap" style="width: 8%;">Status</th>
                        <th class="text-end text-nowrap pe-3" style="width: 12%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($santris as $santri)
                        <tr>
                            <td class="fw-bold text-primary text-nowrap">#{{ $santri->nis }}</td>
                            <td class="text-nowrap">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-3">
                                        <span class="avatar-title rounded-circle border border-white {{ $santri->jenis_kelamin == 'L' ? 'bg-primary-soft text-primary' : 'bg-danger-soft text-danger' }} fw-bold" style="font-size: 13.5px;">
                                            {{ strtoupper(substr($santri->nama_lengkap, 0, 1)) }}
                                        </span>
                                    </div>
                                    <div class="flex-1">
                                        <h6 class="fw-bold mb-0 text-dark text-nowrap" style="font-size: 13.5px;">{{ $santri->nama_lengkap }}</h6>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center text-nowrap">
                                @if($santri->jenis_kelamin == 'L')
                                    <span class="text-info fw-semibold text-xs">
                                        <i class="fas fa-mars me-1"></i> Putra
                                    </span>
                                @else
                                    <span class="text-danger fw-semibold text-xs">
                                        <i class="fas fa-venus me-1"></i> Putri
                                    </span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                @if($santri->kelas)
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-school text-muted me-2" style="font-size: 13px;"></i>
                                        <span class="fw-medium text-dark" style="font-size: 13px;">{{ $santri->kelas->nama }}</span>
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                @if($santri->kamar)
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-bed text-muted me-2" style="font-size: 13px;"></i>
                                        <span class="fw-medium text-dark" style="font-size: 13px;">
                                            {{ $santri->kamar->nama }}
                                            @if($santri->kamar->kompleks)
                                                <span class="text-muted small fw-normal">({{ $santri->kamar->kompleks->nama }})</span>
                                            @endif
                                        </span>
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                @if($santri->wali)
                                    <div class="fw-bold text-dark" style="font-size: 13px;">{{ $santri->wali->nama }}</div>
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $santri->wali->no_hp) }}" target="_blank" class="text-success fw-semibold small d-block mt-0.5">
                                        <i class="fab fa-whatsapp text-success me-1"></i>{{ $santri->wali->no_hp }}
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                @php
                                    $state = $statusMap[$santri->status] ?? ['label' => ucfirst($santri->status), 'class' => 'bg-secondary-soft text-secondary'];
                                @endphp
                                <span class="badge {{ $state['class'] }} rounded-pill px-3 py-1.5 fw-bold text-xs border" style="font-size: 10.5px;">
                                    {{ $state['label'] }}
                                </span>
                            </td>
                            <td class="text-end text-nowrap pe-0">
                                <div class="form-button-action">
                                    <a href="{{ route('tenant.santri.show', $santri) }}" 
                                       class="btn btn-link btn-info p-2" 
                                       data-bs-toggle="tooltip" 
                                       title="Lihat Detail">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="{{ route('tenant.santri.edit', $santri) }}" 
                                       class="btn btn-link btn-primary p-2" 
                                       data-bs-toggle="tooltip" 
                                       title="Edit Profil">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form action="{{ route('tenant.santri.destroy', $santri) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                class="btn btn-link btn-danger p-2 btn-delete" 
                                                data-bs-toggle="tooltip" 
                                                title="Hapus ke Sampah">
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
        var table = $('#santriTable').DataTable({
            "pageLength": 25,
            "language": {
                "search": "",
                "searchPlaceholder": "Cari NIS, Nama, Kelas...",
                "lengthMenu": "Tampilkan _MENU_",
                "info": "Menampilkan _START_ - _END_ dari _TOTAL_ santri",
                "paginate": {
                    "next": '<i class="fa fa-chevron-right"></i>',
                    "previous": '<i class="fa fa-chevron-left"></i>'
                }
            },
            "dom": '<"d-flex flex-wrap justify-content-between align-items-center mb-3"lf>rt<"d-flex flex-wrap justify-content-between align-items-center mt-3"ip>'
        });

        $('body').on('click', '.btn-delete', function(e) {
            e.preventDefault();
            var form = $(this).closest("form");
            
            swal({
                title: "Hapus Santri?",
                text: "Data santri ini akan dipindahkan ke kotak sampah sementara.",
                icon: "warning",
                buttons: {
                    cancel: { 
                        visible: true, 
                        text: "Batal", 
                        className: 'btn btn-focus btn-round' 
                    },
                    confirm: { 
                        text: "Ya, Pindahkan", 
                        className: 'btn btn-danger btn-round' 
                    }
                }
            }).then((willDelete) => {
                if (willDelete) {
                    form.submit();
                }
            });
        });

        table.on('draw', function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });

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