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
            background-color: #f1f5f9;
            color: #1e293b;
        }
        .hero-section {
            background: linear-gradient(135deg, #0f172a 0%, #1e40af 100%);
            padding: 80px 0;
            margin-bottom: -80px;
            color: white;
            border-radius: 0 0 40px 40px;
        }
        .main-card {
            border: none;
            border-radius: 24px;
            background: #ffffff;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .btn-download-primary {
            background: #2563eb;
            border: none;
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-download-primary:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.4);
        }
        .btn-preview-outline {
            border: 2px solid #e2e8f0;
            color: #475569;
            padding: 12px 28px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-preview-outline:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #1e293b;
        }
        .table-custom {
            border-collapse: separate;
            border-spacing: 0 12px;
        }
        .table-custom tbody tr {
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: all 0.2s;
        }
        .table-custom tbody tr:hover {
            transform: scale(1.005);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        }
        .table-custom td {
            padding: 20px !important;
            border: none !important;
        }
        .version-tag {
            background: #eff6ff;
            color: #1d4ed8;
            padding: 6px 14px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.85rem;
            border: 1px solid #dbeafe;
        }
        /* Style untuk Preview Modal */
        .modal-full {
            max-width: 95%;
            height: 90vh;
        }
        .preview-iframe {
            width: 100%;
            height: 75vh;
            border: none;
            border-radius: 8px;
            background: #f8fafc;
        }
    </style>
</head>
<body>

<div class="hero-section">
    <div class="container text-center px-4">
        <h1 class="fw-800 display-6 mb-3">Pusat Informasi Santri</h1>
        <p class="opacity-75 lead mx-auto" style="max-width: 600px;">Akses dokumen pedoman dan tata tertib pondok pesantren versi digital terbaru.</p>
    </div>
</div>

<div class="container py-5">
    @if($latest)
    <div class="row justify-content-center mb-5">
        <div class="col-lg-9">
            <div class="card main-card border-0">
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill mb-3">
                                <i class="fas fa-check-circle me-1"></i> Versi Terbaru Tersedia
                            </span>
                            <h2 class="fw-bold mb-2">Buku Pedoman Santri</h2>
                            <p class="text-muted mb-4">
                                <i class="far fa-calendar-check me-2"></i>Terakhir diperbarui: <strong>{{ $latest->release_date->format('d F Y') }}</strong>
                            </p>
                            
                            <div class="d-flex flex-column flex-sm-row gap-3">
                                <a href="{{ route('public.handbook.download', $latest->id) }}" class="btn btn-download-primary text-white">
                                    <i class="fas fa-download me-2"></i>Download PDF
                                </a>
                                <button type="button" class="btn btn-preview-outline" data-bs-toggle="modal" data-bs-target="#previewModal">
                                    <i class="fas fa-eye me-2"></i>Lihat Online
                                </button>
                            </div>
                        </div>
                        <div class="col-md-5 d-none d-md-block text-center text-primary">
                            <div class="position-relative">
                                <i class="fas fa-file-pdf fa-8x opacity-10"></i>
                                <div class="position-absolute top-50 start-50 translate-middle">
                                    <span class="h4 fw-bold text-dark">v{{ $latest->version }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-full">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header border-0 px-4 pt-4">
                    <h5 class="modal-title fw-bold">Live Preview: Pedoman v{{ $latest->version }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <iframe class="preview-iframe" src="{{ route('handbook.preview', $latest->id) }}"></iframe>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <p class="small text-muted me-auto">Dokumen ini bersifat resmi. Silakan download untuk akses offline.</p>
                    <a href="{{ route('public.handbook.download', $latest->id) }}" class="btn btn-primary rounded-pill px-4">Download PDF</a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row justify-content-center mt-4">
        <div class="col-lg-10">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="fw-bold m-0"><i class="fas fa-clock-rotate-left me-2 text-primary"></i>Riwayat Rilis</h5>
                <span class="badge bg-white text-dark shadow-sm border px-3 py-2 rounded-pill small">{{ $history->count() }} Versi</span>
            </div>

            <div class="table-responsive">
                <table class="table table-custom align-middle">
                    <thead>
                        <tr class="text-muted small text-uppercase fw-bold">
                            <th class="px-4 border-0">Versi</th>
                            <th class="border-0">Tanggal</th>
                            <th class="border-0 d-none d-md-table-cell">Keterangan</th>
                            <th class="text-end px-4 border-0">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $item)
                        <tr class="rounded-4">
                            <td class="px-4">
                                <span class="version-tag">v{{ $item->version }}</span>
                            </td>
                            <td>
                                <div class="small fw-semibold text-dark">{{ $item->release_date->format('d M Y') }}</div>
                                @if($item->status === 'published')
                                    <span class="text-success" style="font-size: 10px;"><i class="fas fa-circle me-1" style="font-size: 7px;"></i> Aktif</span>
                                @endif
                            </td>
                            <td class="d-none d-md-table-cell">
                                <span class="text-muted small">{{ Str::limit($item->description ?? 'Pembaruan sistem dan tata bahasa', 60) }}</span>
                            </td>
                            <td class="text-end px-4">
                                <a href="{{ route('public.handbook.download', $item->id) }}" class="btn btn-sm btn-light rounded-pill px-3 fw-bold text-primary border">
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

<footer class="text-center py-5 text-muted small">
    <hr class="w-25 mx-auto mb-4">
    <p>&copy; {{ date('Y') }} <strong>Manajemen Pondok</strong> • Divisi Digital Digital Library</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>