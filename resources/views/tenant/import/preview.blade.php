@extends('layouts.tenant')

@section('title', 'Verifikasi Data Import')

@section('content')
<div class="container" style="min-height: 90vh;">
    <div class="page-inner py-4">
        
        {{-- BREADCRUMB --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style-1 mb-0" style="background: transparent; padding: 0;">
                <li class="breadcrumb-item"><a href="{{ route('tenant.import-templates.index') }}">Template Survey</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tenant.import.upload') }}">Import Excel</a></li>
                <li class="breadcrumb-item active" aria-current="page">Preview</li>
            </ol>
        </nav>

        {{-- HEADER SECTION --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h2 class="fw-bold text-dark mb-1"><i class="fas fa-microscope text-success me-2"></i> Analisa Validasi Data</h2>
                <p class="text-muted mb-0 small">Tinjau data secara detail sebelum melakukan <strong>Commit</strong> ke database sistem.</p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <a href="{{ route('tenant.import.upload') }}" class="btn btn-light btn-round border shadow-sm btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
                @if($batch->status === 'preview' && $batch->valid_rows > 0)
                <form method="POST" action="{{ route('tenant.import.commit', $batch->id) }}" id="commit-form" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-success btn-round px-4 shadow-sm btn-sm" onclick="return confirm('Yakin ingin menyimpan data yang valid?')">
                        <i class="fas fa-cloud-upload-alt me-1.5"></i> Simpan Data ({{ $batch->valid_rows }})
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- INFO CARDS --}}
        <div class="row g-3 mb-4">
            {{-- Total Baris --}}
            <div class="col-6 col-lg-3">
                <div class="card card-stat-custom h-100 mb-0">
                    <div class="card-body p-3.5 d-flex align-items-center">
                        <div class="icon-avatar bg-primary-soft text-primary me-3">
                            <i class="fas fa-list fa-lg"></i>
                        </div>
                        <div>
                            <span class="text-xs fw-semibold text-muted d-block" style="letter-spacing: 0.5px;">TOTAL BARIS</span>
                            <h4 class="fw-bold mb-0 text-dark mt-0.5">{{ $batch->total_rows }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Siap Simpan --}}
            <div class="col-6 col-lg-3">
                <div class="card card-stat-custom h-100 mb-0">
                    <div class="card-body p-3.5 d-flex align-items-center">
                        <div class="icon-avatar bg-success-soft text-success me-3">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                        <div>
                            <span class="text-xs fw-semibold text-muted d-block" style="letter-spacing: 0.5px;">SIAP SIMPAN</span>
                            <h4 class="fw-bold mb-0 text-success mt-0.5">{{ $batch->valid_rows }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bermasalah --}}
            <div class="col-6 col-lg-3">
                <div class="card card-stat-custom h-100 mb-0 {{ $batch->invalid_rows > 0 ? 'border border-danger border-opacity-25' : '' }}">
                    <div class="card-body p-3.5 d-flex align-items-center">
                        <div class="icon-avatar bg-danger-soft text-danger me-3">
                            <i class="fas fa-exclamation-triangle fa-lg"></i>
                        </div>
                        <div>
                            <span class="text-xs fw-semibold text-muted d-block" style="letter-spacing: 0.5px;">BERMASALAH</span>
                            <h4 class="fw-bold mb-0 text-danger mt-0.5">{{ $batch->invalid_rows }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status --}}
            <div class="col-6 col-lg-3">
                <div class="card card-stat-custom h-100 mb-0">
                    <div class="card-body p-3.5 d-flex align-items-center">
                        <div class="icon-avatar bg-warning-soft text-warning me-3">
                            <i class="fas fa-hourglass-half fa-lg"></i>
                        </div>
                        <div>
                            <span class="text-xs fw-semibold text-muted d-block" style="letter-spacing: 0.5px;">STATUS PROSES</span>
                            <h4 class="fw-bold mb-0 text-warning mt-0.5">{{ strtoupper($batch->status) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- DETAILED WARNING BANNERS & DIAGNOSTIC CARD --}}
        @if($batch->invalid_rows > 0)
            <div class="row g-3 mb-4">
                {{-- Warning Banner --}}
                <div class="{{ $batch->valid_rows == 0 ? 'col-md-7' : 'col-md-8' }}">
                    @if($batch->valid_rows == 0)
                        <div class="alert alert-warning border-0 shadow-sm p-4 h-100 d-flex align-items-start gap-3 rounded-4" style="background-color: #fffbeb; border-left: 5px solid #d97706 !important; margin-bottom: 0;">
                            <div class="icon-avatar bg-warning-soft text-warning shadow-xs mt-0.5">
                                <i class="fas fa-info-circle fa-lg"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fw-bold text-dark mb-1">Semua data dalam berkas bermasalah!</h5>
                                <p class="text-muted text-sm mb-3">Tidak ada data yang dapat disimpan. Silakan unduh laporan kesalahan Excel untuk melihat detail error di setiap baris, perbaiki file Excel Anda, lalu unggah ulang.</p>
                                <a href="{{ route('tenant.import.errors.download', $batch->id) }}" class="btn btn-warning btn-sm btn-round shadow-xs text-white" style="background-color: #d97706 !important; border-color: #d97706 !important; font-weight: 600;">
                                    <i class="fas fa-file-excel me-1.5"></i> Unduh Laporan Kesalahan Excel (.xlsx)
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info border-0 shadow-sm p-4 h-100 d-flex align-items-start gap-3 rounded-4" style="background-color: #f0f9ff; border-left: 5px solid #0284c7 !important; margin-bottom: 0;">
                            <div class="icon-avatar bg-primary-soft text-primary shadow-xs mt-0.5">
                                <i class="fas fa-info-circle fa-lg"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fw-bold text-dark mb-1 font-size-15">Ditemukan data siap simpan dan data bermasalah</h5>
                                <p class="text-muted text-sm mb-3">Terdapat {{ $batch->valid_rows }} baris data siap simpan dan {{ $batch->invalid_rows }} baris data bermasalah. Anda dapat melanjutkan untuk menyimpan data yang valid sekarang, atau mengunduh laporan kesalahan untuk memperbaiki seluruh baris terlebih dahulu.</p>
                                <a href="{{ route('tenant.import.errors.download', $batch->id) }}" class="btn btn-outline-info btn-sm btn-round shadow-xs px-3" style="font-weight: 600; border-color: #0284c7; color: #0284c7; font-size: 12px;">
                                    <i class="fas fa-file-excel me-1.5"></i> Unduh Laporan Kesalahan (.xlsx)
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Top Errors Distribution --}}
                <div class="{{ $batch->valid_rows == 0 ? 'col-md-5' : 'col-md-4' }}">
                    <div class="card card-custom h-100 mb-0" style="border-radius: 16px;">
                        <div class="card-header bg-white border-bottom-0 py-3 px-4">
                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 13.5px;"><i class="fas fa-list-ol text-danger me-2"></i> Diagnosa Error Terbanyak</h6>
                        </div>
                        <div class="card-body px-4 py-2" style="max-height: 200px; overflow-y: auto;">
                            @php
                                $errorDistribution = [];
                                foreach ($batch->rows as $row) {
                                    if ($row->errors) {
                                        foreach ((array)$row->errors as $err) {
                                            $errorDistribution[$err] = ($errorDistribution[$err] ?? 0) + 1;
                                        }
                                    }
                                }
                                arsort($errorDistribution);
                                $topErrors = array_slice($errorDistribution, 0, 3, true);
                            @endphp
                            
                            @if(count($topErrors) > 0)
                                <div class="d-flex flex-column gap-2 mb-2">
                                    @foreach($topErrors as $errMsg => $count)
                                        <div class="p-2.5 rounded-3" style="background: #fafafa; border: 1px solid #f1f5f9;">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="badge bg-danger-soft text-danger fw-bold rounded-pill text-xs px-2 py-0.5">
                                                    {{ $count }} Baris
                                                </span>
                                            </div>
                                            <span class="text-xs text-dark fw-semibold text-wrap d-block" style="line-height: 1.3;">
                                                {{ $errMsg }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4 text-muted small">
                                    <i class="fas fa-check-circle text-success fa-2x mb-2 d-block"></i>
                                    Tidak ada data error
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
        @endif

        @if(!empty($dormStructure))
            <div class="card card-custom mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold mb-0 text-dark" style="font-size: 14px;">
                        <i class="fas fa-sitemap me-2 text-success"></i>Visualisasi Struktur Asrama (Deteksi Excel)
                    </h5>
                    <p class="text-muted text-xs mb-0 mt-1">Hierarki komplek, kamar, lemari, dan slot yang terdeteksi di dalam berkas Excel yang diunggah.</p>
                </div>
                <div class="card-body p-4">
                    <div class="accordion accordion-custom" id="dormAccordion">
                        @foreach($dormStructure as $komplekNama => $kamars)
                            @php
                                $isFirst = $loop->first;
                                $komplekId = 'komplek_' . Str::slug($komplekNama);
                                $totalRooms = count($kamars);
                                $totalSlots = 0;
                                $occupiedSlots = 0;
                                foreach ($kamars as $kamarData) {
                                    if (!empty($kamarData['lemaris'])) {
                                        foreach ($kamarData['lemaris'] as $lemariData) {
                                            $dipakai = $lemariData['slots']['dipakai'] ?? 0;
                                            $kosong = $lemariData['slots']['kosong'] ?? 0;
                                            $rusak = $lemariData['slots']['rusak'] ?? 0;
                                            $barang = $lemariData['slots']['barang'] ?? 0;
                                            $totalSlots += ($dipakai + $kosong + $rusak + $barang);
                                            $occupiedSlots += $dipakai;
                                        }
                                    }
                                }
                            @endphp
                            <div class="accordion-item border-0 mb-3 rounded-3 overflow-hidden" style="border: 1px solid #e2e8f0 !important; border-radius: 8px;">
                                <h2 class="accordion-header" id="heading_{{ $komplekId }}">
                                    <button class="accordion-button {{ $isFirst ? '' : 'collapsed' }} py-3 px-4 fw-bold text-dark d-flex align-items-center gap-3" 
                                            type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#collapse_{{ $komplekId }}" 
                                            aria-expanded="{{ $isFirst ? 'true' : 'false' }}" 
                                            aria-controls="collapse_{{ $komplekId }}"
                                            style="background: #f8fafc; box-shadow: none; border-radius: 8px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-building text-primary me-1 fa-lg"></i>
                                            <span>{{ $komplekNama }}</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 ms-auto me-3">
                                            <span class="badge bg-primary-soft text-primary rounded-pill font-size-11 px-2.5">
                                                {{ $totalRooms }} Kamar
                                            </span>
                                            <span class="badge bg-success-soft text-success rounded-pill font-size-11 px-2.5">
                                                {{ $occupiedSlots }}/{{ $totalSlots }} Slot Terpakai
                                            </span>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse_{{ $komplekId }}" 
                                     class="accordion-collapse collapse {{ $isFirst ? 'show' : '' }}" 
                                     aria-labelledby="heading_{{ $komplekId }}" 
                                     data-bs-parent="#dormAccordion">
                                    <div class="accordion-body p-4 bg-white">
                                        <div class="row g-3">
                                            @foreach($kamars as $kamarNama => $kamarData)
                                                <div class="col-md-6 col-lg-4">
                                                    <div class="p-3 rounded-3 border bg-white shadow-xs h-100" style="border-radius: 8px; border: 1px solid #f1f5f9 !important;">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <span class="fw-bold text-dark text-xs">
                                                                <i class="fas fa-door-open text-warning me-1.5"></i>{{ $kamarNama }}
                                                            </span>
                                                            @if($kamarData['kapasitas'])
                                                                <span class="text-muted text-xs" style="font-size: 10.5px;">Kaps: <strong>{{ $kamarData['kapasitas'] }} Orang</strong></span>
                                                            @endif
                                                        </div>
                                                        
                                                        @if(!empty($kamarData['lemaris']))
                                                            <div class="d-flex flex-column gap-2 mt-2">
                                                                @foreach($kamarData['lemaris'] as $lemariNama => $lemariData)
                                                                    @php
                                                                        $dipakai = $lemariData['slots']['dipakai'] ?? 0;
                                                                        $kosong = $lemariData['slots']['kosong'] ?? 0;
                                                                        $rusak = $lemariData['slots']['rusak'] ?? 0;
                                                                        $barang = $lemariData['slots']['barang'] ?? 0;
                                                                        $total = $dipakai + $kosong + $rusak + $barang;
                                                                    @endphp
                                                                    <div class="p-2.5 rounded-2 border-0" style="font-size: 11px; background: #f8fafc; border-radius: 6px;">
                                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                                            <span class="text-dark font-weight-600">
                                                                                <i class="fas fa-box text-muted me-1.5"></i>{{ $lemariNama }}
                                                                                <span class="text-muted fw-normal" style="font-size: 10px;">({{ ucfirst(str_replace('_', ' ', $lemariData['tipe'])) }})</span>
                                                                            </span>
                                                                            <span class="text-slate font-weight-500" style="font-size: 10px;">{{ $dipakai }}/{{ $total }} Slot</span>
                                                                        </div>
                                                                        
                                                                        {{-- Multi-colored progress bar for slots --}}
                                                                        <div class="progress mb-1.5" style="height: 6px; border-radius: 3px; background-color: #e2e8f0; display: flex;">
                                                                            @if($total > 0)
                                                                                @if($dipakai > 0)
                                                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ ($dipakai / $total) * 100 }}%" title="{{ $dipakai }} Dipakai"></div>
                                                                                @endif
                                                                                @if($barang > 0)
                                                                                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ ($barang / $total) * 100 }}%" title="{{ $barang }} Barang"></div>
                                                                                @endif
                                                                                @if($rusak > 0)
                                                                                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{ ($rusak / $total) * 100 }}%" title="{{ $rusak }} Rusak"></div>
                                                                                @endif
                                                                                @if($kosong > 0)
                                                                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($kosong / $total) * 100 }}%" title="{{ $kosong }} Kosong"></div>
                                                                                @endif
                                                                            @endif
                                                                        </div>
                                                                        
                                                                        <div class="d-flex flex-wrap gap-2 text-xs text-muted mt-1.5" style="font-size: 9px;">
                                                                            @if($kosong > 0)
                                                                                <span><i class="fas fa-circle text-success me-1"></i>{{ $kosong }} Ksg</span>
                                                                            @endif
                                                                            @if($dipakai > 0)
                                                                                <span><i class="fas fa-circle text-primary me-1"></i>{{ $dipakai }} Tpk</span>
                                                                            @endif
                                                                            @if($barang > 0)
                                                                                <span><i class="fas fa-circle text-warning me-1"></i>{{ $barang }} Brg</span>
                                                                            @endif
                                                                            @if($rusak > 0)
                                                                                <span><i class="fas fa-circle text-danger me-1"></i>{{ $rusak }} Rsk</span>
                                                                            @endif
                                                                        </div>

                                                                        {{-- Mini Slot Grid --}}
                                                                        <div class="d-flex flex-wrap gap-1 mt-2.5 pt-2 border-top border-light-subtle">
                                                                            @foreach($lemariData['slots']['details'] as $slotNum => $slotDetail)
                                                                                @php
                                                                                    $st = $slotDetail['status'];
                                                                                    $nama = $slotDetail['santri_nama'];
                                                                                    $nis = $slotDetail['santri_nis'];
                                                                                    
                                                                                    $squareBg = 'bg-success text-white'; // Kosong
                                                                                    if ($st === 'dipakai') {
                                                                                        $squareBg = 'bg-primary text-white';
                                                                                    } elseif ($st === 'rusak') {
                                                                                        $squareBg = 'bg-danger text-white';
                                                                                    } elseif ($st === 'barang') {
                                                                                        $squareBg = 'bg-warning text-dark';
                                                                                    }
                                                                                    
                                                                                    $tooltipTitle = "Slot #{$slotNum}<br>Status: <strong>" . ucfirst($st) . "</strong>";
                                                                                    if ($nama) {
                                                                                        $tooltipTitle .= "<br>Calon Penghuni: <strong>" . htmlspecialchars($nama) . "</strong> (" . htmlspecialchars($nis) . ")";
                                                                                    }
                                                                                @endphp
                                                                                <div class="d-flex align-items-center justify-content-center rounded text-center slot-preview-badge {{ $squareBg }}"
                                                                                     style="width: 28px; height: 28px; font-size: 9px; cursor: help; font-weight: bold; transition: all 0.2s; border-radius: 4px;"
                                                                                     data-bs-toggle="tooltip" 
                                                                                     data-bs-html="true" 
                                                                                     title="{{ $tooltipTitle }}">
                                                                                    <span>#{{ $slotNum }}</span>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <div class="text-muted text-xs style-italic mt-1"><i class="fas fa-info-circle me-1"></i>Tidak ada data lemari.</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- FILTER & TABLE --}}
        <div class="card card-custom">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-round filter-btn btn-primary active" data-filter="all" style="font-weight: 500;">
                        Semua Baris ({{ $batch->total_rows }})
                    </button>
                    <button class="btn btn-sm btn-round filter-btn btn-outline-danger" data-filter="invalid" style="font-weight: 500;">
                        Baris Bermasalah ({{ $batch->invalid_rows }})
                    </button>
                </div>
                @if($batch->invalid_rows > 0)
                    <a href="{{ route('tenant.import.errors.download', $batch->id) }}" class="btn btn-outline-danger btn-xs btn-round shadow-none" style="font-weight: 500;">
                        <i class="fas fa-file-excel me-1.5"></i> Unduh Laporan Error
                    </a>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="previewTable" class="table table-custom align-middle mb-0" style="width: 100%;">
                        <thead>
                            <tr>
                                <th class="ps-4" style="width: 90px;">Baris</th>
                                <th style="width: 130px;">Validitas</th>
                                <th style="width: 150px;">Data Excel</th>
                                <th>Keterangan Masalah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batch->rows as $row)
                            <tr class="{{ !$row->is_valid ? 'bg-soft-danger' : '' }}" data-valid="{{ $row->is_valid ? 'valid' : 'invalid' }}">
                                <td class="ps-4 fw-bold text-slate">#{{ $row->row_number }}</td>
                                <td>
                                    <span class="badge {{ $row->is_valid ? 'bg-success-soft' : 'bg-danger-soft' }} px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 11px;">
                                        {{ $row->is_valid ? 'VALID' : 'INVALID' }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-xs btn-round btn-outline-primary px-3 shadow-none" onclick="showPayload({{ json_encode($row->payload) }}, {{ json_encode($row->errors) }})">
                                        <i class="fas fa-eye me-1.5"></i> Lihat Baris
                                    </button>
                                </td>
                                <td class="pe-4">
                                    @if($row->errors)
                                        @foreach((array)$row->errors as $err)
                                            <div class="d-flex align-items-center text-danger text-sm mb-1.5">
                                                <i class="fas fa-times-circle me-2 flex-shrink-0" style="font-size: 12px; color: #dc2626;"></i>
                                                <span class="text-wrap">{{ $err }}</span>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="d-flex align-items-center text-muted text-xs">
                                            <i class="fas fa-check-circle text-success me-2" style="font-size: 12px;"></i>
                                            <span>Tidak ada masalah</span>
                                        </div>
                                    @endif
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

{{-- MODAL DETAILED PAYLOAD --}}
<div class="modal fade" id="payloadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-bottom-0 px-4 pt-4 pb-0">
                <h5 class="fw-bold text-dark mb-0"><i class="fas fa-table text-primary me-2"></i> Detail Baris Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <div id="modalErrorsContainer"></div>
                <div class="table-responsive" style="border-radius: 10px; border: 1px solid #e2e8f0; overflow: hidden;">
                    <table class="table modal-payload-table mb-0">
                        <tbody id="payloadContainer">
                            {{-- Content loaded dynamically --}}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top-0 px-4 pb-4 pt-0">
                <button type="button" class="btn btn-light btn-round border px-4 w-100 fw-semibold" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* CSS Utility Variables */
    .page-inner {
        padding-top: 15px !important;
    }
    
    /* Custom Accordion Styling */
    .accordion-custom .accordion-item {
        border-radius: 8px !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.025) !important;
    }
    .accordion-custom .accordion-button:not(.collapsed) {
        color: #1e293b;
        background-color: #f8fafc;
        border-bottom: 1px solid #f1f5f9;
        box-shadow: none;
    }
    .accordion-custom .accordion-button:not(.collapsed)::after {
        transform: rotate(-180deg);
    }
    .accordion-custom .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(21, 114, 232, 0.2);
    }
    
    .max-w-5xl {
        max-width: 1024px;
        margin: 0 auto;
    }

    /* Icon Avatar Styles */
    .icon-avatar {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .bg-primary-gradient {
        background: linear-gradient(135deg, #1572e8 0%, #064095 100%) !important;
    }
    
    /* Card design */
    .card-custom {
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03), 0 1px 3px rgba(0, 0, 0, 0.02) !important;
        background: #ffffff;
        overflow: hidden;
        margin-bottom: 24px;
    }

    .card-custom .card-header {
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        padding: 20px 24px;
    }

    .card-stat-custom {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.02) !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: #ffffff;
    }
    
    .card-stat-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05) !important;
    }

    /* Soft colors */
    .bg-success-soft {
        background-color: #ecfdf5 !important;
        color: #059669 !important;
        border: 1px solid #a7f3d0 !important;
    }

    .bg-danger-soft {
        background-color: #fef2f2 !important;
        color: #dc2626 !important;
        border: 1px solid #fecaca !important;
    }
    
    .bg-primary-soft {
        background-color: #eff6ff !important;
        color: #2563eb !important;
        border: 1px solid #bfdbfe !important;
    }
    
    .bg-warning-soft {
        background-color: #fffbeb !important;
        color: #d97706 !important;
        border: 1px solid #fde68a !important;
    }

    .bg-soft-danger {
        background-color: rgba(239, 68, 68, 0.015) !important;
    }

    /* Table styles */
    .table-custom {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .table-custom thead th {
        background-color: #f8fafc;
        color: #475569;
        font-weight: 600;
        font-size: 13px;
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .table-custom tbody td {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13.5px;
    }

    .table-custom tbody tr:hover td {
        background-color: #f8fafc;
    }

    /* Modal payload styling */
    .modal-payload-table th {
        font-weight: 600;
        color: #475569;
        background: #f8fafc;
        width: 35%;
        border-bottom: 1px solid #f1f5f9;
        padding: 12px 16px;
    }
    
    .modal-payload-table td {
        border-bottom: 1px solid #f1f5f9;
        padding: 12px 16px;
    }
    
    .text-slate { color: #64748b; }
    .text-xs { font-size: 0.75rem; }
    .text-sm { font-size: 0.875rem; }
    .font-size-15 { font-size: 15px; }
    .mb-1.5 { margin-bottom: 0.375rem; }
    .mt-2.5 { margin-top: 0.625rem; }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialize DataTables
        var table = $('#previewTable').DataTable({ 
            "ordering": false,
            "pageLength": 25,
            "dom": "<'row px-4 py-3'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                   "<'row'<'col-sm-12'tr>>" +
                   "<'row px-4 py-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            "language": {
                "search": "Cari data:",
                "lengthMenu": "Tampilkan _MENU_ baris",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ baris",
                "paginate": {
                    "next": "Lanjut",
                    "previous": "Sebelumnya"
                }
            }
        });

        // Custom filter function for DataTables
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            const filterMode = $('.filter-btn.active').data('filter');
            if (filterMode === 'all') {
                return true;
            }
            
            const rowNode = table.row(dataIndex).node();
            const isValid = $(rowNode).attr('data-valid') === 'valid';
            
            if (filterMode === 'invalid') {
                return !isValid;
            }
            
            return true;
        });

        // Filter button click handler
        $('.filter-btn').on('click', function(e) {
            e.preventDefault();
            
            // Reset all filter buttons active states
            $('.filter-btn').each(function() {
                const mode = $(this).data('filter');
                if (mode === 'invalid') {
                    $(this).removeClass('btn-danger active').addClass('btn-outline-danger');
                } else {
                    $(this).removeClass('btn-primary active').addClass('btn-outline-secondary');
                }
            });
            
            // Set active class for the clicked button
            const mode = $(this).data('filter');
            if (mode === 'invalid') {
                $(this).removeClass('btn-outline-danger').addClass('btn-danger active');
            } else {
                $(this).removeClass('btn-outline-secondary').addClass('btn-primary active');
            }
            
            // Redraw table
            table.draw();
        });

        // Handle commit form spinner
        const commitForm = document.getElementById('commit-form');
        if (commitForm) {
            commitForm.addEventListener('submit', () => {
                const btn = commitForm.querySelector('button[type="submit"]');
                btn.disabled = true;
                btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Menyimpan...`;
            });
        }
    });

    function showPayload(data, errors) {
        // Render errors banner inside modal if any exist
        let errorsHtml = '';
        if (errors && errors.length > 0) {
            errorsHtml = `
                <div class="alert alert-danger border-0 p-3 mb-3" style="border-radius: 10px; background-color: #fef2f2; color: #dc2626;">
                    <span class="fw-bold d-block mb-1.5 text-xs text-uppercase" style="letter-spacing: 0.5px;"><i class="fas fa-exclamation-triangle me-1.5"></i> Detail Masalah Validasi:</span>
                    <ul class="mb-0 ps-3 text-xs" style="line-height: 1.4;">
                        ${errors.map(err => `<li class="mb-1">${err}</li>`).join('')}
                    </ul>
                </div>
            `;
        }
        $('#modalErrorsContainer').html(errorsHtml);

        let html = '';
        for (let key in data) {
            let val = data[key] === null || data[key] === undefined ? '-' : data[key];
            html += `<tr>
                <th class="ps-3 text-slate small">${key}</th>
                <td class="pe-3 text-dark small fw-medium text-wrap">${val}</td>
            </tr>`;
        }
        $('#payloadContainer').html(html);
        new bootstrap.Modal('#payloadModal').show();
    }
</script>
@endpush