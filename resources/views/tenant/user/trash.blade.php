@extends('layouts.tenant')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded-3 bg-dark text-white shadow">
    <div>
        <h3 class="fw-bold mb-1 text-warning">
            <i class="fas fa-exclamation-triangle me-2 animate-pulse"></i> User Archive / Recycle Bin
        </h3>
        <p class="text-white-50 small mb-0">Hati-hati! Memulihkan user akan mengaktifkan kembali akses login mereka.</p>
    </div>
    <a href="{{ route('tenant.user.index') }}" class="btn btn-light btn-round shadow-sm">
        <i class="fas fa-chevron-left me-2"></i> Kembali ke Panel Utama
    </a>
</div>

@if(session('success'))
    <div id="restore-success-trigger" data-message="{{ session('success') }}"></div>
@endif

<div class="card border-0 shadow-lg" style="border-left: 5px solid #dc3545 !important;">
    <div class="card-header bg-white py-3">
        <div class="d-flex align-items-center">
            <span class="badge bg-danger me-2">SENSITIVE AREA</span>
            <h5 class="card-title mb-0 fw-bold text-dark">Daftar Akun Terhapus</h5>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="datatable-recycle-user">
                <thead class="bg-dark text-white">
                    <tr>
                        <th class="ps-4">User Identity</th>
                        <th class="text-center">Dihapus Pada</th>
                        <th width="200" class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center py-2">
                                    <div class="avatar-sm me-3">
                                        <div class="avatar-title rounded-circle bg-danger-gradient shadow-sm">
                                            <i class="fas fa-user-slash text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $user->name }}</div>
                                        <div class="text-danger small fw-semibold">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center text-muted small">
                                {{ $user->deleted_at ? $user->deleted_at->translatedFormat('d M Y, H:i') : '-' }}
                            </td>
                            <td class="text-center pe-4">
                                <form method="POST" action="{{ route('tenant.user.restore', $user->id) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="button" class="btn btn-success btn-sm fw-bold btn-restore-action shadow-sm">
                                        <i class="fas fa-recycle me-1"></i> RESTORE
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 bg-light">
                                <h5 class="text-muted fw-bold">Archive Kosong</h5>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .animate-pulse { animation: pulse-animation 2s infinite; }
    @keyframes pulse-animation { 0% { opacity: 1; } 50% { opacity: 0.4; } 100% { opacity: 1; } }
    .bg-danger-gradient { background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%); }
    .btn-restore-action { border-radius: 8px; transition: 0.3s; }
    .btn-restore-action:hover { transform: scale(1.05); }
</style>
@endsection

@push('scripts')
{{-- Load Jquery & Plugins sesuai path KaiAdmin kamu --}}
<script src="{{ asset('kaiadmin/assets/js/core/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('kaiadmin/assets/js/plugin/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('kaiadmin/assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>
<script src="{{ asset('kaiadmin/assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

<script>
    $(document).ready(function() {
        // 1. Inisialisasi DataTable
        $('#datatable-recycle-user').DataTable({
            "language": { "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json" }
        });

        // 2. LOGIC FIX: Menangkap Notifikasi Berhasil Restore
        var successMessage = $('#restore-success-trigger').data('message');
        if (successMessage) {
            // SweetAlert Popup
            swal({
                title: 'Berhasil!',
                text: successMessage,
                icon: 'success',
                buttons: {
                    confirm: {
                        className: 'btn btn-success'
                    }
                }
            });

            // Bootstrap Notify (Popup kecil di pojok)
            $.notify({
                icon: 'fa fa-check',
                title: 'Restored',
                message: successMessage,
            }, {
                type: 'success',
                placement: { from: "bottom", align: "right" },
                time: 1000,
            });
        }

        // 3. LOGIC FIX: Confirm Sebelum Restore
        $('.btn-restore-action').on('click', function(e) {
            e.preventDefault();
            var form = $(this).closest('form');
            
            swal({
                title: 'Pulihkan User?',
                text: 'Akun ini akan aktif kembali di daftar utama.',
                icon: 'info',
                buttons: {
                    cancel: {
                        visible: true,
                        text: 'Batal',
                        className: 'btn btn-secondary'
                    },
                    confirm: {
                        text: 'Ya, Pulihkan',
                        className: 'btn btn-success'
                    }
                },
            }).then(function(result) {
                if (result) {
                    form.submit(); // Eksekusi submit form
                }
            });
        });
    });
</script>
@endpush