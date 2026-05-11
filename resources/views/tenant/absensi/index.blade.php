@extends('layouts.tenant')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title text-bold">Presensi Dashboard <span class="badge badge-primary ml-2">{{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}</span></h4>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <div class="row align-items-end">
                        <div class="col-lg-6 col-md-7">
                            <form method="GET" action="{{ route('tenant.absensi.index', ['sesi_id' => $sesi_id]) }}">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label small font-weight-bold text-muted">BULAN</label>
                                        <select name="bulan" class="form-control custom-select-sm border-primary" onchange="this.form.submit()">
                                            @foreach(range(1, 12) as $m)
                                                <option value="{{ sprintf('%02d', $m) }}" {{ $bulan == $m ? 'selected' : '' }}>
                                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small font-weight-bold text-muted">TAHUN</label>
                                        <select name="tahun" class="form-control custom-select-sm border-primary" onchange="this.form.submit()">
                                            @foreach(range(date('Y')-1, date('Y')+1) as $y)
                                                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-lg-6 col-md-5 mt-3 mt-md-0">
                            <label class="form-label small font-weight-bold text-muted">PENCARIAN</label>
                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <i class="fa fa-search text-primary"></i>
                                </span>
                                <input type="text" id="searchSantri" class="form-control border-primary pill" placeholder="Cari Nama atau NIS...">
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 10px;">
                                <div class="d-flex flex-wrap">
                                    <div class="legend-item"><span class="legend-box bg-hadir"></span> <small>H: Hadir</small></div>
                                    <div class="legend-item"><span class="legend-box bg-sakit"></span> <small>S: Sakit</small></div>
                                    <div class="legend-item"><span class="legend-box bg-izin"></span> <small>I: Izin</small></div>
                                    <div class="legend-item"><span class="legend-box bg-alfa"></span> <small>A: Alfa</small></div>
                                    <div class="legend-item"><span class="legend-box bg-terlambat"></span> <small>T: Terlambat</small></div>
                                </div>
                                <div class="text-muted small d-flex align-items-center">
                                    <a href="{{ route('tenant.absensi.print', ['sesi_id' => $sesi_id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" 
                                        target="_blank" 
                                        class="btn btn-outline-primary btn-sm mr-3 shadow-sm">
                                         <i class="fas fa-print mr-1"></i> Print Laporan
                                     </a>
                                    
                                    <span class="badge badge-light border text-dark">
                                        <i class="fas fa-info-circle text-primary mr-1"></i> 
                                        Klik kiri: Status | Klik kanan: Catatan
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive table-premium-wrapper">
                        <table class="table table-premium m-0">
                            <thead>
                                <tr>
                                    <th class="sticky-col-header main-header">Data Santri</th>
                                    @for($i = 1; $i <= $daysInMonth; $i++)
                                        @php $dateObj = \Carbon\Carbon::create($tahun, $bulan, $i); @endphp
                                        <th class="tgl-header {{ $dateObj->isWeekend() ? 'bg-light-weekend' : '' }}">
                                            <div class="d-day">{{ $dateObj->translatedFormat('D') }}</div>
                                            <div class="d-date">{{ $i }}</div>
                                        </th>
                                    @endfor
                                    <th class="rekap-header text-center bg-light border-left">(H/S/I/A/T)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($santris as $santri)
                                @php
                                    // Hitung rekap awal dari data yang ada
                                    $h = $santri->absensis->where('status', 'hadir')->count();
                                    $s = $santri->absensis->where('status', 'sakit')->count();
                                    $i = $santri->absensis->where('status', 'izin')->count();
                                    $a = $santri->absensis->where('status', 'alfa')->count();
                                    $t = $santri->absensis->where('status', 'terlambat')->count();
                                @endphp
                                <tr>
                                    <td class="sticky-col-body">
                                        <div class="d-flex align-items-center">
                                            <div class="name-initial">{{ strtoupper(substr($santri->nama_lengkap, 0, 2)) }}</div>
                                            <div class="ml-2">
                                                <div class="s-name">{{ $santri->nama_lengkap }}</div>
                                                <div class="s-nis">{{ $santri->nis ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                            
                                    @for($day = 1; $day <= $daysInMonth; $day++)
                                        @php
                                            $tglString = $tahun . '-' . $bulan . '-' . sprintf('%02d', $day);
                                            $dataAbsen = $santri->absensis->firstWhere('tanggal', $tglString);
                                            $status = $dataAbsen ? $dataAbsen->status : 'hadir';
                                            $shortStatus = ['hadir' => 'H', 'sakit' => 'S', 'izin' => 'I', 'alfa' => 'A', 'terlambat' => 'T'];
                                        @endphp
                                        <td class="cell-action p-0" 
                                            data-santri="{{ $santri->id }}" 
                                            data-tgl="{{ $tglString }}" 
                                            data-current="{{ $status }}"
                                            data-catatan="{{ $dataAbsen ? $dataAbsen->keterangan : '' }}">
                                            <div class="status-box status-{{ $status }}">
                                                {{ $shortStatus[$status] }}
                                            </div>
                                            @if($dataAbsen && $dataAbsen->keterangan)
                                                <div class="note-indicator"></div>
                                            @endif
                                        </td>
                                    @endfor
                            
                                    <td class="text-center border-left bg-light-rekap" style="min-width: 140px; vertical-align: middle;">
                                        <div class="d-flex justify-content-center rekap-container" id="rekap-{{ $santri->id }}">
                                            <span class="counter-item counter-hadir text-success" title="Hadir">{{ $h }}</span>
                                            <span class="counter-item counter-sakit text-warning" title="Sakit">{{ $s }}</span>
                                            <span class="counter-item counter-izin text-info" title="Izin">{{ $i }}</span>
                                            <span class="counter-item counter-alfa text-danger" title="Alfa">{{ $a }}</span>
                                            <span class="counter-item counter-terlambat text-secondary" title="Terlambat">{{ $t }}</span>
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

<div class="modal fade" id="modalCatatan" tabindex="-1" aria-labelledby="modalCatatanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header bg-primary-gradient text-white border-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="icon-shape bg-white text-primary mr-3 shadow-sm">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0" id="modalCatatanLabel">Detail Keterangan</h5>
                        <small class="text-white-50" id="display_info_modal">Tambahkan alasan absensi santri</small>
                    </div>
                </div>
                <button type="button" class="close text-white opacity-100" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="outline: none; background: none; border: none;">
                    <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                </button>
            </div>

            <div class="modal-body p-4 bg-white">
                <input type="hidden" id="modal_tgl">
                <input type="hidden" id="modal_santri">
                
                <div class="form-group p-0 mb-0">
                    <label class="text-uppercase small font-weight-bold text-muted mb-2 d-block" style="letter-spacing: 1px;">
                        <i class="fas fa-sticky-note mr-1"></i> Catatan Absensi
                    </label>
                    <textarea id="catatan_input" 
                              class="form-control border-2 custom-focus" 
                              rows="4" 
                              placeholder="Ketikkan alasan di sini (misal: Sakit tipes, Izin pulang kampung)..."
                              style="border-radius: 10px; border: 2px solid #f1f4f8; resize: none; padding: 12px;"></textarea>
                </div>
            </div>

            <div class="modal-footer border-0 bg-light p-3">
                <button type="button" class="btn btn-link text-muted font-weight-bold" data-dismiss="modal" data-bs-dismiss="modal" style="text-decoration: none;">Batal</button>
                <button type="button" id="btnSimpanCatatan" class="btn btn-primary px-4 shadow-sm font-weight-bold" style="border-radius: 8px;">
                    <i class="fas fa-save mr-1"></i> Simpan Catatan
                </button>
            </div>
        </div>
    </div>
</div>
<style>
    /* UI Premium Styles */
    .table-premium-wrapper { max-height: 72vh; overflow: auto; position: relative; border-radius: 8px; border: 1px solid #edf2f7; }
    .table-premium { border-collapse: separate; border-spacing: 0; width: 100%; }
    
    /* Sticky Elements */
    .main-header { position: sticky; left: 0; top: 0; z-index: 100; background: #f8f9fc !important; border-bottom: 2px solid #e3e6f0 !important; border-right: 2px solid #e3e6f0 !important; padding: 15px !important; color: #4e73df; font-weight: 800; font-size: 11px; }
    .tgl-header { position: sticky; top: 0; background: #f8f9fc; z-index: 50; min-width: 45px; text-align: center; border-bottom: 2px solid #e3e6f0; padding: 8px 0 !important; }
    .sticky-col-body { position: sticky; left: 0; background: #fff; z-index: 60; min-width: 250px; border-right: 2px solid #e3e6f0; border-bottom: 1px solid #f1f4f8; padding: 8px 15px !important; }

    /* Cells & Status Boxes */
    .cell-action { cursor: pointer; padding: 5px !important; border-bottom: 1px solid #f1f4f8 !important; border-right: 1px solid #f1f4f8 !important; position: relative; }
    .status-box { width: 32px; height: 32px; margin: 0 auto; display: flex; align-items: center; justify-content: center; border-radius: 6px; font-weight: 800; font-size: 12px; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .cell-action:hover .status-box { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); filter: brightness(1.1); }

    .rekap-header {
        font-size: 10px;
        font-weight: 800;
        color: #4e73df;
        vertical-align: middle !important;
        text-align: center;
    }

    .bg-light-rekap {
        background-color: #f8f9fc !important;
    }

    /* Gunakan CLASS .rekap-container, bukan ID agar berlaku untuk semua baris */
    .rekap-container .counter-item {
        display: inline-block;
        min-width: 22px;
        padding: 2px 4px;
        margin: 0 2px;
        border-radius: 4px;
        background: #ffffff;
        border: 1px solid #e3e6f0;
        font-size: 11px;
        font-weight: 800;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    /* Berikan sedikit hover agar terlihat premium */
    .rekap-container .counter-item:hover {
        background: #f1f4f8;
        transform: translateY(-1px);
    }

    /* Gradient & Dekorasi Modal */
    .bg-primary-gradient {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
    }
    .icon-shape {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 16px;
    }
    .custom-focus:focus {
        border-color: #4e73df !important;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15) !important;
        background-color: #fff !important;
    }
    /* Colors */
    .status-hadir { background: #10b981; color: #fff; }
    .status-sakit { background: #f59e0b; color: #fff; }
    .status-izin  { background: #3b82f6; color: #fff; }
    .status-alfa  { background: #ef4444; color: #fff; }
    .status-terlambat { background: #6366f1; color: #fff; }
    
    /* Legend Styles */
    .legend-item { display: flex; align-items: center; margin-right: 15px; }
    .legend-box { width: 12px; height: 12px; border-radius: 3px; margin-right: 5px; }
    .bg-hadir { background: #10b981; } .bg-sakit { background: #f59e0b; } .bg-izin { background: #3b82f6; } .bg-alfa { background: #ef4444; } .bg-terlambat { background: #6366f1; }

    /* Utils */
    .pill { border-radius: 20px !important; }
    .input-icon { position: relative; }
    .input-icon-addon { position: absolute; top: 10px; left: 15px; z-index: 10; }
    #searchSantri { padding-left: 40px !important; }
    .note-indicator { position: absolute; top: 4px; right: 4px; width: 0; height: 0; border-style: solid; border-width: 0 6px 6px 0; border-color: transparent #fff transparent transparent; opacity: 0.8; }
    .name-initial { width: 35px; height: 35px; background: #f1f5f9; color: #475569; display: flex; align-items: center; justify-content: center; border-radius: 10px; font-weight: 700; font-size: 11px; border: 1px solid #e2e8f0; }
    .s-name { font-weight: 700; font-size: 12px; color: #2d3748; }
    .s-nis { font-size: 10px; color: #718096; }
    .is-saving { animation: pulse 1s infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
 
</style>

@push('scripts')
<script>
$(document).ready(function() {
    // 1. Search Logic
    $("#searchSantri").on("keyup", function() {
        let value = $(this).val().toLowerCase();
        $(".table-premium tbody tr").filter(function() {
            $(this).toggle($(this).find(".s-name").text().toLowerCase().indexOf(value) > -1 || 
                         $(this).find(".s-nis").text().toLowerCase().indexOf(value) > -1);
        });
    });

    // 2. Status Cycle Logic
    const nextStatus = { 'hadir':'sakit', 'sakit':'izin', 'izin':'alfa', 'alfa':'terlambat', 'terlambat':'hadir' };
    const statusLabel = { 'hadir':'H', 'sakit':'S', 'izin':'I', 'alfa':'A', 'terlambat':'T' };

    $('.cell-action').on('click', function() {
        let cell = $(this);
        let box = cell.find('.status-box');
        let currentStatus = cell.data('current');
        let newStatus = nextStatus[currentStatus];

        updateUI(cell, box, newStatus);

        $.ajax({
            url: "{{ route('tenant.absensi.store') }}",
            method: "POST",
            data: { _token: "{{ csrf_token() }}", santri_id: cell.data('santri'), tanggal: cell.data('tgl'), status: newStatus, sesi_id: "{{ $sesi_id }}", mode: 'ajax' },
            beforeSend: function() { cell.addClass('is-saving'); },
            success: function() { cell.removeClass('is-saving'); },
            error: function() { cell.removeClass('is-saving'); updateUI(cell, box, currentStatus); alert('Gagal menyimpan status!'); }
        });
    });

    // 3. Catatan Logic (Right Click)
    $('.cell-action').on('contextmenu', function(e) {
        e.preventDefault();
        let cell = $(this);
        let namaSantri = cell.closest('tr').find('.s-name').text(); // Ambil nama dari baris yang sama
        let tgl = cell.data('tgl');
        
        $('#modal_santri').val(cell.data('santri'));
        $('#modal_tgl').val(tgl);
        $('#catatan_input').val(cell.attr('data-catatan'));
        
        // Update teks info di modal biar lebih personal
        $('#display_info_modal').html(`Catatan untuk <b>${namaSantri}</b>`);
        
        $('#modalCatatan').modal('show');
    });

    $('#btnSimpanCatatan').on('click', function() {
        let santriId = $('#modal_santri').val();
        let tgl = $('#modal_tgl').val();
        let catatan = $('#catatan_input').val();
        let cell = $(`.cell-action[data-santri="${santriId}"][data-tgl="${tgl}"]`);

        $.ajax({
            url: "{{ route('tenant.absensi.store') }}",
            method: "POST",
            data: { _token: "{{ csrf_token() }}", santri_id: santriId, tanggal: tgl, keterangan: catatan, sesi_id: "{{ $sesi_id }}", mode: 'ajax_keterangan' },
            success: function() {
                cell.attr('data-catatan', catatan);
                cell.attr('title', catatan ? 'Catatan: ' + catatan : '');
                if(catatan.trim() !== "") {
                    if(cell.find('.note-indicator').length === 0) cell.append('<div class="note-indicator"></div>');
                } else {
                    cell.find('.note-indicator').remove();
                }
                $('#modalCatatan').modal('hide');
            },
            error: function() { alert('Gagal menyimpan catatan!'); }
        });
    });

    function updateUI(cell, box, status) {
        let santriId = cell.data('santri');
        let oldStatus = cell.data('current'); // Status lama (H/S/I/A/T)

        // 1. Update Box Visual
        cell.data('current', status);
        box.removeClass('status-hadir status-sakit status-izin status-alfa status-terlambat');
        box.addClass('status-' + status);
        box.fadeOut(50, function() {
            $(this).text(statusLabel[status]).fadeIn(50);
        });

        // 2. Update Angka Rekap di Kanan
        let rekapDiv = $(`#rekap-${santriId}`);
        
        // Kurangi counter status lama
        let oldCounter = rekapDiv.find(`.counter-${oldStatus}`);
        oldCounter.text(Math.max(0, parseInt(oldCounter.text()) - 1));

        // Tambah counter status baru
        let newCounter = rekapDiv.find(`.counter-${status}`);
        newCounter.text(parseInt(newCounter.text()) + 1);
    }
});
</script>
@endpush
@endsection