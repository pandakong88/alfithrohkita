@extends('layouts.tenant')

@section('content')
<div class="page-inner" style="padding-top: 15px !important;">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-trash-alt text-danger me-2"></i>Tong Sampah Santri</h3>
            <p class="text-muted small mb-0">Kelola data santri yang telah dihapus sementara.</p>
        </div>
        <a href="{{ route('tenant.santri.index') }}" class="btn btn-outline-primary btn-round btn-sm">
            <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar Aktif
        </a>
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4" style="width: 150px;">NIS</th>
                            <th>Nama Lengkap</th>
                            <th>Dihapus Pada</th>
                            <th class="text-center" style="width: 250px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($santris as $santri)
                            <tr>
                                <td class="ps-4 fw-bold text-primary">{{ $santri->nis }}</td>
                                <td>
                                    <div class="fw-bold">{{ $santri->nama_lengkap }}</div>
                                    <small class="text-muted">{{ $santri->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</small>
                                </td>
                                <td>
                                    <span class="text-muted small">
                                        {{ $santri->deleted_at->translatedFormat('d M Y, H:i') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        {{-- Tombol Pemicu Restore --}}
                                        <button type="button" class="btn btn-sm btn-outline-success btn-round px-3" 
                                                onclick="confirmAction('restore', '{{ $santri->id }}', '{{ $santri->nama_lengkap }}')">
                                            <i class="fas fa-undo me-1"></i> Pulihkan
                                        </button>

                                        {{-- Tombol Pemicu Force Delete --}}
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-round px-3" 
                                                onclick="confirmAction('force-delete', '{{ $santri->id }}', '{{ $santri->nama_lengkap }}')">
                                            <i class="fas fa-times me-1"></i> Hapus Permanen
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <p class="text-muted">Tidak ada data santri di tong sampah.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL KONFIRMASI DINAMIS --}}
<div class="modal fade" id="confirmTrashModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body text-center p-4">
                <div id="modalIconContainer" class="mb-3">
                    {{-- Icon akan diisi via JS --}}
                </div>
                <h5 class="fw-bold" id="modalTitle">Konfirmasi</h5>
                <p class="text-muted small" id="modalMessage"></p>
                
                <form id="formActionTrash" method="POST" action="">
                    @csrf
                    <input type="hidden" name="_method" id="methodField" value="PATCH">
                    
                    <div class="d-grid gap-2">
                        <button type="submit" id="confirmBtn" class="btn btn-round">Ya, Lanjutkan</button>
                        <button type="button" class="btn btn-light btn-round" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-round { border-radius: 50px; }
    .card { border-radius: 15px; }
    .table thead th { font-size: 11px; letter-spacing: 1px; }
</style>
@endsection

@push('scripts')
<script>
    function confirmAction(type, id, nama) {
        let form = $('#formActionTrash');
        let method = $('#methodField');
        let icon = $('#modalIconContainer');
        let btn = $('#confirmBtn');
        let title = $('#modalTitle');
        let msg = $('#modalMessage');
        
        // Reset Class
        btn.removeClass('btn-danger btn-success');

        if (type === 'restore') {
            // Setup Modal untuk Restore
            title.text('Pulihkan Santri?');
            msg.html('Data <b>' + nama + '</b> akan dikembalikan ke daftar santri aktif.');
            icon.html('<i class="fas fa-undo text-success fa-3x"></i>');
            btn.addClass('btn-success').text('Ya, Pulihkan');
            method.val('PATCH');
            form.attr('action', "{{ url('tenant/santri') }}/" + id + "/restore");
            
        } else if (type === 'force-delete') {
            // Setup Modal untuk Force Delete
            title.text('Hapus Permanen?');
            msg.html('Data <b>' + nama + '</b> akan dihapus selamanya dan tidak bisa dikembalikan!');
            icon.html('<i class="fas fa-exclamation-triangle text-danger fa-3x"></i>');
            btn.addClass('btn-danger').text('Ya, Hapus Selamanya');
            method.val('DELETE');
            form.attr('action', "{{ url('tenant/santri') }}/" + id + "/force-delete");
        }

        $('#confirmTrashModal').modal('show');
    }
</script>
@endpush