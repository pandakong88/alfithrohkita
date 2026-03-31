@extends('layouts.tenant')

@section('title', 'Detail Template Survey')

@section('content')
<div class="container">
    <div class="page-inner py-5">
        <div class="max-w-5xl mx-auto">
            
            {{-- BREADCRUMB / BACK BUTTON --}}
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb breadcrumb-style-1">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.import-templates.index') }}">Template Survey</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail</li>
                </ol>
            </nav>

            {{-- HEADER SECTION --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center">
                    <div class="icon-avatar bg-primary-gradient text-white me-3 shadow-sm">
                        <i class="fas fa-file-excel fa-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-dark fw-bold mb-0">{{ $template->nama_template }}</h2>
                        <p class="text-muted mb-0">Dibuat pada {{ $template->created_at->format('d M Y') }}</p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('tenant.import-templates.index') }}" class="btn btn-light btn-round border shadow-sm">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                    <a href="{{ route('tenant.import-templates.download', $template->id) }}" class="btn btn-primary btn-round shadow-sm">
                        <i class="fas fa-download me-1"></i> Download Template
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-round border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <div class="card-head-row">
                                <div class="card-title fw-bold text-dark">
                                    <i class="fas fa-list-ol me-2 text-primary"></i>Struktur Urutan Kolom Excel
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr class="text-muted">
                                            <th class="ps-4" style="width: 100px;">Kolom</th>
                                            <th>Label Field</th>
                                            <th>Key / Identitas</th>
                                            <th class="pe-4 text-center">Tipe Entitas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($fields as $index => $field)
                                        <tr>
                                            <td class="ps-4">
                                                <span class="badge badge-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                                    {{ $index + 1 }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-dark">{{ $field->label }}</span>
                                            </td>
                                            <td>
                                                <code>{{ $field->field_key }}</code>
                                            </td>
                                            <td class="pe-4 text-center">
                                                @php
                                                    // Mapping warna SOLID & Icon
                                                    $entityConfig = [
                                                        'santri'   => ['bg' => 'bg-info', 'icon' => 'fa-user-graduate'],
                                                        'wali'     => ['bg' => 'bg-success', 'icon' => 'fa-users'],
                                                        'akademik' => ['bg' => 'bg-warning', 'icon' => 'fa-book-open'],
                                                        'asrama'   => ['bg' => 'bg-secondary', 'icon' => 'fa-bed'],
                                                        'keamanan' => ['bg' => 'bg-danger', 'icon' => 'fa-shield-alt'],
                                                    ][strtolower($field->entity)] ?? ['bg' => 'bg-dark', 'icon' => 'fa-database'];
                                                @endphp
                                            
                                                <div class="d-inline-flex align-items-center">
                                                    <span class="badge {{ $entityConfig['bg'] }} text-white px-3 py-2 d-flex align-items-center shadow-sm" 
                                                          style="border-radius: 6px; font-weight: 700; font-size: 10px; min-width: 100px; justify-content: center;">
                                                        <i class="fas {{ $entityConfig['icon'] }} me-2"></i>
                                                        {{ strtoupper($field->entity) }}
                                                    </span>
                                                </div>
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
                                Pastikan urutan kolom di file Excel Anda sama persis dengan urutan di atas untuk menghindari kegagalan import.
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
    .icon-avatar {
        width: 50px; height: 50px; display: flex; 
        align-items: center; justify-content: center; border-radius: 12px;
    }
    .card-round { border-radius: 15px !important; }
    .bg-light-soft { background-color: #f8f9fa; }
    code {
        background-color: #f1f4f8;
        color: #e83e8c;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 12px;
    }
</style>
@endsection