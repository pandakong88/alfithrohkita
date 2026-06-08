@extends('layouts.tenant')

@section('title', 'Detail Profil Wali')

@section('content')
{{-- BREADCRUMB --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb breadcrumb-style-1 mb-0" style="background: transparent; padding: 0;">
        <li class="breadcrumb-item"><a href="{{ route('tenant.wali.index') }}">Manajemen Wali</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail Wali</li>
    </ol>
</nav>

{{-- HEADER SECTION --}}
<div class="card card-round mb-4 border-0">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4">
            <div class="d-flex align-items-center flex-column flex-sm-row text-center text-sm-start">
                <div class="avatar avatar-xxl me-sm-4 mb-3 mb-sm-0">
                    <span class="avatar-title rounded-circle border border-white bg-primary text-white fw-bold" style="font-size: 32px; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                        {{ strtoupper(substr($wali->nama, 0, 1)) }}
                    </span>
                </div>
                <div>
                    <h3 class="text-dark fw-bold mb-1" style="font-size: 1.8rem;">{{ $wali->nama }}</h3>
                    <p class="text-slate mb-1">
                        <span class="fw-semibold text-primary">ID: #WAL-{{ $wali->id }}</span> &bull; 
                        <span class="small text-muted"><i class="fas fa-briefcase me-1"></i> {{ $wali->pekerjaan ?? 'Tidak Ada Pekerjaan' }}</span>
                    </p>
                    <p class="text-muted mb-0 small">
                        <i class="far fa-calendar-alt me-1"></i> Terdaftar sejak: {{ $wali->created_at->format('d M Y') }}
                    </p>
                </div>
            </div>
            <div class="d-flex gap-2 justify-content-center">
                <a href="{{ route('tenant.wali.index') }}" class="btn btn-light btn-round border shadow-sm btn-sm px-3.5">
                    <i class="fas fa-arrow-left me-1.5"></i> Kembali
                </a>
                @can('manage_wali')
                <a href="{{ route('tenant.wali.edit', $wali) }}" class="btn btn-primary btn-round shadow-sm btn-sm px-3.5">
                    <i class="fas fa-edit me-1.5"></i> Edit Wali
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>

{{-- MAIN CONTENT ROW --}}
<div class="row g-4">
    
    {{-- SIDEBAR: BIODATA & AUDIT LOG --}}
    <div class="col-lg-4">
        <div class="d-flex flex-column gap-4">
            {{-- Detail Kontak & Personal --}}
            <div class="card card-round border-0 shadow-none">
                <div class="card-header py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-user-circle me-2 text-primary"></i>Profil Wali
                    </h6>
                </div>
                <div class="card-body p-3">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr class="border-bottom">
                                <td class="py-2.5 text-muted small fw-bold" style="width: 35%;"><i class="fas fa-id-card text-primary me-2"></i> NIK</td>
                                <td class="py-2.5 text-dark fw-semibold" style="font-size: 13px;">{{ $wali->nik ?? '-' }}</td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="py-2.5 text-muted small fw-bold"><i class="fas fa-phone text-primary me-2"></i> No. HP</td>
                                <td class="py-2.5 text-dark fw-semibold" style="font-size: 13px;">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span>{{ $wali->no_hp }}</span>
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $wali->no_hp) }}" target="_blank" class="badge bg-success text-white border-0 fw-bold d-inline-flex align-items-center gap-1" style="font-size: 10px; padding: 4px 8px; text-decoration: none;">
                                            <i class="fab fa-whatsapp"></i> Chat WA
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="py-2.5 text-muted small fw-bold"><i class="fas fa-briefcase text-primary me-2"></i> Pekerjaan</td>
                                <td class="py-2.5 text-dark fw-semibold" style="font-size: 13px;">{{ $wali->pekerjaan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="py-2.5 text-muted small fw-bold" valign="top"><i class="fas fa-home text-primary me-2"></i> Alamat</td>
                                <td class="py-2.5 text-dark fw-semibold" style="line-height: 1.5; font-size: 13px;">{{ $wali->alamat ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Audit Log Card --}}
            <div class="card card-round border-0 shadow-none">
                <div class="card-header py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-history me-2 text-primary"></i>Audit Log
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="p-3 bg-light rounded-3 d-flex flex-column gap-2 text-xs text-muted">
                        <div>
                            <i class="fas fa-plus-circle me-1.5 text-primary"></i> Terdaftar oleh: 
                            <strong class="text-dark">{{ $wali->creator->name ?? 'Sistem' }}</strong> 
                            pada <span class="text-dark">{{ $wali->created_at->format('d M Y H:i') }}</span>
                        </div>
                        @if($wali->updater)
                            <div>
                                <i class="fas fa-edit me-1.5 text-info"></i> Diperbarui oleh: 
                                <strong class="text-dark">{{ $wali->updater->name }}</strong> 
                                pada <span class="text-dark">{{ $wali->updated_at->format('d M Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN TABLE: KONEKSI SANTRI --}}
    <div class="col-lg-8">
        <div class="card card-round border-0 shadow-none h-100">
            <div class="card-header py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-link me-2 text-primary"></i>Santri Terhubung ({{ $wali->santris->count() }})
                </h6>
            </div>
            <div class="card-body p-3">
                @if($wali->santris->isNotEmpty())
                    <div class="alert alert-info border-0 shadow-none p-3 mb-4 rounded-3 d-flex align-items-center">
                        <div class="icon-avatar bg-info text-white me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 8px;">
                            <i class="fas fa-info-circle fa-lg"></i>
                        </div>
                        <div class="flex-1" style="font-size: 13.5px; line-height: 1.5;">
                            <span class="fw-bold text-dark">{{ $wali->nama }}</span> 
                            terhubung sebagai <strong class="text-primary">Orang Tua / Wali</strong> dari santri aktif berikut di bawah ini:
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-sm">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th class="ps-3" style="width: 15%;">NIS</th>
                                    <th style="width: 30%;">Nama Santri</th>
                                    <th class="text-center" style="width: 15%;">Grup (JK)</th>
                                    <th style="width: 20%;">Kelas & Kamar</th>
                                    <th class="text-center" style="width: 12%;">Status</th>
                                    <th class="text-end pe-3" style="width: 8%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($wali->santris as $santri)
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
                                        ];
                                        $st = $statusMap[$santri->status] ?? ['label' => ucfirst($santri->status), 'class' => 'bg-secondary-soft text-secondary'];
                                    @endphp
                                    <tr>
                                        <td class="ps-3 fw-bold text-primary text-nowrap">#{{ $santri->nis }}</td>
                                        <td class="text-nowrap">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3">
                                                    <span class="avatar-title rounded-circle border border-white {{ $santri->jenis_kelamin == 'L' ? 'bg-primary-soft text-primary' : 'bg-danger-soft text-danger' }} fw-bold" style="font-size: 12px; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                                        {{ strtoupper(substr($santri->nama_lengkap, 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div class="flex-1">
                                                    <h6 class="fw-bold mb-0 text-dark text-nowrap" style="font-size: 13px;">{{ $santri->nama_lengkap }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center text-nowrap">
                                            @if($santri->jenis_kelamin == 'L')
                                                <span class="text-info fw-semibold text-xs"><i class="fas fa-mars me-1"></i> Putra</span>
                                            @else
                                                <span class="text-danger fw-semibold text-xs"><i class="fas fa-venus me-1"></i> Putri</span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">
                                            <div class="d-flex flex-column gap-1">
                                                <small class="text-dark fw-medium"><i class="fas fa-school text-muted me-1.5"></i>{{ $santri->kelas->nama ?? '-' }}</small>
                                                <small class="text-muted"><i class="fas fa-bed text-muted me-1.5"></i>{{ $santri->kamar->nama ?? '-' }} @if(isset($santri->kamar->kompleks)) ({{ $santri->kamar->kompleks->nama }}) @endif</small>
                                            </div>
                                        </td>
                                        <td class="text-center text-nowrap">
                                            <span class="badge {{ $st['class'] }} rounded-pill px-2.5 py-1 fw-bold text-xs border" style="font-size: 10px;">
                                                {{ $st['label'] }}
                                            </span>
                                        </td>
                                        <td class="text-end text-nowrap pe-3">
                                            <a href="{{ route('tenant.santri.show', $santri) }}" class="btn btn-link btn-info p-2" data-bs-toggle="tooltip" title="Lihat Profil Santri">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-user-slash fa-3x mb-3 text-muted-light"></i>
                        <p class="mb-0 fw-semibold">Belum Ada Santri Terhubung</p>
                        <p class="text-xs mb-0">Wali murid ini belum dihubungkan dengan data santri manapun.</p>
                        @can('manage_santri')
                        <a href="{{ route('tenant.santri.create') }}" class="btn btn-link btn-xs mt-2 decoration-none fw-bold"><i class="fas fa-plus-circle me-1"></i> Daftarkan Santri Baru</a>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

<style>
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

    .btn-round { border-radius: 50px; }
    .text-slate { color: #475569; }
</style>
@endsection
