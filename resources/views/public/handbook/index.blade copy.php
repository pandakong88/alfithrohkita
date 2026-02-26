<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Pedoman Santri | Digital Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .hero-section {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            padding: 60px 0;
            margin-bottom: -100px;
            color: white;
            border-radius: 0 0 50px 50px;
        }
        .main-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .main-card:hover {
            transform: translateY(-5px);
        }
        .btn-download-primary {
            background: #1e40af;
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-download-primary:hover {
            background: #1e3a8a;
            box-shadow: 0 10px 20px rgba(30, 64, 175, 0.2);
            transform: scale(1.02);
        }
        .badge-status {
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 500;
        }
        .table-custom {
            border-collapse: separate;
            border-spacing: 0 10px;
        }
        .table-custom tbody tr {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            border-radius: 10px;
            transition: all 0.2s;
        }
        .table-custom tbody tr td {
            padding: 20px;
            border: none;
            vertical-align: middle;
        }
        .table-custom tbody tr td:first-child { border-radius: 10px 0 0 10px; }
        .table-custom tbody tr td:last-child { border-radius: 0 10px 10px 0; }
        
        .version-tag {
            background: #e0e7ff;
            color: #4338ca;
            padding: 4px 12px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="hero-section shadow-lg">
    <div class="container text-center">
        <h1 class="fw-bold mb-2"><i class="fas fa-book-open me-2"></i> Pusat Pedoman Santri</h1>
        <p class="opacity-75">Akses dokumen tata tertib dan panduan akademik terbaru secara digital</p>
    </div>
</div>

<div class="container py-5">
    @if($latest)
    <div class="row justify-content-center mb-5">
        <div class="col-lg-8">
            <div class="card main-card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="d-md-flex align-items-center justify-content-between">
                        <div>
                            <span class="badge bg-success badge-status mb-3">
                                <i class="fas fa-check-circle me-1"></i> Versi Berlaku Saat Ini
                            </span>
                            <h2 class="fw-bold mb-1">Versi {{ $latest->version }}</h2>
                            <p class="text-muted">
                                <i class="far fa-calendar-alt me-1"></i> Rilis pada: {{ $latest->release_date->format('d F Y') }}
                            </p>
                            <div class="mt-4 d-flex gap-2">
                                <a href="{{ route('public.handbook.download', $latest->id) }}" class="btn btn-download-primary btn-lg text-white">
                                    <i class="fas fa-cloud-download-alt me-2"></i> Download Sekarang
                                </a>
                            </div>
                        </div>
                        <div class="d-none d-md-block text-primary">
                            <i class="fas fa-file-pdf fa-7x opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h4 class="fw-bold m-0"><i class="fas fa-history me-2 text-primary"></i> Arsip Versi Sebelumnya</h4>
                <span class="text-muted small">Total {{ $history->count() }} Dokumen</span>
            </div>

            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr class="text-muted">
                            <th class="px-4">VERSI</th>
                            <th>TANGGAL RILIS</th>
                            <th>STATUS</th>
                            <th>KETERANGAN</th>
                            <th class="text-end px-4">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $item)
                        <tr>
                            <td class="px-4">
                                <span class="version-tag">{{ $item->version }}</span>
                            </td>
                            <td>
                                <span class="text-secondary small fw-bold">{{ $item->release_date->format('d M Y') }}</span>
                            </td>
                            <td>
                                @if($item->status === 'published')
                                    <span class="text-success small fw-bold">
                                        <i class="fas fa-dot-circle me-1"></i> Aktif
                                    </span>
                                @else
                                    <span class="text-muted small fw-bold">
                                        <i class="fas fa-archive me-1"></i> Diarsipkan
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted small text-truncate d-inline-block" style="max-width: 200px;">
                                    {{ $item->description ?? 'Tidak ada keterangan' }}
                                </span>
                            </td>
                            <td class="text-end px-4">
                                <a href="{{ route('public.handbook.download', $item->id) }}" 
                                   class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="fas fa-download me-1"></i> Unduh
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<footer class="text-center py-4 mt-5 text-muted small">
    &copy; {{ date('Y') }} Manajemen Pondok Pesantren Digital. All rights reserved.
</footer>

</body>
</html>