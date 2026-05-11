@extends('layouts.tenant')

@section('content')
<div class="page-inner">
    {{-- HEADER (Sembunyi saat print) --}}
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-3 pb-4 no-print">
        <div>
            <h3 class="fw-bold mb-1 text-primary">Detail Surat Izin</h3>
            <p class="text-muted mb-0">Informasi lengkap mengenai status perizinan santri.</p>
        </div>
        <div class="ms-md-auto ml-md-auto py-2 py-md-0">
            <div class="d-flex flex-row align-items-center justify-content-md-end">
                <button onclick="window.print()" class="btn btn-dark btn-round mr-2 me-2">
                    <i class="fa fa-print mr-2 me-2"></i> Cetak Struk Saku
                </button>
                <a href="{{ route('tenant.perizinan.index') }}" class="btn btn-outline-secondary btn-round">
                    <i class="fa fa-arrow-left mr-2 me-2"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4 no-print" role="alert">
            <i class="fa fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    {{-- TAMPILAN DASHBOARD (Sembunyi saat print) --}}
    <div class="row no-print">
        <div class="col-md-8">
            <div class="card card-round border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title fw-bold text-uppercase" style="letter-spacing: 1px;">Data Perizinan</h5>
                        <span class="text-muted small">#{{ $perizinan->kode_surat }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless table-striped-rows">
                            <tbody>
                                <tr>
                                    <th width="30%" class="text-muted pl-0">Nama Lengkap</th>
                                    <td class="fw-bold text-dark">: {{ $perizinan->santri->nama_lengkap }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted pl-0">NIS / ID</th>
                                    <td class="text-dark">: {{ $perizinan->santri->nis }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted pl-0">Nomor Surat (Manual)</th>
                                    <td class="text-dark">: {{ $perizinan->nomor_manual ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted pl-0">Keperluan</th>
                                    <td class="text-dark">: <span class="badge badge-info">{{ $perizinan->keperluan ?? 'Lainnya' }}</span></td>
                                </tr>
                                <tr>
                                    <th class="text-muted pl-0">Waktu Keluar</th>
                                    <td class="text-dark">: {{ $perizinan->tanggal_keluar->format('d M Y - H:i') }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted pl-0">Batas Kembali</th>
                                    <td class="fw-bold text-{{ $perizinan->status == 'aktif' && now()->gt($perizinan->batas_kembali) ? 'danger' : 'primary' }}">
                                        : {{ $perizinan->batas_kembali->format('d M Y - H:i') }}
                                    </td>
                                </tr>
                                @php
                                    $isOverdue = $perizinan->status == 'aktif' && now()->gt($perizinan->batas_kembali);
                                @endphp
                                <tr>
                                    <th class="text-muted pl-0">Status Perizinan</th>
                                    <td class="pl-2">
                                        @if($isOverdue)
                                            <span class="badge badge-danger px-3 py-2 rounded-pill shadow-sm pulse-text">
                                                <i class="fas fa-exclamation-triangle mr-1"></i> Terlambat 
                                                ({{ now()->diffForHumans($perizinan->batas_kembali, true) }})
                                            </span>
                                        @elseif($perizinan->status == 'aktif')
                                            <span class="badge badge-primary px-3 py-2 rounded-pill shadow-sm">
                                                <i class="fas fa-walking mr-1"></i> Aktif (Diluar)
                                            </span>
                                        @elseif($perizinan->status == 'kembali')
                                            <span class="badge badge-success px-3 py-2 rounded-pill shadow-sm">
                                                <i class="fas fa-check-circle mr-1"></i> Sudah Kembali
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted pl-0">Keterangan</th>
                                    <td class="text-dark">: {{ $perizinan->keterangan ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-round border-0 shadow-sm bg-primary-gradient text-white mb-4">
                <div class="card-body text-center py-4">
                    <h5 class="fw-bold mb-3">QR Code Validation</h5>
                    <div class="qr-section">
                        {{-- Cukup generate kodenya saja, jangan URL-nya --}}
                        {!! QrCode::size(150)->margin(1)->generate($perizinan->kode_surat) !!}
                        
                        <p class="kode-surat">{{ $perizinan->kode_surat }}</p>
                    </div>
                    <p class="small op-8 mb-0 px-3">Scan untuk memproses kepulangan santri secara otomatis.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- FORMAT PRINT STRUK (Muncul hanya saat print) --}}
    <div class="print-struk-container">
        <div class="struk-header">
            <h4 class="fw-bold">SURAT IZIN SANTRI</h4>
            <p>Pon-Pes Al-Fithroh</p>
            <div class="divider"></div>
        </div>
        
        <div class="struk-body">
            <div class="qr-section">
                {!! QrCode::size(150)->margin(1)->generate(route('tenant.perizinan.scan', $perizinan->kode_surat)) !!}
                <p class="kode-surat">{{ $perizinan->kode_surat }}</p>
            </div>

            <table class="table-struk">
                <tr>
                    <td width="35%">Santri</td>
                    <td>: {{ $perizinan->santri->nama_lengkap }}</td>
                </tr>
                <tr>
                    <td>Keluar</td>
                    <td>: {{ $perizinan->tanggal_keluar->format('d/m/y H:i') }}</td>
                </tr>
                <tr>
                    <td class="fw-bold">Kembali</td>
                    <td class="fw-bold">: {{ $perizinan->batas_kembali->format('d/m/y H:i') }}</td>
                </tr>
                <tr>
                    <td>Keperluan</td>
                    <td>: {{ $perizinan->keperluan ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <div class="struk-footer text-center">
            <div class="divider"></div>
            <p>Simpan surat ini untuk proses scan masuk.</p>
            <p class="tgl-cetak">Dicetak: {{ now()->format('d/m/y H:i') }}</p>
        </div>
    </div>
</div>

<style>
    /* TAMPILAN DASHBOARD */
    .table-striped-rows tbody tr { border-bottom: 1px solid #f8f9fa; }
    .table-striped-rows th { font-weight: 500; font-size: 0.9rem; }
    .bg-primary-gradient { background: linear-gradient(-45deg, #06418F, #1572E8) !important; }
    .rounded-lg { border-radius: 15px !important; }
    .pulse-text { animation: pulse-red 2s infinite; }
    @keyframes pulse-red {
        0% { transform: scale(0.98); opacity: 1; }
        50% { transform: scale(1); opacity: 0.7; }
        100% { transform: scale(0.98); opacity: 1; }
    }

    /* SEMBUNYIKAN STRUK DI LAYAR */
    .print-struk-container { display: none; }

    /* --- LOGIKA PRINT FIXED --- */
    @media print {
        /* 1. Sembunyikan SEMUA elemen dasar website */
        nav, .sidebar, .main-header, .footer, .no-print, .btn, .alert {
            display: none !important;
        }

        /* 2. Paksa container utama untuk tidak membatasi konten */
        body, .wrapper, .main-panel, .content, .page-inner {
            display: block !important;
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
            width: 100% !important;
            overflow: visible !important;
        }

        /* 3. Sembunyikan Row Dashboard yang ada di dalam page-inner */
        .page-inner > .row, .page-inner > .d-flex {
            display: none !important;
        }

        /* 4. Tampilkan Struk dengan Paksaan (Position Absolute) */
        .print-struk-container {
            display: block !important;
            position: absolute;
            left: 0;
            top: 0;
            width: 80mm; /* Lebar saku */
            background: white !important;
            padding: 10px;
            font-family: 'Courier New', Courier, monospace;
            color: black !important;
        }

        /* Styling elemen dalam struk */
        .struk-header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 5px; }
        .struk-header h4 { margin: 0; font-size: 16px; font-weight: bold; }
        .qr-section { text-align: center; margin: 15px 0; }
        .table-struk { width: 100%; font-size: 12px; border-collapse: collapse; }
        .table-struk td { padding: 4px 0; }
        .divider { border-top: 1px dashed #000; margin: 10px 0; }
        .struk-footer { text-align: center; font-size: 10px; margin-top: 10px; }
    }
</style>
@endsection