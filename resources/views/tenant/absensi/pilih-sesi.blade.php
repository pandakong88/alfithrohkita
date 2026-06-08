@extends('layouts.tenant')

@section('title', 'Gerbang Absensi - Al Fitroh')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title"><i class="fas fa-user-check mr-2 text-primary"></i> Gerbang Absensi</h4>
        <ul class="breadcrumbs">
            <li class="nav-home"><a href="#"><i class="flaticon-home"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Absensi</a></li>
        </ul>
    </div>

    <!-- Filter & Search Card -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center flex-wrap" style="gap: 15px;">
                        <div class="flex-grow-1">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                </div>
                                <input type="text" id="search_sesi" class="form-control" placeholder="Cari nama sesi...">
                            </div>
                        </div>

                        <div class="d-flex align-items-center" style="gap: 10px;">
                            <div class="dropdown">
                                <button class="btn btn-white btn-border dropdown-toggle font-weight-bold" type="button" data-toggle="dropdown">
                                    <i class="fas fa-sort-amount-down mr-1"></i> Urutkan
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item sort-trigger" href="#" data-sort="time-asc">Waktu Terdekat</a>
                                    <a class="dropdown-item sort-trigger" href="#" data-sort="name-asc">Nama A-Z</a>
                                    <a class="dropdown-item sort-trigger" href="#" data-sort="progress-desc">Progres Input</a>
                                </div>
                            </div>
                            
                            <div class="form-group mb-0 d-flex align-items-center bg-light px-2 rounded" style="border: 1px solid #ebedf2;">
                                <label class="mb-0 mr-2 small font-weight-bold">TANGGAL:</label>
                                <input type="date" class="form-control form-control-sm border-0 bg-transparent font-weight-bold" 
                                       id="filter_tanggal" value="{{ $tanggal }}" style="width: 140px; cursor: pointer;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid Sesi -->
    <div class="row" id="sesi_container">
        @forelse($sesis as $sesi)
        @php
            $colors = ['bg-primary-gradient', 'bg-info-gradient', 'bg-success-gradient', 'bg-secondary-gradient'];
            $bgClass = $colors[$loop->index % count($colors)];
        @endphp
    
        <div class="col-xl-4 col-md-6 mb-4 sesi-item" data-name="{{ strtolower($sesi->nama_sesi) }}">
            <div class="card card-stats card-round border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 {{ $bgClass }} text-white">
                        <h4 class="font-weight-bold mb-1">{{ $sesi->nama_sesi }}</h4>
                        <p class="mb-0 small opacity-8">
                            <i class="far fa-clock mr-1"></i> {{ substr($sesi->jam_mulai, 0, 5) }} WIB
                        </p>
                    </div>
                    
                    <div class="p-4 bg-white">
                        <h6 class="text-muted small text-uppercase font-weight-bold mb-3">Laporan Ketidakhadiran :</h6>
                        
                        <div class="d-flex justify-content-around text-center mb-4">
                            <div>
                                <h5 class="mb-0 font-weight-bold text-danger">{{ $sesi->jumlah_alfa }}</h5>
                                <small class="text-muted">ALFA</small>
                            </div>
                            <div style="border-left: 1px solid #eee;"></div>
                            <div>
                                <h5 class="mb-0 font-weight-bold text-warning">{{ $sesi->jumlah_izin_sakit }}</h5>
                                <small class="text-muted">IZIN/S</small>
                            </div>
                            <div style="border-left: 1px solid #eee;"></div>
                            <div>
                                <h5 class="mb-0 font-weight-bold text-muted">{{ $sesi->jumlah_terlambat }}</h5>
                                <small class="text-muted">TELAT</small>
                            </div>
                        </div>
    
                        <div class="row no-gutters">
                            <div class="col-@can('manage_absensi')6 pr-1 @else 12 @endcan">
                                <a href="{{ route('tenant.absensi.index', ['sesi_id' => $sesi->id]) }}?tanggal={{ $tanggal }}" 
                                   class="btn btn-primary btn-block btn-round font-weight-bold">
                                    @can('manage_absensi') Buka Absensi @else Lihat Rekap @endcan
                                </a>
                            </div>
                            @can('manage_absensi')
                            <div class="col-6 pl-1">
                                <a href="{{ route('tenant.absensi.index', ['sesi_id' => $sesi->id, 'mode' => 'scan']) }}?tanggal={{ $tanggal }}" 
                                   class="btn btn-border btn-block btn-round font-weight-bold">Scan QR</a>
                            </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <!-- Empty state -->
    @endforelse
    </div>
</div>

<style>
    .sesi-item:hover { transform: translateY(-5px); }
    .btn-border { border: 1px solid #ebedf2 !important; }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Live Search
        $('#search_sesi').on('keyup', function() {
            let val = $(this).val().toLowerCase();
            $('.sesi-item').each(function() {
                $(this).toggle($(this).data('name').indexOf(val) > -1);
            });
        });

        // Sorting
        $('.sort-trigger').on('click', function(e) {
            e.preventDefault();
            let sort = $(this).data('sort');
            let items = $('.sesi-item').get();
            items.sort(function(a, b) {
                let A = $(a).data(sort.split('-')[0]);
                let B = $(b).data(sort.split('-')[0]);
                if(sort === 'progress-desc') return parseFloat(B) - parseFloat(A);
                if(sort === 'time-asc') return (A < B) ? -1 : 1;
                return A.toString().localeCompare(B.toString());
            });
            $.each(items, function(i, itm) { $('#sesi_container').append(itm); });
        });

        // Filter Tanggal
        $('#filter_tanggal').on('change', function() {
            window.location.href = "{{ route('tenant.absensi.pilih-sesi') }}?tanggal=" + $(this).val();
        });
    });
</script>
@endpush