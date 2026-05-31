@extends('layouts.tenant')

@section('content')



<!-- START: CONTENT UTAMA DASHBOARD -->
<div class="container">
<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">Dashboard Tenant</h3>
            <h6 class="op-7 mb-2">Ringkasan performa toko top-up game Anda hari ini.</h6>
        </div>
    </div>

    <!-- ROW 1: CARDS STATISTIK (DATA DUMMY) -->
    <div class="row">
        <!-- Pendapatan Hari Ini -->
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                <i class="fas fa-wallet"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Pendapatan Hari Ini</p>
                                <h4 class="card-title">Rp 2.450.000</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaksi Sukses -->
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body pt-3">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Transaksi Sukses</p>
                                <h4 class="card-title">142 Tx</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sisa Saldo API/H2H -->
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                <i class="fas fa-server"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Saldo Dghost Engine</p>
                                <h4 class="card-title">Rp 7.890.000</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaksi Pending -->
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-warning bubble-shadow-small">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Transaksi Pending</p>
                                <h4 class="card-title">3 Order</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW 2: GRAFIK & PRODUK TERLARIS -->
    <div class="row">
        <!-- Grafik Tren Penjualan -->
        <div class="col-md-8">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Tren Penjualan (7 Hari Terakhir)</div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Placeholder untuk Chart js (KaiAdmin biasanya pakai Chart.js) -->
                    <div class="chart-container" style="min-height: 300px;">
                        <canvas id="salesChartDummy"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Produk Terlaris -->
        <div class="col-md-4">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-title">Produk Terpopuler</div>
                </div>
                <div class="card-body pb-0">
                    <!-- Item 1 -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar avatar-sm me-3">
                            <span class="avatar-title rounded-circle bg-primary text-white">ML</span>
                        </div>
                        <div class="flex-1">
                            <h6 class="fw-bold mb-0">Mobile Legends</h6>
                            <small class="text-muted">86 Pilihan Produk Terjual</small>
                        </div>
                        <div class="text-end">
                            <span class="badge badge-success">Top 1</span>
                        </div>
                    </div>
                    <!-- Item 2 -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar avatar-sm me-3">
                            <span class="avatar-title rounded-circle bg-warning text-white">FF</span>
                        </div>
                        <div class="flex-1">
                            <h6 class="fw-bold mb-0">Free Fire</h6>
                            <small class="text-muted">45 Pilihan Produk Terjual</small>
                        </div>
                        <div class="text-end">
                            <span class="badge badge-info">Top 2</span>
                        </div>
                    </div>
                    <!-- Item 3 -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar avatar-sm me-3">
                            <span class="avatar-title rounded-circle bg-danger text-white">GI</span>
                        </div>
                        <div class="flex-1">
                            <h6 class="fw-bold mb-0">Genshin Impact</h6>
                            <small class="text-muted">11 Pilihan Produk Terjual</small>
                        </div>
                        <div class="text-end">
                            <span class="badge badge-secondary">Top 3</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW 3: TABEL TRANSAKSI TERAKHIR -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row card-tools-still-right">
                        <div class="card-title">Riwayat Transaksi Terbaru</div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <!-- Tabel Transaksi -->
                        <table class="table align-items-center mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Invoice</th>
                                    <th>Game / Produk</th>
                                    <th>Target ID</th>
                                    <th>Harga</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><a href="#" class="text-primary fw-bold">#INV-20260531-01</a></td>
                                    <td>Mobile Legends - 86 Diamonds</td>
                                    <td>12345678 (2134)</td>
                                    <td>Rp 20.500</td>
                                    <td><span class="badge badge-success">Sukses</span></td>
                                </tr>
                                <tr>
                                    <td><a href="#" class="text-primary fw-bold">#INV-20260531-02</a></td>
                                    <td>Free Fire - 140 Diamonds</td>
                                    <td>987654321</td>
                                    <td>Rp 19.000</td>
                                    <td><span class="badge badge-warning">Pending</span></td>
                                </tr>
                                <tr>
                                    <td><a href="#" class="text-primary fw-bold">#INV-20260531-03</a></td>
                                    <td>Genshin Impact - Blessing of the Welkin Moon</td>
                                    <td>80442115 (Asia)</td>
                                    <td>Rp 79.000</td>
                                    <td><span class="badge badge-success">Sukses</span></td>
                                </tr>
                                <tr>
                                    <td><a href="#" class="text-primary fw-bold">#INV-20260531-04</a></td>
                                    <td>PUBG Mobile - 60 Unknown Cash</td>
                                    <td>55123498</td>
                                    <td>Rp 14.500</td>
                                    <td><span class="badge badge-danger">Gagal</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
<!-- END: CONTENT UTAMA DASHBOARD -->

</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
    var ctx = document.getElementById('salesChartDummy').getContext('2d');
    var salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"],
            datasets: [{
                label: "Pendapatan (Rp)",
                borderColor: "#1d7af3",
                pointBorderColor: "#FFF",
                pointBackgroundColor: "#1d7af3",
                pointBorderWidth: 2,
                pointHoverRadius: 4,
                pointHoverBorderWidth: 1,
                pointRadius: 4,
                backgroundColor: 'transparent',
                fill: true,
                borderWidth: 2,
                data: [1200000, 1500000, 1100000, 2000000, 1800000, 2900000, 2450000]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
        }
    });
});
</script>
@endsection