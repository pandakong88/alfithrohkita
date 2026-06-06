@extends('layouts.tenant')

@section('title', 'Detail Template Survey')

@section('content')
<div class="container">
    <div class="page-inner py-4" style="padding-top: 15px !important;">
        <div class="max-w-5xl mx-auto">
            
            {{-- BREADCRUMB --}}
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb breadcrumb-style-1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.import-templates.index') }}">Template Survey</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail</li>
                </ol>
            </nav>

            {{-- HEADER SECTION --}}
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                <div class="d-flex align-items-center">
                    <div class="icon-avatar bg-primary-gradient text-white me-3 shadow-sm">
                        <i class="fas fa-file-excel fa-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-dark fw-bold mb-0" style="font-size: 1.6rem;">{{ $template->nama_template }}</h3>
                        <p class="text-muted mb-0 small">Dibuat pada {{ $template->created_at->format('d M Y') }} • Terakhir diperbarui {{ $template->updated_at->format('d M Y') }}</p>
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <a href="{{ route('tenant.import-templates.index') }}" class="btn btn-light btn-round border shadow-sm btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                    <a href="{{ route('tenant.import-templates.edit', $template->id) }}" class="btn btn-warning btn-round shadow-sm btn-sm text-white" style="background-color: #ffa534 !important; border-color: #ffa534 !important;">
                        <i class="fas fa-edit me-1"></i> Edit Template
                    </a>
                    
                    {{-- Dropdown Unduh Excel --}}
                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn btn-primary btn-round shadow-sm btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-download me-1"></i> Unduh Excel
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="font-size: 13px; min-width: 240px; margin-top: 5px;">
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('tenant.import-templates.download', [$template->id, 'with_data' => 'false']) }}">
                                    <i class="fas fa-file-excel text-success me-2" style="width: 16px;"></i> 
                                    <strong>Download Template Kosong</strong>
                                    <small class="text-muted d-block mt-0.5">Untuk tambah santri baru massal</small>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('tenant.import-templates.download', [$template->id, 'with_data' => 'true']) }}">
                                    <i class="fas fa-database text-primary me-2" style="width: 16px;"></i> 
                                    <strong>Download + Data Santri</strong>
                                    <small class="text-muted d-block mt-0.5">Untuk edit / update massal data lama</small>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- SUMMARY STATS CARD --}}
            <div class="row mb-4">
                <div class="col-6 col-md-4">
                    <div class="card card-round border-0 shadow-sm mb-0 h-100" style="transition: transform 0.2s ease;">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="rounded-3 d-flex align-items-center justify-content-center bg-primary-gradient text-white shadow-sm" style="width: 44px; height: 44px; min-width: 44px;">
                                <i class="fas fa-columns fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0 small" style="font-size: 11px;">Total Kolom</h6>
                                <h4 class="fw-bold text-dark mb-0">{{ count($fields) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    @php
                        $wajibCount = $fields->filter(function($f) {
                            return in_array($f->field_key, ['nis', 'nama_lengkap', 'jenis_kelamin']);
                        })->count();
                    @endphp
                    <div class="card card-round border-0 shadow-sm mb-0 h-100" style="transition: transform 0.2s ease;">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="rounded-3 d-flex align-items-center justify-content-center bg-warning-gradient text-white shadow-sm" style="width: 44px; height: 44px; min-width: 44px;">
                                <i class="fas fa-lock fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0 small" style="font-size: 11px;">Kolom Wajib (Inti)</h6>
                                <h4 class="fw-bold text-dark mb-0">{{ $wajibCount }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4 mt-3 mt-md-0">
                    @php
                        $customCount = $fields->filter(function($f) {
                            return $f->entity === 'custom';
                        })->count();
                    @endphp
                    <div class="card card-round border-0 shadow-sm mb-0 h-100" style="transition: transform 0.2s ease;">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="rounded-3 d-flex align-items-center justify-content-center bg-success-gradient text-white shadow-sm" style="width: 44px; height: 44px; min-width: 44px;">
                                <i class="fas fa-tags fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0 small" style="font-size: 11px;">Kolom Kustom</h6>
                                <h4 class="fw-bold text-dark mb-0">{{ $customCount }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- EXCEL MOCKUP PREVIEW --}}
            <div class="card card-round border-0 shadow-sm mb-4 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold text-dark" style="font-size: 14px;">
                        <i class="fas fa-th me-2 text-success"></i>Visualisasi Header File Excel
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 mockup-table" style="border-collapse: collapse; min-width: 800px;">
                            <thead>
                                <tr style="background-color: #f1f3f4; color: #5f6368; font-size: 11px; font-weight: bold; text-align: center;">
                                    <th style="width: 50px; border: 1px solid #dadce0; padding: 6px;"></th>
                                    @foreach($fields as $index => $field)
                                        <th style="border: 1px solid #dadce0; padding: 6px 12px; font-weight: bold;">{{ chr(65 + ($index % 26)) . ($index >= 26 ? floor($index / 26) : '') }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Baris 1: DB Key --}}
                                <tr>
                                    <td class="text-center text-muted fw-bold" style="background-color: #f1f3f4; border: 1px solid #dadce0; font-size: 11px; width: 50px;">1</td>
                                    @foreach($fields as $field)
                                        <td style="border: 1px solid #dadce0; padding: 8px 12px; background-color: #fffbf0; text-align: center;">
                                            <code style="font-size: 10px; color: #b78103; font-weight: bold; border-color: #ffd591;">{{ $field->field_key }}</code>
                                        </td>
                                    @endforeach
                                </tr>
                                {{-- Baris 2: Label --}}
                                <tr>
                                    <td class="text-center text-muted fw-bold" style="background-color: #f1f3f4; border: 1px solid #dadce0; font-size: 11px; width: 50px;">2</td>
                                    @foreach($fields as $field)
                                        <td class="fw-bold text-center" style="border: 1px solid #dadce0; padding: 8px 12px; background-color: #f8fafd; font-size: 12px; color: #1572e8;">
                                            {{ $field->label }}
                                        </td>
                                    @endforeach
                                </tr>
                                {{-- Baris 3: Contoh --}}
                                <tr>
                                    <td class="text-center text-muted fw-bold" style="background-color: #f1f3f4; border: 1px solid #dadce0; font-size: 11px; width: 50px;">3</td>
                                    @foreach($fields as $field)
                                        @php
                                            $exampleText = 'Contoh Data';
                                            if ($field->field_key === 'nis') $exampleText = '24250001';
                                            elseif ($field->field_key === 'nama_lengkap') $exampleText = 'Muhammad Zaki';
                                            elseif ($field->field_key === 'jenis_kelamin') $exampleText = 'Laki-laki';
                                            elseif ($field->field_key === 'no_hp') $exampleText = '081234567890';
                                            elseif ($field->field_key === 'alamat') $exampleText = 'Jl. Kenanga No. 12';
                                        @endphp
                                        <td class="text-muted text-center" style="border: 1px solid #dadce0; padding: 8px 12px; font-style: italic; font-size: 11px;">
                                            {{ $exampleText }}
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer py-2 px-3" style="background-color: #fbfcfe; border-top: 1px solid #e3ebf6;">
                    <span class="text-muted small" style="font-size: 11px;">
                        <i class="fas fa-info-circle text-primary me-1"></i> Baris 1 berisi key sistem (disembunyikan oleh sistem / jangan diubah). Baris 2 adalah judul kolom visual Excel Anda.
                    </span>
                </div>
            </div>

            {{-- DETAILED STRUKTUR TABLE --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-round border-0 shadow-sm overflow-hidden">
                        <div class="card-header bg-white border-bottom py-3">
                            <div class="card-head-row">
                                <div class="card-title fw-bold text-dark" style="font-size: 14px;">
                                    <i class="fas fa-list-ol me-2 text-primary"></i>Daftar Urutan & Validasi Kolom Excel
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr class="text-muted" style="font-size: 12px;">
                                            <th class="ps-4" style="width: 110px;">No Kolom</th>
                                            <th>Label Field</th>
                                            <th>Key / Identitas</th>
                                            <th>Kategori Data</th>
                                            <th class="pe-4 text-center">Validasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($fields as $index => $field)
                                        @php
                                            $isWajib = in_array($field->field_key, ['nis', 'nama_lengkap', 'jenis_kelamin']);
                                        @endphp
                                        <tr class="structure-row">
                                            <td class="ps-4">
                                                <span class="badge badge-primary-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-weight: bold; font-size: 11px;">
                                                    {{ $index + 1 }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-dark d-block" style="font-size: 13px;">{{ $field->label }}</span>
                                            </td>
                                            <td>
                                                <code>{{ $field->field_key }}</code>
                                            </td>
                                            <td>
                                                @php
                                                    $entityLabels = [
                                                        'santri'   => ['bg' => 'badge-entity-santri', 'icon' => 'fa-user-graduate', 'text' => 'Santri'],
                                                        'wali'     => ['bg' => 'badge-entity-wali', 'icon' => 'fa-users', 'text' => 'Wali'],
                                                        'akademik' => ['bg' => 'badge-entity-akademik', 'icon' => 'fa-book-open', 'text' => 'Akademik'],
                                                        'asrama'   => ['bg' => 'badge-entity-asrama', 'icon' => 'fa-bed', 'text' => 'Asrama'],
                                                        'keamanan' => ['bg' => 'badge-entity-keamanan', 'icon' => 'fa-shield-alt', 'text' => 'Keamanan'],
                                                        'custom'   => ['bg' => 'badge-entity-custom', 'icon' => 'fa-tags', 'text' => 'Kustom'],
                                                    ];
                                                    $conf = $entityLabels[strtolower($field->entity)] ?? ['bg' => 'badge-entity-other', 'icon' => 'fa-database', 'text' => strtoupper($field->entity)];
                                                @endphp
                                                <span class="badge {{ $conf['bg'] }} px-2 py-1 align-items-center gap-1" style="border-radius: 4px; font-size: 11px; font-weight: 500;">
                                                    <i class="fas {{ $conf['icon'] }} me-1"></i> {{ $conf['text'] }}
                                                </span>
                                            </td>
                                            <td class="pe-4 text-center">
                                                @if($isWajib)
                                                    <span class="badge bg-warning-light text-warning-dark px-2 py-1 rounded" style="font-size: 10px; font-weight: bold; border: 1px solid #ffd591;">
                                                        <i class="fas fa-lock me-1"></i> Wajib
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-muted px-2 py-1 rounded border" style="font-size: 10px;">
                                                        Opsional
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-light-soft border-top p-4 text-center">
                            <p class="text-muted small mb-0">
                                <i class="fas fa-info-circle me-1"></i> 
                                Pastikan urutan kolom di file Excel Anda sama persis dengan urutan di atas untuk menghindari kegagalan import data.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .bg-primary-gradient {
        background: linear-gradient(135deg, #1572e8 0%, #04befe 100%) !important;
    }
    .bg-warning-gradient {
        background: linear-gradient(135deg, #ffa534 0%, #ffc107 100%) !important;
    }
    .bg-success-gradient {
        background: linear-gradient(135deg, #2bb930 0%, #66bb6a 100%) !important;
    }
    .icon-avatar {
        width: 50px; height: 50px; display: flex; 
        align-items: center; justify-content: center; border-radius: 12px;
    }
    .card-round { border-radius: 15px !important; }
    .bg-light-soft { background-color: #fbfcfe; }
    
    code {
        background-color: #f1f4f8;
        color: #e83e8c;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 12px;
        border: 1px solid #dadce0;
    }

    .badge-primary-light {
        background-color: #e1f0ff !important;
        color: #1572e8 !important;
    }

    .badge-entity-santri { background-color: #e6f4ff; color: #0958d9; border: 1px solid #91caee; }
    .badge-entity-wali { background-color: #f6ffed; color: #389e0d; border: 1px solid #b7eb8f; }
    .badge-entity-akademik { background-color: #fff7e6; color: #d46b08; border: 1px solid #ffd591; }
    .badge-entity-asrama { background-color: #fff0f6; color: #c41d7f; border: 1px solid #ffadd2; }
    .badge-entity-keamanan { background-color: #fff2e8; color: #d4380d; border: 1px solid #ffbb96; }
    .badge-entity-custom { background-color: #f9f0ff; color: #531dab; border: 1px solid #d3adf7; }
    .badge-entity-other { background-color: #f5f5f5; color: #595959; border: 1px solid #d9d9d9; }

    .bg-warning-light { background-color: #fffbeb !important; }
    .text-warning-dark { color: #b78103 !important; }

    .structure-row {
        transition: background-color 0.2s ease;
    }
    .structure-row:hover {
        background-color: #f8fafc !important;
    }

    .mockup-table th, .mockup-table td {
        border-color: #dadce0 !important;
        vertical-align: middle;
    }
    .gap-3 { gap: 1rem !important; }
</style>
@endsection