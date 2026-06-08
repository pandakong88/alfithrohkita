@extends('layouts.tenant')

@section('title', 'Manajemen Asrama')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Manajemen Asrama</h3>
        <p class="text-muted small mb-0">Kelola komplek gedung, kamar asrama, kapasitas hunian, dan inventaris lemari.</p>
    </div>
    @can('manage_asrama')
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-round" data-bs-toggle="modal" data-bs-target="#modalAddKomplek">
            <i class="fas fa-building me-1"></i> Tambah Komplek
        </button>
        <button type="button" class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#modalAddKamar">
            <i class="fas fa-plus me-1"></i> Tambah Kamar
        </button>
    </div>
    @endcan
</div>

@if(session('success'))
    <div id="asrama-success-message" data-message="{{ session('success') }}"></div>
@endif
@if(session('error'))
    <div id="asrama-error-message" data-message="{{ session('error') }}"></div>
@endif

{{-- STATS SECTION --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card card-stats card-round border-0 shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-primary bubble-shadow-small bg-primary text-white" style="border-radius: 12px; width: 45px; height: 45px; line-height: 45px;">
                            <i class="fas fa-building"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3">
                        <div class="numbers">
                            <p class="card-category text-muted text-xs fw-semibold mb-0">TOTAL KOMPLEK</p>
                            <h4 class="card-title fw-bold text-dark mb-0 mt-0.5">{{ $totalKomplek }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card card-stats card-round border-0 shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-success bubble-shadow-small bg-success text-white" style="border-radius: 12px; width: 45px; height: 45px; line-height: 45px;">
                            <i class="fas fa-door-open"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3">
                        <div class="numbers">
                            <p class="card-category text-muted text-xs fw-semibold mb-0">TOTAL KAMAR</p>
                            <h4 class="card-title fw-bold text-dark mb-0 mt-0.5">{{ $totalKamar }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card card-stats card-round border-0 shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-info bubble-shadow-small bg-info text-white" style="border-radius: 12px; width: 45px; height: 45px; line-height: 45px;">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3">
                        <div class="numbers">
                            <p class="card-category text-muted text-xs fw-semibold mb-0">KAPASITAS ASRAMA</p>
                            <h4 class="card-title fw-bold text-dark mb-0 mt-0.5">{{ $terisi }} / {{ $totalKapasitas }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card card-stats card-round border-0 shadow-sm mb-0">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-warning bubble-shadow-small bg-warning text-white" style="border-radius: 12px; width: 45px; height: 45px; line-height: 45px;">
                            <i class="fas fa-bed"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3">
                        <div class="numbers">
                            <p class="card-category text-muted text-xs fw-semibold mb-0">OKUPANSI RATE</p>
                            <h4 class="card-title fw-bold text-dark mb-0 mt-0.5">
                                {{ $totalKapasitas > 0 ? round(($terisi / $totalKapasitas) * 100) : 0 }}%
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TABS FOR ASRAMA OVERVIEW --}}
<ul class="nav nav-pills nav-primary mb-4" id="asramaTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-bold px-4 py-2" id="kamar-tab" data-bs-toggle="pill" data-bs-target="#kamar-panel" type="button" role="tab" aria-controls="kamar-panel" aria-selected="true" style="border-radius: 8px;">
            <i class="fas fa-door-open me-2"></i>Komplek & Kamar
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold px-4 py-2" id="lemari-tab" data-bs-toggle="pill" data-bs-target="#lemari-panel" type="button" role="tab" aria-controls="lemari-panel" aria-selected="false" style="border-radius: 8px;">
            <i class="fas fa-box-open me-2"></i>Ringkasan Inventaris Lemari
        </button>
    </li>
</ul>

<div class="tab-content" id="asramaTabContent">
    {{-- TAB 1: KOMPLEK & KAMAR --}}
    <div class="tab-pane fade show active" id="kamar-panel" role="tabpanel" aria-labelledby="kamar-tab">
        <div class="row">
            @forelse($kompleks as $komplek)
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-0">
                            <div>
                                <h5 class="fw-bold mb-0 text-primary">
                                    <i class="fas fa-building me-2"></i>{{ $komplek->nama }}
                                </h5>
                                <small class="text-muted">{{ $komplek->kamars->count() }} Kamar</small>
                            </div>
                            @can('manage_asrama')
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-icon btn-link btn-xs text-warning" 
                                        onclick="editKomplek({{ $komplek->id }}, '{{ addslashes($komplek->nama) }}')" 
                                        title="Edit Komplek">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('tenant.komplek.destroy', $komplek) }}" method="POST" class="d-inline delete-komplek-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-icon btn-link btn-xs text-danger btn-delete-asrama" title="Hapus Komplek">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                            @endcan
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                    <thead class="table-light text-muted font-xs text-uppercase">
                                        <tr>
                                            <th class="ps-4" style="width: 40%">Nama Kamar</th>
                                            <th style="width: 40%">Kapasitas</th>
                                            <th class="text-center pe-4" style="width: 20%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($komplek->kamars as $kamar)
                                            @php
                                                $occupancyRate = $kamar->kapasitas > 0 ? ($kamar->santris_count / $kamar->kapasitas) * 100 : 0;
                                                $barColor = 'bg-success';
                                                if ($occupancyRate > 90) {
                                                    $barColor = 'bg-danger';
                                                } elseif ($occupancyRate > 60) {
                                                    $barColor = 'bg-warning';
                                                }
                                            @endphp
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-bold text-dark">{{ $kamar->nama }}</div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="progress flex-grow-1" style="height: 6px; border-radius: 3px;">
                                                            <div class="progress-bar {{ $barColor }}" role="progressbar" style="width: {{ $occupancyRate }}%"></div>
                                                        </div>
                                                        <span class="text-muted fw-bold" style="min-width: 45px;">
                                                            {{ $kamar->santris_count }}/{{ $kamar->kapasitas }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="text-center pe-4">
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <a href="{{ route('tenant.kamar.show', $kamar) }}" class="btn btn-icon btn-link btn-xs text-primary" title="Detail Kamar">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        @can('manage_asrama')
                                                        <button type="button" class="btn btn-icon btn-link btn-xs text-warning" 
                                                                onclick="editKamar({{ $kamar->id }}, '{{ addslashes($kamar->nama) }}', {{ $kamar->kapasitas }}, {{ $komplek->id }})" 
                                                                title="Edit Kamar">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <form action="{{ route('tenant.kamar.destroy', $kamar) }}" method="POST" class="d-inline delete-kamar-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" class="btn btn-icon btn-link btn-xs text-danger btn-delete-asrama" title="Hapus Kamar">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                        @endcan
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">Belum ada kamar di komplek ini.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="icon-avatar bg-light-soft text-muted mx-auto mb-3" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-hotel fa-lg"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Belum Ada Data Komplek</h5>
                    <p class="text-muted small mb-0">Tambahkan komplek gedung baru untuk mengelompokkan kamar asrama.</p>
                    @can('manage_asrama')
                    <button class="btn btn-primary btn-round btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#modalAddKomplek">
                        Tambah Komplek Pertama <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                    @endcan
                </div>
            @endforelse
        </div>
    </div>

    {{-- TAB 2: INVENTARIS LEMARI GLOBAL --}}
    <div class="tab-pane fade" id="lemari-panel" role="tabpanel" aria-labelledby="lemari-tab">
        {{-- STATS WIDGETS --}}
        <div class="row g-3 mb-4">
            {{-- Total Lemari --}}
            <div class="col-md-3 col-6">
                <div class="card card-stats card-round border-0 shadow-sm mb-0">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center text-white bg-secondary bubble-shadow-small" style="border-radius: 12px; width: 45px; height: 45px; line-height: 45px;">
                                    <i class="fas fa-box-open"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3">
                                <div class="numbers">
                                    <p class="card-category text-muted text-xs fw-semibold mb-0">TOTAL LEMARI</p>
                                    <h4 class="card-title fw-bold text-dark mb-0 mt-0.5">{{ $totalLemari }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Slot Kosong --}}
            <div class="col-md-3 col-6">
                <div class="card card-stats card-round border-0 shadow-sm mb-0">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center text-white bg-success bubble-shadow-small" style="border-radius: 12px; width: 45px; height: 45px; line-height: 45px;">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3">
                                <div class="numbers">
                                    <p class="card-category text-success text-xs fw-semibold mb-0">SLOT KOSONG</p>
                                    <h4 class="card-title fw-bold text-success mb-0 mt-0.5">{{ $slotStats['kosong'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Slot Terpakai --}}
            <div class="col-md-3 col-6">
                <div class="card card-stats card-round border-0 shadow-sm mb-0">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center text-white bg-primary bubble-shadow-small" style="border-radius: 12px; width: 45px; height: 45px; line-height: 45px;">
                                    <i class="fas fa-user-check"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3">
                                <div class="numbers">
                                    <p class="card-category text-primary text-xs fw-semibold mb-0">SLOT DIPAKAI</p>
                                    <h4 class="card-title fw-bold text-primary mb-0 mt-0.5">{{ $slotStats['dipakai'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Slot Rusak/Barang --}}
            <div class="col-md-3 col-6">
                <div class="card card-stats card-round border-0 shadow-sm mb-0">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center text-white bg-warning bubble-shadow-small" style="border-radius: 12px; width: 45px; height: 45px; line-height: 45px;">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3">
                                <div class="numbers">
                                    <p class="card-category text-warning text-xs fw-semibold mb-0">RUSAK / BARANG</p>
                                    <h4 class="card-title fw-bold text-warning mb-0 mt-0.5">{{ $slotStats['rusak'] + $slotStats['barang'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- INVENTORY TABLE --}}
        <div class="card border-0 shadow-sm card-round">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-list-ul text-primary me-2"></i>Rincian Slot & Inventaris Lemari</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;" id="lemariInventoryTable">
                        <thead class="table-light text-muted font-xs text-uppercase">
                            <tr>
                                <th class="ps-4">Komplek / Kamar</th>
                                <th>Lemari</th>
                                <th>Tipe</th>
                                <th>Okupansi Slot</th>
                                <th class="pe-4">Rincian Slot</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $hasLemari = false; @endphp
                            @foreach($kompleks as $komplek)
                                @foreach($komplek->kamars as $kamar)
                                    @foreach($kamar->lemaris as $lemari)
                                        @php 
                                            $hasLemari = true; 
                                            $d = $lemari->slots->where('status', 'dipakai')->count();
                                            $k = $lemari->slots->where('status', 'kosong')->count();
                                            $r = $lemari->slots->where('status', 'rusak')->count();
                                            $b = $lemari->slots->where('status', 'barang')->count();
                                            $tot = $lemari->slots->count();
                                            $percent = $tot > 0 ? ($d / $tot) * 100 : 0;
                                        @endphp
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold text-dark">{{ $kamar->nama }}</div>
                                                <small class="text-muted">{{ $komplek->nama }}</small>
                                            </td>
                                            <td><span class="fw-semibold text-slate">{{ $lemari->nama }}</span></td>
                                            <td>
                                                <span class="badge bg-light text-dark border font-size-10 px-2 py-1 rounded">
                                                    {{ ucfirst(str_replace('_', ' ', $lemari->tipe)) }}
                                                </span>
                                            </td>
                                            <td style="width: 200px;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="progress flex-grow-1" style="height: 6px; border-radius: 3px; background-color: #f1f5f9;">
                                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percent }}%"></div>
                                                    </div>
                                                    <span class="text-muted fw-bold" style="min-width: 45px;">
                                                        {{ $d }}/{{ $tot }} Slot
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="pe-4">
                                                <div class="d-flex flex-wrap gap-2 text-xs">
                                                    @if($k > 0)
                                                        <span class="badge bg-success-light text-success border border-success-subtle font-size-10 px-2 py-0.5" style="background-color: #f0fdf4 !important; color: #166534 !important;"><i class="fas fa-circle me-1"></i>{{ $k }} Kosong</span>
                                                    @endif
                                                    @if($d > 0)
                                                        <span class="badge bg-primary-light text-primary border border-primary-subtle font-size-10 px-2 py-0.5" style="background-color: #eff6ff !important; color: #1e40af !important;"><i class="fas fa-circle me-1"></i>{{ $d }} Dipakai</span>
                                                    @endif
                                                    @if($b > 0)
                                                        <span class="badge bg-warning-light text-warning border border-warning-subtle font-size-10 px-2 py-0.5" style="background-color: #fffbeb !important; color: #854d0e !important;"><i class="fas fa-circle me-1"></i>{{ $b }} Barang</span>
                                                    @endif
                                                    @if($r > 0)
                                                        <span class="badge bg-danger-light text-danger border border-danger-subtle font-size-10 px-2 py-0.5" style="background-color: #fef2f2 !important; color: #991b1b !important;"><i class="fas fa-circle me-1"></i>{{ $r }} Rusak</span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @endforeach
                            
                            @if(!$hasLemari)
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">Belum ada inventaris lemari di seluruh kamar.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODALS --}}

{{-- Modal Tambah Komplek --}}
<div class="modal fade" id="modalAddKomplek" tabindex="-1" aria-labelledby="modalAddKomplekLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('tenant.komplek.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalAddKomplekLabel"><i class="fas fa-building me-2"></i>Tambah Komplek</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_komplek_nama" class="form-label fw-bold">Nama Komplek <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add_komplek_nama" name="nama" placeholder="Contoh: Komplek Abu Bakar, Komplek A, dll." required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Komplek --}}
<div class="modal fade" id="modalEditKomplek" tabindex="-1" aria-labelledby="modalEditKomplekLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formEditKomplek" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalEditKomplekLabel"><i class="fas fa-edit me-2"></i>Edit Komplek</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_komplek_nama" class="form-label fw-bold">Nama Komplek <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_komplek_nama" name="nama" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Perbarui</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Tambah Kamar --}}
<div class="modal fade" id="modalAddKamar" tabindex="-1" aria-labelledby="modalAddKamarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('tenant.kamar.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalAddKamarLabel"><i class="fas fa-plus me-2"></i>Tambah Kamar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_kamar_komplek" class="form-label fw-bold">Pilih Komplek <span class="text-danger">*</span></label>
                        <select class="form-select" id="add_kamar_komplek" name="komplek_id" required>
                            <option value="">-- Pilih Komplek --</option>
                            @foreach($kompleks as $komplek)
                                <option value="{{ $komplek->id }}">{{ $komplek->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="add_kamar_nama" class="form-label fw-bold">Nama Kamar <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add_kamar_nama" name="nama" placeholder="Contoh: Kamar 01, Kamar A1, dll." required>
                    </div>
                    <div class="mb-3">
                        <label for="add_kamar_kapasitas" class="form-label fw-bold">Kapasitas (Orang) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="add_kamar_kapasitas" name="kapasitas" min="1" placeholder="Contoh: 4" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Kamar --}}
<div class="modal fade" id="modalEditKamar" tabindex="-1" aria-labelledby="modalEditKamarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formEditKamar" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalEditKamarLabel"><i class="fas fa-edit me-2"></i>Edit Kamar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_kamar_komplek" class="form-label fw-bold">Pilih Komplek <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_kamar_komplek" name="komplek_id" required>
                            @foreach($kompleks as $komplek)
                                <option value="{{ $komplek->id }}">{{ $komplek->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_kamar_nama" class="form-label fw-bold">Nama Kamar <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_kamar_nama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_kamar_kapasitas" class="form-label fw-bold">Kapasitas (Orang) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="edit_kamar_kapasitas" name="kapasitas" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Perbarui</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .font-xs { font-size: 0.7rem; letter-spacing: 0.05em; }
    .bg-light-soft { background-color: #f8fbff; }
</style>

@endsection

@push('scripts')
<script>
    function editKomplek(id, nama) {
        var actionUrl = "{{ route('tenant.komplek.update', ':id') }}".replace(':id', id);
        $('#formEditKomplek').attr('action', actionUrl);
        $('#edit_komplek_nama').val(nama);
        var modal = new bootstrap.Modal(document.getElementById('modalEditKomplek'));
        modal.show();
    }

    function editKamar(id, nama, kapasitas, komplekId) {
        var actionUrl = "{{ route('tenant.kamar.update', ':id') }}".replace(':id', id);
        $('#formEditKamar').attr('action', actionUrl);
        $('#edit_kamar_nama').val(nama);
        $('#edit_kamar_kapasitas').val(kapasitas);
        $('#edit_kamar_komplek').val(komplekId);
        var modal = new bootstrap.Modal(document.getElementById('modalEditKamar'));
        modal.show();
    }

    $(document).ready(function() {
        // Initialize DataTable for global cabinet inventory
        $('#lemariInventoryTable').DataTable({
            "pageLength": 10,
            "dom": '<"p-3 d-flex justify-content-between"lf>rt<"p-3 d-flex justify-content-between"ip>',
            "language": {
                "search": "Cari Lemari/Kamar:",
                "lengthMenu": "Tampilkan _MENU_ baris",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ lemari",
                "paginate": {
                    "next": "Lanjut",
                    "previous": "Sebelumnya"
                }
            }
        });

        // Show success alert if any
        var successMsg = $('#asrama-success-message').data('message');
        if(successMsg) {
            swal({
                title: 'Berhasil!',
                text: successMsg,
                icon: 'success',
                timer: 2000,
                buttons: false
            });
            $.notify({
                icon: 'fa fa-check',
                message: successMsg
            },{
                type: 'success',
                placement: { from: 'bottom', align: 'right' },
                delay: 2000,
                timer: 500
            });
        }

        // Show error alert if any
        var errorMsg = $('#asrama-error-message').data('message');
        if(errorMsg) {
            swal({
                title: 'Gagal!',
                text: errorMsg,
                icon: 'error',
                buttons: {
                    confirm: {
                        className: 'btn btn-danger'
                    }
                }
            });
        }

        // SweetAlert Confirm for Delete
        $('.btn-delete-asrama').on('click', function(e) {
            e.preventDefault();
            var form = $(this).closest('form');
            swal({
                title: 'Yakin ingin menghapus?',
                text: 'Semua relasi di bawahnya (kamar/lemari/slot) akan terpengaruh atau ikut terhapus!',
                icon: 'warning',
                buttons: {
                    cancel: {
                        text: 'Batal',
                        visible: true,
                        className: 'btn btn-secondary'
                    },
                    confirm: {
                        text: 'Ya, Hapus',
                        visible: true,
                        className: 'btn btn-danger'
                    }
                },
                dangerMode: true,
            }).then(function(willDelete) {
                if (willDelete) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
