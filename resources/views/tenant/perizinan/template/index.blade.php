@extends('layouts.tenant')

@section('title', 'Template Perizinan')

@section('content')
<div class="container-fluid" style="background: #f4f7f6; min-height: 90vh;">
    <div class="page-inner py-5">
        
       {{-- HEADER --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h2 class="fw-extrabold text-dark mb-1">
                    <i class="fas fa-file-contract text-primary me-2"></i> Template Perizinan
                </h2>
                <p class="text-muted fw-medium">Kelola format surat izin santri untuk berbagai keperluan operasional.</p>
            </div>
            <div class="mt-3 mt-md-0 d-flex gap-2">
                <a href="{{ route('tenant.template-perizinan.upload') }}" class="btn btn-outline-primary btn-round px-4 shadow-sm">
                    <i class="fas fa-upload me-2"></i> Upload File
                </a>
                <a href="{{ route('tenant.template-perizinan.create') }}" class="btn btn-primary btn-round px-4 shadow-sm">
                    <i class="fas fa-plus-circle me-2"></i> Buat Template Baru
                </a>
            </div>
        </div>

        {{-- ALERT SUCCESS --}}
        @if(session('success'))
        <div class="alert alert-glass alert-success border-0 shadow-sm mb-4 animate__animated animate__fadeIn">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fa-lg me-3"></i>
                <span class="fw-bold">{{ session('success') }}</span>
            </div>
        </div>
        @endif

        <div class="card card-round border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4" style="width: 40%;">Detail Template</th>
                                <th class="text-center">Konfigurasi Cetak</th>
                                <th class="text-center">Status Keaktifan</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $template)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-title rounded-circle bg-soft-primary text-primary font-weight-bold">
                                                {{ substr($template->nama, 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="d-flex align-items-center">
                                                <h6 class="fw-bold mb-0 text-dark me-2">{{ $template->nama }}</h6>
                                                @if($template->is_default)
                                                    <span class="badge badge-xs bg-info text-white rounded-pill">Default</span>
                                                @endif
                                            </div>
                                            <small class="text-muted d-block mt-1">{{ $template->deskripsi ?: 'Tidak ada deskripsi' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex align-items-center bg-light px-3 py-1 rounded-pill">
                                        <i class="fas fa-print text-muted me-2 small"></i>
                                        <span class="small fw-bold text-dark">Layout {{ $template->layout_print }}</span>
                                    </div>
                                    <div class="mt-1">
                                        <small class="text-muted" style="font-size: 10px;">
                                            @if($template->layout_print == 1) 1 Lembar Penuh @elseif($template->layout_print == 2) Setengah Lembar @else Seperempat Lembar @endif
                                        </small>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($template->is_active)
                                        <span class="badge bg-success-gradient px-3 py-2 rounded-pill shadow-sm">
                                            <i class="fas fa-check-circle me-1"></i> AKTIF
                                        </span>
                                    @else
                                        <span class="badge bg-secondary px-3 py-2 rounded-pill shadow-sm">
                                            <i class="fas fa-times-circle me-1"></i> NONAKTIF
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('tenant.template-perizinan.edit', $template->id) }}" 
                                           class="btn btn-icon btn-link btn-primary btn-lg" 
                                           data-bs-toggle="tooltip" title="Ubah Template">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        
                                        <form action="{{ route('tenant.template-perizinan.destroy', $template->id) }}" 
                                              method="POST" 
                                              id="delete-form-{{ $template->id }}"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                    onclick="confirmDelete({{ $template->id }})"
                                                    class="btn btn-icon btn-link btn-danger btn-lg" 
                                                    data-bs-toggle="tooltip" title="Hapus Template">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-layer-group fa-3x text-light mb-3"></i>
                                        <h6 class="text-muted fw-bold">Belum Ada Template Tersedia</h6>
                                        <p class="text-muted small">Mulai buat template pertama untuk memudahkan cetak surat izin.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .fw-extrabold { font-weight: 800; }
    .bg-soft-primary { background-color: rgba(21, 114, 232, 0.1); }
    .bg-success-gradient { background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important; }
    .card-round { border-radius: 12px !important; }
    .alert-glass { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(5px); }
    
    /* Hover effect row */
    .table tbody tr { transition: background 0.2s ease; }
    .table tbody tr:hover { background-color: rgba(0,0,0,0.015); }
    
    /* Avatar style */
    .avatar-title { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });

    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus Template?',
            text: "Template yang dihapus tidak dapat dipulihkan kembali.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $(`#delete-form-${id}`).submit();
            }
        })
    }
</script>
@endpush