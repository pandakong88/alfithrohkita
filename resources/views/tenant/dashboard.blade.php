@extends('layouts.tenant')

@section('title', 'Dashboard Pondok')

@section('content')
{{-- HEADER SECTION --}}
<div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
    <div>
        <h3 class="fw-bold mb-1 text-dark" style="font-size: 1.8rem; letter-spacing: -0.02em;">Dashboard Pondok</h3>
        <h6 class="text-muted mb-0">Ringkasan aktivitas santri, kehadiran, perizinan, dan kedisiplinan hari ini.</h6>
    </div>
</div>

{{-- ROW 1: CARDS STATISTIK --}}
<div class="row g-3 mb-4">
    <!-- Total Santri -->
    <div class="col-6 col-lg-3">
        <div class="card card-stats card-round border-0 shadow-sm mb-0">
            <div class="card-body p-3.5">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-primary bubble-shadow-small bg-primary text-white" style="border-radius: 12px; width: 50px; height: 50px; line-height: 50px;">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category text-muted text-xs fw-semibold mb-0">TOTAL SANTRI</p>
                            <h4 class="card-title fw-bold text-dark mb-0 mt-0.5" style="font-size: 1.5rem;">{{ $totalSantri }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Wali Murid -->
    <div class="col-6 col-lg-3">
        <div class="card card-stats card-round border-0 shadow-sm mb-0">
            <div class="card-body p-3.5">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-success bubble-shadow-small bg-success text-white" style="border-radius: 12px; width: 50px; height: 50px; line-height: 50px;">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category text-muted text-xs fw-semibold mb-0">WALI MURID</p>
                            <h4 class="card-title fw-bold text-dark mb-0 mt-0.5" style="font-size: 1.5rem;">{{ $totalWali }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sedang Izin Keluar -->
    <div class="col-6 col-lg-3">
        <div class="card card-stats card-round border-0 shadow-sm mb-0">
            <div class="card-body p-3.5">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-warning bubble-shadow-small bg-warning text-white" style="border-radius: 12px; width: 50px; height: 50px; line-height: 50px;">
                            <i class="fas fa-sign-out-alt"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category text-muted text-xs fw-semibold mb-0">SEDANG IZIN</p>
                            <h4 class="card-title fw-bold text-dark mb-0 mt-0.5" style="font-size: 1.5rem;">{{ $aktifIzin }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kasus Pelanggaran -->
    <div class="col-6 col-lg-3">
        <div class="card card-stats card-round border-0 shadow-sm mb-0">
            <div class="card-body p-3.5">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-danger bubble-shadow-small bg-danger text-white" style="border-radius: 12px; width: 50px; height: 50px; line-height: 50px;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category text-muted text-xs fw-semibold mb-0">PELANGGARAN</p>
                            <h4 class="card-title fw-bold text-dark mb-0 mt-0.5" style="font-size: 1.5rem;">{{ $totalPelanggaran }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ROW 2: PRESENSI HARI INI & DISTRIBUSI SANTRI --}}
<div class="row g-4 mb-4">
    <!-- Presensi Kehadiran Hari Ini -->
    <div class="col-md-7">
        <div class="card card-round border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="card-title fw-bold text-dark mb-0"><i class="fas fa-calendar-check text-primary me-2"></i>Kehadiran Santri Hari Ini</h5>
                <p class="text-muted small mb-0">{{ date('d F Y') }}</p>
            </div>
            <div class="card-body px-4 pb-4 pt-3">
                @php
                    $totalToday = $hadirToday + $sakitToday + $izinToday + $alfaToday + $terlambatToday;
                    $percentHadir = $totalToday > 0 ? round(($hadirToday / $totalToday) * 100) : 0;
                    $percentIzin = $totalToday > 0 ? round((($izinToday + $sakitToday) / $totalToday) * 100) : 0;
                    $percentAlfa = $totalToday > 0 ? round(($alfaToday / $totalToday) * 100) : 0;
                    $percentTerlambat = $totalToday > 0 ? round(($terlambatToday / $totalToday) * 100) : 0;
                @endphp

                @if($totalToday == 0)
                    <div class="text-center py-5">
                        <div class="icon-avatar bg-light-soft text-muted mx-auto mb-3" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-clipboard-list fa-lg"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Belum Ada Presensi Hari Ini</h6>
                        <p class="text-muted small mb-0">Pencatatan kehadiran santri untuk hari ini belum diinput.</p>
                        <a href="{{ route('tenant.absensi.pilih-sesi') }}" class="btn btn-primary btn-sm btn-round mt-3">
                            Mulai Presensi <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                @else
                    <div class="d-flex align-items-center mb-4">
                        <div class="me-4">
                            <span class="text-muted small d-block">TOTAL TERCATAT</span>
                            <h2 class="fw-bold text-dark mb-0">{{ $totalToday }} <span class="text-muted text-lg" style="font-size: 1rem;">Santri</span></h2>
                        </div>
                        <div class="border-start ps-4">
                            <span class="badge badge-success px-3 py-1.5 rounded-pill mb-0.5" style="font-size: 11px;">
                                <i class="fas fa-check-circle me-1"></i> {{ $percentHadir }}% Hadir Tepat Waktu
                            </span>
                        </div>
                    </div>

                    <!-- Progress Bar Cluster -->
                    <div class="space-y-4">
                        <div>
                            <div class="d-flex justify-content-between text-sm mb-1">
                                <span class="fw-semibold text-dark"><i class="fas fa-circle text-success me-2 font-xs"></i>Hadir</span>
                                <span class="text-muted fw-bold">{{ $hadirToday }} Santri ({{ $percentHadir }}%)</span>
                            </div>
                            <div class="progress progress-sm bg-light-soft rounded-pill" style="height: 8px;">
                                <div class="progress-bar bg-success rounded-pill" role="progressbar" style="width: {{ $percentHadir }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex justify-content-between text-sm mb-1">
                                <span class="fw-semibold text-dark"><i class="fas fa-circle text-info me-2 font-xs"></i>Sakit & Izin Resmi</span>
                                <span class="text-muted fw-bold">{{ $sakitToday + $izinToday }} Santri ({{ $percentIzin }}%)</span>
                            </div>
                            <div class="progress progress-sm bg-light-soft rounded-pill" style="height: 8px;">
                                <div class="progress-bar bg-info rounded-pill" role="progressbar" style="width: {{ $percentIzin }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex justify-content-between text-sm mb-1">
                                <span class="fw-semibold text-dark"><i class="fas fa-circle text-warning me-2 font-xs"></i>Terlambat Masuk Sesi</span>
                                <span class="text-muted fw-bold">{{ $terlambatToday }} Santri ({{ $percentTerlambat }}%)</span>
                            </div>
                            <div class="progress progress-sm bg-light-soft rounded-pill" style="height: 8px;">
                                <div class="progress-bar bg-warning rounded-pill" role="progressbar" style="width: {{ $percentTerlambat }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex justify-content-between text-sm mb-1">
                                <span class="fw-semibold text-dark"><i class="fas fa-circle text-danger me-2 font-xs"></i>Absen Tanpa Keterangan (Alfa)</span>
                                <span class="text-muted fw-bold">{{ $alfaToday }} Santri ({{ $percentAlfa }}%)</span>
                            </div>
                            <div class="progress progress-sm bg-light-soft rounded-pill" style="height: 8px;">
                                <div class="progress-bar bg-danger rounded-pill" role="progressbar" style="width: {{ $percentAlfa }}%"></div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Grafik Distribusi Gender Santri -->
    <div class="col-md-5">
        <div class="card card-round border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="card-title fw-bold text-dark mb-0"><i class="fas fa-venus-mars text-info me-2"></i>Distribusi Gender Santri</h5>
            </div>
            <div class="card-body px-4 pb-4 pt-3 d-flex flex-column justify-content-center">
                @if($totalSantri == 0)
                    <div class="text-center py-4">
                        <p class="text-muted mb-0">Belum ada data santri terdaftar.</p>
                    </div>
                @else
                    <div class="chart-container d-flex align-items-center justify-content-center" style="min-height: 180px; position: relative;">
                        <canvas id="genderChart" style="max-width: 180px; max-height: 180px;"></canvas>
                    </div>
                    <div class="row text-center mt-3 g-2">
                        <div class="col-6">
                            <span class="text-muted text-xs fw-semibold d-block">LAKI-LAKI (SANTRI)</span>
                            <h5 class="fw-bold text-primary mb-0 mt-0.5"><i class="fas fa-mars me-1.5"></i>{{ $santriLaki }} <span class="text-muted small fw-normal">({{ $totalSantri > 0 ? round(($santriLaki / $totalSantri) * 100) : 0 }}%)</span></h5>
                        </div>
                        <div class="col-6 border-start">
                            <span class="text-muted text-xs fw-semibold d-block">PEREMPUAN (SANTRIWATI)</span>
                            <h5 class="fw-bold text-danger mb-0 mt-0.5"><i class="fas fa-venus me-1.5"></i>{{ $santriPerempuan }} <span class="text-muted small fw-normal">({{ $totalSantri > 0 ? round(($santriPerempuan / $totalSantri) * 100) : 0 }}%)</span></h5>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ROW 3: GRAFIK TREN PELANGGARAN --}}
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card card-round border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="card-title fw-bold text-dark mb-0"><i class="fas fa-chart-line text-danger me-2"></i>Tren Laporan Pelanggaran (7 Hari Terakhir)</h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="min-height: 250px;">
                    <canvas id="violationChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ROW 4: LOG LAYOUT (IZIN TERBARU & PELANGGARAN TERBARU) --}}
<div class="row g-4">
    <!-- Log Perizinan Terbaru -->
    <div class="col-md-6">
        <div class="card card-round border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold text-dark mb-0"><i class="fas fa-file-contract text-warning me-2"></i>Keluar-Masuk Terbaru</h5>
                <a href="{{ route('tenant.perizinan.index') }}" class="btn btn-link btn-xs text-primary fw-bold p-0">Lihat Semua <i class="fas fa-chevron-right ms-1"></i></a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light-soft text-muted font-xs text-uppercase">
                            <tr>
                                <th class="ps-4">Santri</th>
                                <th>Keperluan</th>
                                <th>Kembali</th>
                                <th class="pe-4 text-end">Status</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.85rem;">
                            @forelse($recentPerizinans as $izin)
                            <tr>
                                <td class="ps-4 text-nowrap">
                                    <div class="fw-bold text-dark">{{ $izin->santri->nama_lengkap }}</div>
                                    <small class="text-muted">Kelas: {{ $izin->santri->kelas->nama ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="text-dark d-inline-block text-truncate" style="max-width: 120px;" title="{{ $izin->keperluan }}">
                                        {{ $izin->keperluan ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-nowrap">
                                    <div>{{ $izin->batas_kembali->format('d/m/y') }}</div>
                                    <small class="text-muted">{{ $izin->batas_kembali->format('H:i') }} WIB</small>
                                </td>
                                <td class="pe-4 text-end text-nowrap">
                                    @if($izin->status === 'kembali')
                                        <span class="badge badge-success px-2.5 py-1 rounded-pill" style="font-size: 10px;">KEMBALI</span>
                                    @elseif($izin->status === 'terlambat')
                                        <span class="badge badge-danger px-2.5 py-1 rounded-pill" style="font-size: 10px;">TERLAMBAT</span>
                                    @elseif($izin->status === 'dibatalkan')
                                        <span class="badge badge-light text-muted px-2.5 py-1 rounded-pill" style="font-size: 10px;">BATAL</span>
                                    @else
                                        <span class="badge badge-warning px-2.5 py-1 rounded-pill" style="font-size: 10px;">SEDANG PERGI</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">Belum ada data keluar masuk terbaru.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Log Pelanggaran Terbaru -->
    <div class="col-md-6">
        <div class="card card-round border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold text-dark mb-0"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Pelanggaran Terbaru</h5>
                <a href="{{ route('tenant.pelanggaran.index') }}" class="btn btn-link btn-xs text-primary fw-bold p-0">Lihat Semua <i class="fas fa-chevron-right ms-1"></i></a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light-soft text-muted font-xs text-uppercase">
                            <tr>
                                <th class="ps-4">Santri</th>
                                <th>Pelanggaran</th>
                                <th class="text-center">Poin</th>
                                <th class="pe-4 text-end">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.85rem;">
                            @forelse($recentPelanggarans as $pelanggaran)
                            <tr>
                                <td class="ps-4 text-nowrap">
                                    <div class="fw-bold text-dark">{{ $pelanggaran->santri->nama_lengkap }}</div>
                                    <small class="text-muted">Kelas: {{ $pelanggaran->santri->kelas->nama ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="text-dark d-inline-block text-truncate" style="max-width: 140px;" title="{{ $pelanggaran->judul_pelanggaran }}">
                                        {{ $pelanggaran->judul_pelanggaran }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-danger-light text-danger fw-bold rounded-pill px-2.5 py-1" style="font-size: 10px;">+{{ $pelanggaran->poin }}</span>
                                </td>
                                <td class="pe-4 text-end text-nowrap">
                                    <div>{{ $pelanggaran->tanggal->format('d/m/Y') }}</div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">Belum ada laporan pelanggaran terbaru.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-light-soft { background-color: #f8fbff; }
    .badge-danger-light { background-color: #fee2e2; color: #b91c1c; border: none; }
    .font-xs { font-size: 0.7rem; letter-spacing: 0.05em; }
    .space-y-4 > * + * { margin-top: 1rem; }
    .card-category { font-size: 11px; letter-spacing: 0.05em; }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 1. Violation Trend Chart
        var ctxViolation = document.getElementById('violationChart').getContext('2d');
        var violationChart = new Chart(ctxViolation, {
            type: 'line',
            data: {
                labels: {!! json_encode($violationLabels) !!},
                datasets: [{
                    label: "Kasus Pelanggaran",
                    borderColor: "#f3545d",
                    pointBorderColor: "#FFF",
                    pointBackgroundColor: "#f3545d",
                    pointBorderWidth: 2,
                    pointHoverRadius: 4,
                    pointHoverBorderWidth: 1,
                    pointRadius: 4,
                    backgroundColor: 'rgba(243, 84, 93, 0.08)',
                    fill: true,
                    borderWidth: 2,
                    data: {!! json_encode($violationTrend) !!}
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            stepSize: 1,
                            fontColor: '#8d9498'
                        },
                        gridLines: {
                            color: '#f1f1f1',
                            zeroLineColor: '#f1f1f1'
                        }
                    }],
                    xAxes: [{
                        ticks: {
                            fontColor: '#8d9498'
                        },
                        gridLines: {
                            display: false
                        }
                    }]
                }
            }
        });

        // 2. Gender Doughnut Chart
        @if($totalSantri > 0)
        var ctxGender = document.getElementById('genderChart').getContext('2d');
        var genderChart = new Chart(ctxGender, {
            type: 'doughnut',
            data: {
                labels: ["Laki-laki", "Perempuan"],
                datasets: [{
                    data: [{{ $santriLaki }}, {{ $santriPerempuan }}],
                    backgroundColor: ["#1572e8", "#f3545d"],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false
                },
                cutoutPercentage: 75
            }
        });
        @endif
    });
</script>
@endsection