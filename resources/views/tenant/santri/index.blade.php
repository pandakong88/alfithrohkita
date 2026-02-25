@extends('layouts.tenant')

@section('content')
<div class="page-inner" style="padding-top: 15px !important;">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row mb-3">
        <div>
            <h3 class="fw-bold mb-1">Database Santri</h3>
            <h6 class="op-7 mb-0">Manajemen data santri, NIS, dan status akademik.</h6>
        </div>
        <div class="ms-md-auto py-2 py-md-0">
            <a href="{{ route('tenant.santri.trash') }}" class="btn btn-label-info btn-round btn-sm me-2">
                <i class="fas fa-trash-alt me-2"></i> Kotak Sampah
            </a>
            <a href="{{ route('tenant.santri.create') }}" class="btn btn-info btn-round btn-sm shadow-sm text-white">
                <i class="fas fa-plus-circle me-2"></i> Tambah Santri
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
                        <table id="santriTable" class="display table table-head-bg-info table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 10%">NIS</th>
                                    <th style="width: 25%">Nama Santri</th>
                                    <th class="text-center">JK</th>
                                    <th>Wali Murid</th>
                                    <th class="text-center">Status</th>
                                    <th>Tgl Masuk</th>
                                    <th class="text-end pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($santris as $santri)
                                    <tr>
                                        <td class="fw-bold text-info">#{{ $santri->nis }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3">
                                                    <span class="avatar-title rounded-circle border border-white bg-info">
                                                        {{ strtoupper(substr($santri->nama_lengkap, 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div class="flex-1">
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 13px;">{{ $santri->nama_lengkap }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $santri->jenis_kelamin == 'L' ? 'bg-secondary' : 'bg-warning' }} btn-round" style="font-size: 10px;">
                                                {{ $santri->jenis_kelamin }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark" style="font-size: 13px;">{{ $santri->wali?->nama ?? '-' }}</div>
                                            <small class="text-muted">{{ $santri->wali?->no_hp ?? '' }}</small>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $statusClass = [
                                                    'Aktif' => 'badge-success',
                                                    'Lulus' => 'badge-primary',
                                                    'Keluar' => 'badge-danger',
                                                    'Pindah' => 'badge-warning'
                                                ][$santri->status] ?? 'badge-secondary';
                                            @endphp
                                            <span class="badge {{ $statusClass }}">{{ $santri->status }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted small fw-bold">
                                                <i class="far fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($santri->tanggal_masuk)->format('d/m/Y') }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-0">
                                            <div class="form-button-action">
                                                <a href="{{ route('tenant.santri.edit', $santri) }}" 
                                                   class="btn btn-link btn-primary btn-lg p-2" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Edit Profil">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                        
                                                <form action="{{ route('tenant.santri.destroy', $santri) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" 
                                                            class="btn btn-link btn-danger btn-lg p-2 btn-delete" 
                                                            data-bs-toggle="tooltip" 
                                                            title="Hapus Santri">
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
    /* Merapikan form-button-action supaya icon tidak bertumpuk */
    .form-button-action {
        display: flex !important;
        justify-content: flex-end;
        gap: 2px;
    }

    /* Memastikan icon pensil/edit tidak tenggelam */
    .btn-link.btn-primary {
        color: #1572e8 !important; /* Warna biru icon edit */
    }

    .btn-link.btn-primary:hover {
        color: #1266d4 !important;
        background: transparent !important;
    }

    /* Ukuran icon supaya pas */
    .form-button-action i {
        font-size: 16px !important;
    }
    /* Tema Warna Info/Teal */
    .btn-info, .bg-info, .table-head-bg-info thead th {
        background: #48abf7 !important; /* Biru muda / Teal */
        border-color: #48abf7 !important;
    }
    
    .text-info { color: #48abf7 !important; }

    .mt-n1 { margin-top: -10px !important; }
    
    .table-head-bg-info thead th {
        color: #fff !important;
        padding: 12px 15px !important;
        font-size: 10px !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .card-round { border-radius: 12px !important; }
    
    .btn-label-info {
        background: #e1f2ff;
        color: #48abf7;
        border: none;
    }
    
    .btn-label-info:hover {
        background: #48abf7;
        color: white;
    }

    /* Custom Badges */
    .badge { padding: 5px 12px; font-weight: 700; border-radius: 50px; font-size: 10px; }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // 1. Initialize DataTables
        var table = $('#santriTable').DataTable({
            "pageLength": 10,
            "language": {
                "search": "",
                "searchPlaceholder": "Cari NIS atau Nama...",
                "lengthMenu": "_MENU_",
                "info": "Menampilkan _START_ - _END_ dari _TOTAL_ santri",
                "paginate": {
                    "next": '<i class="fa fa-chevron-right"></i>',
                    "previous": '<i class="fa fa-chevron-left"></i>'
                }
            },
            "dom": '<"d-flex flex-wrap justify-content-between align-items-center mb-3"lf>rt<"d-flex flex-wrap justify-content-between align-items-center mt-3"ip>'
        });

        // 2. PERBAIKAN TOMBOL AKSI (Event Delegation)
        // Kita tembak ke body atau #santriTable supaya tombol tetap jalan walau pindah page
        $('body').on('click', '.btn-delete', function(e) {
            e.preventDefault(); // Stop form biar gak langsung kehapus
            
            var form = $(this).closest("form");
            
            swal({
                title: "Hapus Santri?",
                text: "Data akan dipindahkan ke kotak sampah.",
                icon: "warning",
                buttons: {
                    cancel: { 
                        visible: true, 
                        text: "Batal", 
                        className: 'btn btn-focus' 
                    },
                    confirm: { 
                        text: "Ya, Hapus", 
                        className: 'btn btn-danger' 
                    }
                }
            }).then((willDelete) => {
                if (willDelete) {
                    form.submit(); // Eksekusi hapus jika klik OK
                }
            });
        });

        // 3. Inisialisasi Tooltip ulang setiap kali tabel di-draw
        table.on('draw', function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });

        // 4. Notify Kanan Bawah
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