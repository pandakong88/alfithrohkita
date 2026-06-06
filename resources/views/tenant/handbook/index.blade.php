@extends('layouts.tenant')

@section('content')
<div class="page-inner">
    <!-- Header Page -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold mb-1">Buku Pedoman Santri</h3>
            <ul class="breadcrumbs">
                <li class="nav-home">
                    <a href="{{ route('tenant.dashboard') }}">
                        <i class="icon-home text-primary"></i>
                    </a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                    <a href="#" class="text-muted">Buku Pedoman Santri</a>
                </li>
            </ul>
        </div>
        <a href="{{ route('tenant.santri.handbook.create') }}" class="btn btn-primary btn-round">
            <i class="fas fa-plus-circle me-1"></i> Tambah Versi
        </a>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Cards Statistics Row -->
    <div class="row">
        <!-- Card 1: Total Versi -->
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Total Versi</p>
                                <h4 class="card-title">{{ $handbooks->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 2: Versi Aktif -->
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Versi Aktif</p>
                                <h4 class="card-title">
                                    @php
                                        $active = $handbooks->where('status', 'published')->first();
                                    @endphp
                                    {{ $active ? 'v' . $active->version : '-' }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 3: Draf Dokumen -->
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-warning bubble-shadow-small">
                                <i class="fas fa-file-signature"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Draf Dokumen</p>
                                <h4 class="card-title">{{ $handbooks->where('status', 'draft')->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 4: Arsip -->
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-secondary bubble-shadow-small">
                                <i class="fas fa-archive"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Arsip</p>
                                <h4 class="card-title">{{ $handbooks->where('status', 'archived')->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Handbooks List Card -->
    <div class="card card-round">
        <div class="card-header">
            <div class="card-title">Daftar Rilis Buku Pedoman</div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="handbookTable" class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th class="ps-4">Versi</th>
                            <th>Tanggal Rilis</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($handbooks as $item)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-primary">v{{ $item->version }}</span>
                                </td>
                                <td>{{ $item->release_date->format('d M Y') }}</td>
                                <td>
                                    @if($item->status === 'published')
                                        <span class="badge badge-success px-3">
                                             PUBLISHED
                                        </span>
                                    @elseif($item->status === 'draft')
                                        <span class="badge badge-warning text-dark px-3">
                                             DRAFT
                                        </span>
                                    @else
                                        <span class="badge badge-secondary px-3">
                                             ARCHIVED
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted small">
                                        {{ Str::limit($item->description ?? 'Tidak ada keterangan.', 50) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="form-button-action">
                                        <!-- Lihat PDF -->
                                        <a href="{{ asset($item->file_path) }}" 
                                           target="_blank"
                                           class="btn btn-link btn-info"
                                           data-bs-toggle="tooltip" 
                                           title="Buka PDF">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                        <!-- Edit -->
                                        <a href="{{ route('tenant.santri.handbook.edit', $item->id) }}"
                                           class="btn btn-link btn-primary"
                                           data-bs-toggle="tooltip" 
                                           title="Ubah Data">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <!-- Hapus -->
                                        <form action="{{ route('tenant.santri.handbook.destroy', $item->id) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus versi buku pedoman ini? File fisik PDF juga akan dihapus permanen.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-link btn-danger"
                                                    data-bs-toggle="tooltip" 
                                                    title="Hapus">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-info-circle fa-2x mb-3 text-muted"></i>
                                    <p class="m-0">Belum ada rilis buku pedoman santri yang ditambahkan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection