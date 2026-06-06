@extends('layouts.tenant')

@section('title', 'Detail Profil Santri')

@section('content')
@php
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

    $state = $statusMap[$santri->status] ?? ['label' => ucfirst($santri->status), 'class' => 'bg-secondary-soft text-secondary'];
@endphp

{{-- BREADCRUMB --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb breadcrumb-style-1 mb-0" style="background: transparent; padding: 0;">
        <li class="breadcrumb-item"><a href="{{ route('tenant.santri.index') }}">Database Santri</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail Profil</li>
    </ol>
</nav>

{{-- HEADER SECTION --}}
<div class="card card-round mb-4 border-0">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4">
            <div class="d-flex align-items-center flex-column flex-sm-row text-center text-sm-start">
                <div class="avatar avatar-xxl me-sm-4 mb-3 mb-sm-0">
                    <span class="avatar-title rounded-circle border border-white {{ $santri->jenis_kelamin == 'L' ? 'bg-primary-soft text-primary' : 'bg-danger-soft text-danger' }} fw-bold" style="font-size: 32px; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                        {{ strtoupper(substr($santri->nama_lengkap, 0, 1)) }}
                    </span>
                </div>
                <div>
                    <div class="d-flex align-items-center justify-content-center justify-content-sm-start flex-wrap gap-2 mb-1.5">
                        <h3 class="text-dark fw-bold mb-0" style="font-size: 1.8rem;">{{ $santri->nama_lengkap }}</h3>
                        <span class="badge {{ $state['class'] }} rounded-pill px-3 py-1 fw-bold text-xs border">
                            {{ $state['label'] }}
                        </span>
                    </div>
                    <p class="text-slate mb-1">
                        <span class="fw-semibold text-primary">#{{ $santri->nis }}</span> &bull; 
                        <span class="small fw-bold {{ $santri->jenis_kelamin == 'L' ? 'text-info' : 'text-danger' }}">
                            <i class="fas {{ $santri->jenis_kelamin == 'L' ? 'fa-mars' : 'fa-venus' }} me-1"></i>
                            {{ $santri->jenis_kelamin == 'L' ? 'Putra (Laki-laki)' : 'Putri (Perempuan)' }}
                        </span>
                    </p>
                    <p class="text-muted mb-0 small">
                        <i class="far fa-calendar-alt me-1"></i> Masuk: {{ $santri->tanggal_masuk ? $santri->tanggal_masuk->format('d M Y') : '-' }}
                        @if($santri->tanggal_keluar)
                            &bull; Keluar: {{ $santri->tanggal_keluar->format('d M Y') }}
                        @endif
                    </p>
                </div>
            </div>
            <div class="d-flex gap-2 justify-content-center">
                <a href="{{ route('tenant.santri.index') }}" class="btn btn-light btn-round border shadow-sm btn-sm px-3.5">
                    <i class="fas fa-arrow-left me-1.5"></i> Kembali
                </a>
                <a href="{{ route('tenant.santri.edit', $santri) }}" class="btn btn-primary btn-round shadow-sm btn-sm px-3.5">
                    <i class="fas fa-edit me-1.5"></i> Edit Profil
                </a>
            </div>
        </div>
    </div>
</div>

{{-- METRICS SECTION (NATIVE KAI ADMIN STATS CARD) --}}
<div class="row">
    {{-- Kehadiran --}}
    <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round border-0 shadow-none">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-success bubble-shadow-small">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category">Kehadiran</p>
                            <h4 class="card-title text-nowrap" style="font-size: 15px; margin-bottom: 2px;">{{ $absensiStats['hadir'] }} Hadir</h4>
                            <small class="text-muted text-nowrap" style="font-size: 10px;">
                                {{ $absensiStats['izin'] }} I &bull; {{ $absensiStats['sakit'] }} S &bull; <span class="text-danger fw-bold">{{ $absensiStats['alfa'] }} A</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Poin Pelanggaran --}}
    <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round border-0 shadow-none">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center {{ $totalPoin > 50 ? 'icon-danger' : ($totalPoin > 20 ? 'icon-warning' : 'icon-primary') }} bubble-shadow-small">
                            <i class="fas fa-gavel"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category">Poin Pelanggaran</p>
                            <h4 class="card-title">{{ $totalPoin }} Poin</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Kelas --}}
    <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round border-0 shadow-none">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-info bubble-shadow-small">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category">Kelas Saat Ini</p>
                            <h4 class="card-title text-nowrap" style="font-size: 15px;">{{ $santri->kelas->nama ?? 'Belum Ditentukan' }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Kamar & Komplek --}}
    <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round border-0 shadow-none">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-warning bubble-shadow-small">
                            <i class="fas fa-bed"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category">Kamar Asrama</p>
                            <h4 class="card-title text-nowrap" style="font-size: 15px; margin-bottom: 2px;">{{ $santri->kamar->nama ?? 'Belum Ditentukan' }}</h4>
                            @if(isset($santri->kamar->kompleks))
                                <small class="text-muted d-block text-nowrap" style="font-size: 10px;">
                                    Komplek: {{ $santri->kamar->kompleks->nama }}
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MAIN DETAIL CONTENT --}}
<div class="row g-4">
    
    {{-- SIDEBAR: BIODATA & WALI --}}
    <div class="col-lg-4">
        <div class="d-flex flex-column gap-4">
            {{-- Detail Kontak & Personal --}}
            <div class="card card-round border-0 shadow-none">
                <div class="card-header py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-user-circle me-2 text-primary"></i>Profil Personal
                    </h6>
                </div>
                <div class="card-body p-3">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr class="border-bottom">
                                <td class="py-2.5 text-muted small fw-bold" style="width: 35%;"><i class="fas fa-map-marker-alt text-primary me-2"></i> Lahir</td>
                                <td class="py-2.5 text-dark fw-semibold" style="font-size: 13px;">{{ $santri->tempat_lahir ?? '-' }}, {{ $santri->tanggal_lahir ? $santri->tanggal_lahir->format('d M Y') : '-' }}</td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="py-2.5 text-muted small fw-bold"><i class="fas fa-phone text-primary me-2"></i> No. HP</td>
                                <td class="py-2.5 text-dark fw-semibold" style="font-size: 13px;">{{ $santri->no_hp ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="py-2.5 text-muted small fw-bold" valign="top"><i class="fas fa-map-marked-alt text-primary me-2"></i> Alamat</td>
                                <td class="py-2.5 text-dark fw-semibold" style="line-height: 1.5; font-size: 13px;">{{ $santri->alamat ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Wali Murid --}}
            <div class="card card-round border-0 shadow-none">
                <div class="card-header py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-user-shield me-2 text-primary"></i>Informasi Wali Murid
                    </h6>
                </div>
                <div class="card-body p-3">
                    @if($santri->wali)
                        <div class="d-flex align-items-center mb-3 p-2 bg-light rounded-3">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-title bg-primary text-white rounded-circle fw-bold" style="font-size: 13px;">
                                    {{ strtoupper(substr($santri->wali->nama, 0, 1)) }}
                                </span>
                            </div>
                            <div class="flex-1">
                                <h6 class="fw-bold text-dark mb-0" style="font-size: 14px;">{{ $santri->wali->nama }}</h6>
                                <small class="text-muted" style="font-size: 11px;"><i class="fas fa-briefcase me-1"></i> Pekerjaan: {{ $santri->wali->pekerjaan ?? '-' }}</small>
                            </div>
                        </div>
                        
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr class="border-bottom">
                                    <td class="py-2.5 text-muted small fw-bold" style="width: 35%;"><i class="fas fa-phone text-primary me-2"></i> Telepon</td>
                                    <td class="py-2.5 text-dark fw-semibold" style="font-size: 13px;">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span>{{ $santri->wali->no_hp }}</span>
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $santri->wali->no_hp) }}" target="_blank" class="badge bg-success text-white border-0 fw-bold d-inline-flex align-items-center gap-1" style="font-size: 10px; padding: 4px 8px; text-decoration: none;">
                                                <i class="fab fa-whatsapp"></i> Chat WA
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @if($santri->wali->alamat)
                                <tr>
                                    <td class="py-2.5 text-muted small fw-bold" valign="top"><i class="fas fa-home text-primary me-2"></i> Alamat</td>
                                    <td class="py-2.5 text-dark fw-semibold" style="line-height: 1.5; font-size: 13px;">{{ $santri->wali->alamat }}</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-user-slash fa-2x mb-2 text-muted-light"></i>
                            <p class="mb-0 small italic">Belum ada wali yang terhubung.</p>
                            <a href="{{ route('tenant.santri.edit', $santri) }}" class="btn btn-link btn-xs mt-1 decoration-none fw-bold">Hubungkan Sekarang</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN TABBED DATA --}}
    <div class="col-lg-8">
        <div class="card card-round border-0 shadow-none h-100">
            <div class="card-header bg-white p-0 border-bottom">
                <ul class="nav nav-tabs nav-line border-0 px-4" id="profileTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active py-3 px-3 fw-bold text-sm" id="akademik-tab" data-bs-toggle="tab" data-bs-target="#akademik" type="button" role="tab" aria-controls="akademik" aria-selected="true">
                            <i class="fas fa-graduation-cap me-1.5"></i>Akademik & Asrama
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-3 px-3 fw-bold text-sm" id="absensi-tab" data-bs-toggle="tab" data-bs-target="#absensi" type="button" role="tab" aria-controls="absensi" aria-selected="false">
                            <i class="fas fa-calendar-check me-1.5"></i>Absensi ({{ $santri->absensis->count() }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-3 px-3 fw-bold text-sm" id="perizinan-tab" data-bs-toggle="tab" data-bs-target="#perizinan" type="button" role="tab" aria-controls="perizinan" aria-selected="false">
                            <i class="fas fa-file-signature me-1.5"></i>Perizinan ({{ $santri->perizinans->count() }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-3 px-3 fw-bold text-sm" id="pelanggaran-tab" data-bs-toggle="tab" data-bs-target="#pelanggaran" type="button" role="tab" aria-controls="pelanggaran" aria-selected="false">
                            <i class="fas fa-gavel me-1.5"></i>Pelanggaran ({{ $santri->pelanggaranSantri->count() }})
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body p-4">
                <div class="tab-content" id="profileTabContent">
                    
                    {{-- TAB 1: AKADEMIK & ASRAMA --}}
                    <div class="tab-pane fade show active" id="akademik" role="tabpanel" aria-labelledby="akademik-tab">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="fw-bold text-primary mb-3 small uppercase tracking-wider"><i class="fas fa-graduation-cap me-1.5"></i>Detail Kelas</h6>
                                <div class="card card-round shadow-none border p-3 mb-0">
                                    <table class="table table-sm table-borderless align-middle mb-0 text-sm">
                                        <tbody>
                                            <tr class="border-bottom">
                                                <td class="text-muted ps-0 py-2" style="width: 40%;">Nama Kelas</td>
                                                <td class="text-dark fw-bold py-2">{{ $santri->kelas->nama ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted ps-0 py-2">Keterangan</td>
                                                <td class="text-slate py-2" style="line-height: 1.5;">{{ $santri->kelas->keterangan ?? '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold text-primary mb-3 small uppercase tracking-wider"><i class="fas fa-bed me-1.5"></i>Detail Kamar Asrama</h6>
                                <div class="card card-round shadow-none border p-3 mb-0">
                                    <table class="table table-sm table-borderless align-middle mb-0 text-sm">
                                        <tbody>
                                            <tr class="border-bottom">
                                                <td class="text-muted ps-0 py-2" style="width: 40%;">Nama Kamar</td>
                                                <td class="text-dark fw-bold py-2">{{ $santri->kamar->nama ?? '-' }}</td>
                                            </tr>
                                            <tr class="border-bottom">
                                                <td class="text-muted ps-0 py-2">Komplek</td>
                                                <td class="text-dark py-2">{{ $santri->kamar->kompleks->nama ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted ps-0 py-2">Kapasitas</td>
                                                <td class="text-dark py-2">{{ $santri->kamar->kapasitas ?? '-' }} Santri</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-12">
                                <hr class="my-2 opacity-25">
                            </div>
                            <div class="col-12">
                                <h6 class="fw-bold text-primary mb-3 small uppercase tracking-wider"><i class="fas fa-history me-1.5"></i>Log Audit Sistem</h6>
                                <div class="p-3 bg-light rounded-3 d-flex flex-column gap-2 text-xs text-muted">
                                    <div>
                                        <i class="fas fa-plus-circle me-1.5 text-primary"></i> Dibuat oleh: 
                                        <strong class="text-dark">{{ $santri->creator->name ?? 'Sistem' }}</strong> 
                                        pada <span class="text-dark">{{ $santri->created_at->format('d M Y H:i') }}</span>
                                    </div>
                                    @if($santri->updater)
                                        <div>
                                            <i class="fas fa-edit me-1.5 text-info"></i> Terakhir diperbarui oleh: 
                                            <strong class="text-dark">{{ $santri->updater->name }}</strong> 
                                            pada <span class="text-dark">{{ $santri->updated_at->format('d M Y H:i') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 2: ABSENSI --}}
                    <div class="tab-pane fade" id="absensi" role="tabpanel" aria-labelledby="absensi-tab">
                        @if($santri->absensis->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 text-sm">
                                    <thead class="bg-light text-muted">
                                        <tr>
                                            <th class="ps-3" style="width: 25%;">Tanggal</th>
                                            <th style="width: 30%;">Sesi Absensi</th>
                                            <th class="text-center" style="width: 20%;">Status</th>
                                            <th class="pe-3" style="width: 25%;">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($santri->absensis as $absensi)
                                            <tr>
                                                <td class="ps-3 fw-medium text-nowrap">{{ $absensi->tanggal->format('d M Y') }}</td>
                                                <td class="text-nowrap">
                                                    <span class="fw-semibold text-dark">{{ $absensi->sesi->nama_sesi ?? 'Umum' }}</span>
                                                    <small class="text-muted d-block" style="font-size: 11px;">Metode: {{ ucfirst($absensi->metode ?? 'manual') }}</small>
                                                </td>
                                                <td class="text-center text-nowrap">
                                                    @php
                                                        $absStatusMap = [
                                                            'hadir' => 'bg-success text-white',
                                                            'sakit' => 'bg-warning text-dark',
                                                            'izin' => 'bg-info text-white',
                                                            'alfa' => 'bg-danger text-white',
                                                        ];
                                                        $absClass = $absStatusMap[strtolower($absensi->status)] ?? 'bg-secondary text-white';
                                                    @endphp
                                                    <span class="badge {{ $absClass }} rounded-pill px-2.5 py-1 fw-bold text-xs" style="text-transform: uppercase; font-size: 10px;">
                                                        {{ $absensi->status }}
                                                    </span>
                                                </td>
                                                <td class="text-slate pe-3 small">{{ $absensi->keterangan ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-calendar-times fa-3x mb-3 text-muted-light"></i>
                                <p class="mb-0 fw-semibold">Belum Ada Riwayat Absensi</p>
                                <p class="text-xs mb-0">Riwayat absen santri akan tercatat otomatis saat sesi absensi dijalankan.</p>
                            </div>
                        @endif
                    </div>

                    {{-- TAB 3: PERIZINAN --}}
                    <div class="tab-pane fade" id="perizinan" role="tabpanel" aria-labelledby="perizinan-tab">
                        @if($santri->perizinans->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 text-sm">
                                    <thead class="bg-light text-muted">
                                        <tr>
                                            <th class="ps-3" style="width: 20%;">Kode / No</th>
                                            <th style="width: 35%;">Keperluan & Waktu Keluar</th>
                                            <th style="width: 25%;">Batas & Waktu Kembali</th>
                                            <th class="text-center pe-3" style="width: 20%;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($santri->perizinans as $izin)
                                            <tr>
                                                <td class="ps-3 text-nowrap">
                                                    <span class="fw-bold text-primary" style="font-size: 12.5px;">{{ $izin->kode_surat }}</span>
                                                    @if($izin->nomor_manual)
                                                        <small class="text-muted d-block mt-0.5">No: {{ $izin->nomor_manual }}</small>
                                                    @endif
                                                </td>
                                                <td class="text-nowrap">
                                                    <strong class="text-dark d-block" style="font-size: 13.5px;">{{ $izin->keperluan }}</strong>
                                                    <span class="text-slate small d-block mt-0.5">
                                                        <i class="far fa-clock text-muted me-1"></i>Keluar: {{ $izin->tanggal_keluar ? $izin->tanggal_keluar->format('d M Y H:i') : '-' }}
                                                    </span>
                                                </td>
                                                <td class="text-nowrap">
                                                    <span class="text-slate small d-block">
                                                        <i class="fas fa-hourglass-half text-muted me-1"></i>Batas: {{ $izin->batas_kembali ? $izin->batas_kembali->format('d M Y H:i') : '-' }}
                                                    </span>
                                                    @if($izin->tanggal_kembali)
                                                        <span class="text-success small d-block mt-0.5">
                                                            <i class="fas fa-check-circle text-success me-1"></i>Kembali: {{ $izin->tanggal_kembali->format('d M Y H:i') }}
                                                        </span>
                                                    @else
                                                        <span class="text-danger small d-block mt-0.5">
                                                            <i class="fas fa-spinner fa-spin text-danger me-1"></i>Belum Kembali
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-center pe-3 text-nowrap">
                                                    @php
                                                        $izinStatusMap = [
                                                            'aktif' => 'bg-warning-soft text-warning border-warning-subtle',
                                                            'kembali' => 'bg-success-soft text-success border-success-subtle',
                                                            'terlambat' => 'bg-danger-soft text-danger border-danger-subtle',
                                                            'dibatalkan' => 'bg-secondary-soft text-secondary border-secondary-subtle',
                                                        ];
                                                        $lblClass = $izinStatusMap[$izin->status] ?? 'bg-secondary-soft text-secondary';
                                                    @endphp
                                                    <span class="badge {{ $lblClass }} border rounded-pill px-2.5 py-1.5 fw-bold text-xs" style="font-size: 10px;">
                                                        {{ $izin->status_label }}
                                                    </span>
                                                    @if($izin->status === 'terlambat' && $izin->durasi_terlambat_human)
                                                        <small class="text-danger fw-bold d-block mt-1" style="font-size: 10px;">
                                                            + {{ $izin->durasi_terlambat_human }}
                                                        </small>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-file-medical-alt fa-3x mb-3 text-muted-light"></i>
                                <p class="mb-0 fw-semibold">Belum Ada Riwayat Perizinan</p>
                                <p class="text-xs mb-0">Izin keluar komplek / liburan santri belum pernah diajukan.</p>
                            </div>
                        @endif
                    </div>

                    {{-- TAB 4: PELANGGARAN --}}
                    <div class="tab-pane fade" id="pelanggaran" role="tabpanel" aria-labelledby="pelanggaran-tab">
                        @if($santri->pelanggaranSantri->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 text-sm">
                                    <thead class="bg-light text-muted">
                                        <tr>
                                            <th class="ps-3" style="width: 20%;">Tanggal</th>
                                            <th style="width: 45%;">Pelanggaran & Kategori</th>
                                            <th class="text-center" style="width: 15%;">Poin</th>
                                            <th class="pe-3" style="width: 20%;">Pencatat</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($santri->pelanggaranSantri as $pelanggaran)
                                            <tr>
                                                <td class="ps-3 fw-medium text-nowrap">
                                                    {{ $pelanggaran->tanggal->format('d M Y') }}
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark" style="font-size: 13.5px;">{{ $pelanggaran->judul_pelanggaran }}</div>
                                                    <small class="text-muted d-block mt-0.5">
                                                        Sumber: {{ ucfirst($pelanggaran->kategori_sumber) }} 
                                                        @if($pelanggaran->kategoriPelanggaran)
                                                            &bull; Kategori: {{ $pelanggaran->kategoriPelanggaran->nama_kategori }}
                                                        @endif
                                                    </small>
                                                    @if($pelanggaran->catatan_detail)
                                                        <small class="text-slate d-block mt-1 italic">{{ $pelanggaran->catatan_detail }}</small>
                                                    @endif
                                                </td>
                                                <td class="text-center text-nowrap">
                                                    <span class="badge bg-danger-soft text-danger border border-danger-subtle rounded-pill px-2.5 py-1.5 fw-bold text-xs" style="font-size: 10.5px;">
                                                        +{{ $pelanggaran->poin }}
                                                    </span>
                                                </td>
                                                <td class="text-slate pe-3 small text-nowrap">
                                                    {{ $pelanggaran->pencatat->name ?? 'Sistem' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5 text-success">
                                <i class="fas fa-shield-alt fa-3x mb-3 text-success-light"></i>
                                <p class="mb-0 fw-semibold text-success">Sangat Bagus! Bersih Pelanggaran</p>
                                <p class="text-xs mb-0 text-muted">Santri ini tidak memiliki catatan indispliner atau pelanggaran hukum pondok.</p>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

<style>
    /* Icon Avatar Styles */
    .icon-avatar {
        width: 46px;
        height: 46px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .bg-primary-gradient {
        background: linear-gradient(135deg, #1572e8 0%, #064095 100%) !important;
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

    .text-muted-light {
        color: #cbd5e1 !important;
    }
    
    .text-success-light {
        color: #a7f3d0 !important;
    }

    .btn-round { border-radius: 50px; }
    
    .text-slate { color: #475569; }
    .text-xs { font-size: 0.75rem; }
    .text-sm { font-size: 0.875rem; }

    /* Nav Tabs Line Customization */
    .nav-tabs.nav-line {
        border-bottom: 1px solid #f1f5f9;
    }
    
    .nav-tabs.nav-line .nav-item .nav-link {
        border: none;
        background: transparent;
        color: #64748b;
        position: relative;
        transition: all 0.2s ease;
    }
    
    .nav-tabs.nav-line .nav-item .nav-link:hover {
        color: #1572e8;
    }
    
    .nav-tabs.nav-line .nav-item .nav-link.active {
        color: #1572e8;
    }
    
    .nav-tabs.nav-line .nav-item .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background-color: #1572e8;
        border-radius: 3px 3px 0 0;
    }

    .leading-relaxed {
        line-height: 1.625;
    }

    .uppercase {
        text-transform: uppercase;
    }

    .tracking-wider {
        letter-spacing: 0.05em;
    }
</style>
@endsection
