@extends('layouts.tenant')

@section('content')
{{-- Import Font, Icons & Animasi --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

<div class="page-inner" style="background: #f8fafc; min-height: 100vh; font-family: 'Plus Jakarta Sans', sans-serif;">
    
    {{-- HEADER SECTION --}}
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-3 pb-4">
        <div>
            <h3 class="fw-bold mb-1 text-primary">Monitoring Perizinan</h3>
            <p class="text-muted mb-0">Kelola dan pantau keluar-masuk santri secara real-time.</p>
        </div>

        <div class="ms-md-auto py-2 py-md-0">
            <div class="d-flex flex-row align-items-center justify-content-end">
                <div class="header-search mr-3 me-3">
                    <div class="input-group quick-scan-group shadow-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0">
                                <i class="fas fa-qrcode text-muted"></i>
                            </span>
                        </div>
                        <input type="text" id="quickScanInput" class="form-control border-left-0" placeholder="Scan Kode Surat..." style="min-width: 200px;">
                        <div class="input-group-append">
                            <button class="btn btn-dark fw-bold px-3" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <a href="{{ route('tenant.perizinan.create') }}" class="btn btn-primary btn-round px-4 shadow-sm fw-bold">
                    <i class="fas fa-plus-circle mr-2 me-2"></i> Buat Izin Baru
                </a>
            </div>
        </div>
    </div>

    {{-- STATS WIDGET --}}
    <div class="row">
        <div class="col-sm-6 col-md-4">
            <div class="card card-stats card-round border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small" style="background: rgba(29, 122, 243, 0.1); color: #1d7af3; border-radius: 15px;">
                                <i class="fas fa-walking"></i>
                            </div>
                        </div>
                        <div class="col col-stats ml-3 ms-3">
                            <div class="numbers">
                                <p class="card-category text-uppercase fw-bold text-muted small">Sedang Izin</p>
                                <h4 class="card-title fw-bold">{{ $perizinans->where('status', 'aktif')->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="card card-stats card-round border-0 shadow-sm bg-danger-gradient">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center bg-white bg-opacity-20 rounded-pill">
                                <i class="fas fa-user-clock text-white"></i>
                            </div>
                        </div>
                        <div class="col col-stats ml-3 ms-3">
                            <div class="numbers">
                                <p class="card-category text-white-50 text-uppercase fw-bold small">Terlambat</p>
                                <h4 class="card-title fw-bold text-white">
                                    {{ $perizinans->where('status', 'aktif')->filter(fn($p) => now()->gt($p->batas_kembali))->count() }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="card card-stats card-round border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-success bubble-shadow-small" style="background: rgba(49, 175, 80, 0.1); color: #31af50; border-radius: 15px;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="col col-stats ml-3 ms-3">
                            <div class="numbers">
                                <p class="card-category text-uppercase fw-bold text-success small">Kembali Hari Ini</p>
                                <h4 class="card-title text-success fw-bold">{{ $perizinans->where('status', 'kembali')->where('updated_at', '>=', now()->startOfDay())->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DATA TABLE --}}
    <div class="card card-round border-0 shadow-sm mt-3" style="border-radius: 20px;">
        <div class="card-body">
            <div class="table-responsive">
                <table id="perizinanTable" class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr class="text-uppercase small fw-bold text-muted">
                            <th class="border-0">Santri</th>
                            <th class="text-center border-0">Waktu Keluar</th>
                            <th class="text-center border-0">Batas Kembali</th>
                            <th class="text-center border-0">Status</th>
                            <th class="text-end border-0">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($perizinans as $p)
                        @php $isOverdue = $p->status == 'aktif' && now()->gt($p->batas_kembali); @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm mr-3 me-3">
                                        <span class="avatar-title rounded-circle bg-light-primary text-primary fw-bold">{{ substr($p->santri->nama_lengkap, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $p->santri->nama_lengkap }}</div>
                                        <span class="text-muted small">#{{ $p->kode_surat }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center small text-muted">{{ $p->tanggal_keluar->format('d M, H:i') }}</td>
                            <td class="text-center">
                                <span class="small {{ $isOverdue ? 'text-danger fw-bold' : 'text-dark fw-bold' }}">
                                    {{ $p->batas_kembali->format('d M, H:i') }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $p->status == 'kembali' ? 'badge-success' : ($isOverdue ? 'badge-danger' : 'badge-primary') }} rounded-pill px-3">
                                    {{ $p->status == 'kembali' ? 'Kembali' : ($isOverdue ? 'Terlambat' : 'Aktif') }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button onclick="openRiwayatModal({{ $p->santri_id }}, '{{ $p->santri->nama_lengkap }}')" class="btn btn-icon btn-round btn-sm btn-label-warning mx-1" title="Riwayat">
                                    <i class="fa fa-history"></i>
                                </button>
                                <a href="{{ route('tenant.perizinan.show', $p->id) }}" class="btn btn-icon btn-round btn-sm btn-label-info mx-1" title="Detail">
                                    <i class="fa fa-file-alt"></i>
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

{{-- MODAL RIWAYAT AUDIT VERSION --}}
<div class="modal fade" id="modalRiwayat" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg" style="border-radius: 24px; border: none; overflow: hidden;">
            <div class="modal-header d-flex align-items-center p-4" style="background: #1a2035; border-bottom: 3px solid #1d7af3;">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md me-3 mr-3">
                        <span class="avatar-title rounded-circle bg-primary text-white shadow-sm">
                            <i class="fas fa-history"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0">Log Aktivitas Santri</h5>
                        <div class="mt-1">
                            <span id="namaSantriModal" class="fw-bold px-3 py-1 text-warning" style="background: rgba(255,255,255,0.1); border-radius: 8px; font-size: 0.9rem; border: 1px solid rgba(255,255,255,0.2);"></span>
                        </div>
                    </div>
                </div>
                <button type="button" class="close text-white opacity-1 ml-auto ms-auto" data-dismiss="modal" style="font-size: 2rem; border:none; background:none; outline: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-0">
                <div class="row no-gutters g-0">
                    <div class="col-lg-7 p-4 bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0"><i class="fas fa-calendar-alt text-primary me-2"></i> Kalender Audit</h6>
                            <div class="small p-2 bg-light rounded-pill px-3">
                                <span class="badge rounded-circle bg-success" style="width: 8px; height: 8px; display: inline-block;"></span> <small class="text-dark fw-bold mr-2">Selesai</small>
                                <span class="badge rounded-circle bg-primary" style="width: 8px; height: 8px; display: inline-block;"></span> <small class="text-dark fw-bold">Aktif</small>
                            </div>
                        </div>
                        <div id="calendar" class="modern-calendar p-2 border rounded shadow-sm bg-white"></div>
                    </div>

                    <div class="col-lg-5 p-4" style="background: #f4f6f9; border-left: 1px solid #e0e0e0;">
                        <h6 class="fw-bold text-dark mb-4 d-flex align-items-center">
                            <i class="fas fa-stream text-primary mr-2 me-2"></i> Ringkasan Izin di Bulan Ini
                        </h6>
                        <div id="timelineContainer" class="custom-scrollbar" style="max-height: 480px; overflow-y: auto; padding: 5px;">
                            {{-- JS Generated Content --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .quick-scan-group { border-radius: 50px !important; overflow: hidden; border: 1px solid #ebedf2; background: #fff; }
    .quick-scan-group input { border: none !important; }
    .bg-light-primary { background-color: #f0f4ff; }
    .btn-label-warning { background: #fff4e1; color: #ffad46 !important; }
    .btn-label-info { background: #e1f0ff; color: #1d7af3 !important; }
    .bg-danger-gradient { background: linear-gradient(135deg, #f25961 0%, #ff7976 100%) !important; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
    .card-timeline { border-radius: 12px !important; transition: all 0.3s ease; background: #fff; border: 1px solid #e2e8f0; }
    .fc .fc-toolbar-title { font-size: 1rem !important; font-weight: 800; }
    .fc .fc-button-primary { background: #fff !important; color: #1d7af3 !important; border: 1px solid #e2e8f0 !important; }
    .fc-event { cursor: pointer; border: none !important; }
</style>

@push('scripts')
<script src="https://unpkg.com/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
    let calendar;

    function openRiwayatModal(santriId, namaSantri) {
        $('#namaSantriModal').text(namaSantri);
        $('#modalRiwayat').modal('show');
        
        const calendarEl = document.getElementById('calendar');
        if (calendar) calendar.destroy();

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            height: 480,
            headerToolbar: { left: 'prev,next today', center: 'title', right: '' },
            events: "/dashboard/perizinan/data-riwayat/" + santriId,
            
            displayEventTime: true,
            dayMaxEvents: true,
            nextDayThreshold: '00:00:00',
            eventOrder: "start",

            // SINKRONISASI AWAL: Begitu data masuk pertama kali
            eventSourceSuccess: function(content, xhr) {
                const now = calendar.getDate(); // Ambil tanggal aktif kalender
                setTimeout(() => {
                    renderAuditTimeline(calendar.getEvents(), now.getMonth() + 1, now.getFullYear());
                }, 100);
            },

            // SINKRONISASI SAAT PINDAH BULAN
            datesSet: function(info) {
                const targetMonth = info.view.currentStart.getMonth() + 1;
                const targetYear = info.view.currentStart.getFullYear();
                
                // Gunakan filter yang sudah ada
                setTimeout(() => {
                    renderAuditTimeline(calendar.getEvents(), targetMonth, targetYear);
                }, 300);
            }
        });
        calendar.render();
    }

    function renderAuditTimeline(events, month, year) {
        const monthNames = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        
        // FILTER SANGAT KETAT: Cek Bulan DAN Tahun
        const filtered = events.filter(ev => {
            const props = ev.extendedProps;
            // Kita bandingkan string untuk menghindari masalah tipe data JS
            return String(props.month) === String(month) && String(props.year) === String(year);
        });

        let html = `<div class="mb-3 text-center"><span class="badge bg-primary px-3 shadow-sm">Audit: ${monthNames[month]} ${year}</span></div>`;
        
        if (filtered.length === 0) {
            html += `
                <div class="text-center p-5 animate__animated animate__fadeIn">
                    <i class="fas fa-calendar-times fa-3x text-light mb-3" style="opacity:0.3;"></i>
                    <p class="text-muted small">Tidak ada data izin untuk periode ini.</p>
                </div>`;
        } else {
            // Sortir terbaru ke terlama
            filtered.sort((a, b) => b.start - a.start).forEach((ev, i) => {
                const p = ev.extendedProps;
                html += `
                    <div class="card shadow-sm mb-2 card-timeline animate__animated animate__fadeInUp" 
                         style="border-left: 4px solid ${ev.backgroundColor}; animation-delay: ${i*30}ms; background:#fff;">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <b style="font-size:12px; color:#1a2035;">${ev.title}</b>
                                <span class="badge badge-light border" style="font-size:9px;">${p.kode}</span>
                            </div>
                            <div class="small text-muted mt-2" style="font-size:11px;">
                                <i class="far fa-calendar-check mr-1 text-primary"></i> ${p.tgl_indo}
                                <br>
                                <i class="far fa-clock mr-1 text-primary"></i> ${p.jam}
                            </div>
                        </div>
                    </div>`;
            });
        }
        $('#timelineContainer').html(html);
    }
</script>
@endpush
@endsection