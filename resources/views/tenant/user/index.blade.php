@extends('layouts.tenant')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark">Manajemen User</h3>
            <p class="text-muted small mb-0">Kelola hak akses pengguna, perizinan, dan konfigurasi profil untuk staf pondok.</p>
        </div>
        @can('manage_users')
        <div class="d-flex gap-2">
            <a href="{{ route('tenant.user.trash') }}" class="btn btn-soft-danger btn-round shadow-sm">
                <i class="fas fa-archive me-1"></i> User Trash
            </a>
            
            <a href="{{ route('tenant.user.create') }}" class="btn btn-primary btn-round shadow-sm">
                <i class="fas fa-user-plus me-1"></i> Tambah User
            </a>
        </div>
        @endcan
    </div>

    @if(session('success'))
        <div id="user-success-message" data-message="{{ session('success') }}"></div>
    @endif

    {{-- STATS SECTION --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4">
            <div class="card card-stats card-round border-0 shadow-sm mb-0 card-custom">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small bg-primary text-white" style="border-radius: 12px; width: 45px; height: 45px; line-height: 45px;">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3">
                            <div class="numbers">
                                <p class="card-category text-muted text-xs fw-semibold mb-0">TOTAL USER</p>
                                <h4 class="card-title fw-bold text-dark mb-0 mt-0.5">{{ $users->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-4">
            <div class="card card-stats card-round border-0 shadow-sm mb-0 card-custom">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-success bubble-shadow-small bg-success text-white" style="border-radius: 12px; width: 45px; height: 45px; line-height: 45px;">
                                <i class="fas fa-user-check"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3">
                            <div class="numbers">
                                <p class="card-category text-muted text-xs fw-semibold mb-0">USER AKTIF</p>
                                <h4 class="card-title fw-bold text-success mb-0 mt-0.5">{{ $users->where('is_active', true)->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card card-stats card-round border-0 shadow-sm mb-0 card-custom">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-danger bubble-shadow-small bg-danger text-white" style="border-radius: 12px; width: 45px; height: 45px; line-height: 45px;">
                                <i class="fas fa-user-minus"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3">
                            <div class="numbers">
                                <p class="card-category text-muted text-xs fw-semibold mb-0">USER NON-AKTIF</p>
                                <h4 class="card-title fw-bold text-danger mb-0 mt-0.5">{{ $users->where('is_active', false)->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-custom border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle" id="datatable-user">
                    <thead>
                        <tr>
                            <th style="min-width: 250px">Nama & Email</th>
                            <th>Role / Akses</th>
                            <th class="text-center">Status</th>
                            @can('manage_users')
                            <th style="width: 140px;" class="text-center">Aksi</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-wrapper me-3">
                                            <div class="avatar-title-custom">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark mb-0">{{ $user->name }}</div>
                                            <span class="text-muted small font-monospace">{{ $user->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @forelse($user->roles as $role)
                                        <span class="badge badge-role badge-role-{{ str_contains($role->name, 'admin') ? 'admin' : 'staff' }}">
                                            <i class="fas fa-shield-alt me-1"></i> {{ format_role_name($role->name) }}
                                        </span>
                                    @empty
                                        <span class="badge bg-secondary text-white">Tanpa Role</span>
                                    @endforelse
                                </td>
                                <td class="text-center">
                                    @if($user->is_active)
                                        <span class="badge badge-status badge-status-active">
                                            <span class="dot dot-active"></span> Aktif
                                        </span>
                                    @else
                                        <span class="badge badge-status badge-status-inactive">
                                            <span class="dot dot-inactive"></span> Nonaktif
                                        </span>
                                    @endif
                                </td>
                                @can('manage_users')
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('tenant.user.edit', $user) }}" 
                                           class="btn btn-action btn-edit shadow-sm" 
                                           data-bs-toggle="tooltip" 
                                           title="Edit Pengguna">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ route('tenant.user.toggle', $user) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="btn btn-action btn-toggle-status {{ $user->is_active ? 'active' : 'inactive' }} shadow-sm" 
                                                    data-bs-toggle="tooltip" 
                                                    title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <i class="fas {{ $user->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('tenant.user.destroy', $user) }}" method="POST" class="d-inline delete-user-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-action btn-delete btn-delete-user shadow-sm" 
                                                    data-bs-toggle="tooltip" 
                                                    title="Hapus Pengguna">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                @endcan
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
                                    <div class="py-4">
                                        <i class="fas fa-users-slash text-muted mb-3" style="font-size: 40px;"></i>
                                        <p class="mb-0 fw-bold">Belum ada data user</p>
                                        <small class="text-muted">Tambahkan pengguna baru untuk memberikan akses.</small>
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

<style>
    /* Card Styling */
    .card-custom {
        border-radius: 16px;
        background: #ffffff;
    }
    
    /* Table Styling */
    .table thead th {
        font-weight: 700;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.8px;
        color: #8A92A6;
        border-bottom: 2px solid #EDF2F7;
        padding: 16px 24px;
        background-color: #FAFCFF;
    }
    .table tbody td {
        padding: 18px 24px;
        border-bottom: 1px solid #EDF2F7;
    }
    .table tbody tr:hover {
        background-color: #FAFCFF;
    }
    
    /* Avatar Styling */
    .avatar-wrapper {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .avatar-title-custom {
        width: 100%;
        height: 100%;
        border-radius: 12px;
        background: linear-gradient(135deg, #4F46E5 0%, #3B82F6 100%);
        color: #ffffff;
        font-weight: 700;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.15);
    }
    
    /* Buttons Custom */
    .btn-round {
        border-radius: 50px;
        padding: 0.6rem 1.4rem;
        font-weight: 600;
    }
    .btn-soft-danger {
        background-color: rgba(220, 53, 69, 0.08);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.15);
    }
    .btn-soft-danger:hover {
        background-color: #dc3545;
        color: #ffffff;
    }
    
    /* Badge Roles */
    .badge-role {
        border-radius: 8px;
        padding: 6px 12px;
        font-weight: 600;
        font-size: 12px;
    }
    .badge-role-admin {
        background-color: rgba(79, 70, 229, 0.08);
        color: #4F46E5;
    }
    .badge-role-staff {
        background-color: rgba(16, 185, 129, 0.08);
        color: #10B981;
    }
    
    /* Badge Status */
    .badge-status {
        border-radius: 50px;
        padding: 6px 14px;
        font-weight: 600;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
    }
    .badge-status-active {
        background-color: rgba(16, 185, 129, 0.08);
        color: #10B981;
    }
    .badge-status-inactive {
        background-color: rgba(107, 114, 128, 0.08);
        color: #6B7280;
    }
    .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        margin-right: 6px;
        display: inline-block;
    }
    .dot-active { background-color: #10B981; }
    .dot-inactive { background-color: #6B7280; }
    
    /* Action Buttons */
    .btn-action {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        padding: 0;
    }
    .btn-edit {
        background-color: rgba(245, 158, 11, 0.08);
        color: #F59E0B;
    }
    .btn-edit:hover {
        background-color: #F59E0B;
        color: #ffffff;
        transform: translateY(-2px);
    }
    .btn-toggle-status.active {
        background-color: rgba(59, 130, 246, 0.08);
        color: #3B82F6;
    }
    .btn-toggle-status.active:hover {
        background-color: #3B82F6;
        color: #ffffff;
        transform: translateY(-2px);
    }
    .btn-toggle-status.inactive {
        background-color: rgba(107, 114, 128, 0.08);
        color: #6B7280;
    }
    .btn-toggle-status.inactive:hover {
        background-color: #6B7280;
        color: #ffffff;
        transform: translateY(-2px);
    }
    .btn-delete {
        background-color: rgba(239, 68, 68, 0.08);
        color: #EF6868;
    }
    .btn-delete:hover {
        background-color: #EF6868;
        color: #ffffff;
        transform: translateY(-2px);
    }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // 1. Inisialisasi DataTable
        $('#datatable-user').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
            },
            "pageLength": 10,
        });

        // 2. SweetAlert + Notify untuk Success
        var msg = $('#user-success-message').data('message');
        if(msg) {
            swal({
                title: 'Berhasil!',
                text: msg,
                icon: 'success',
                timer: 2000,
                buttons: false
            });
            $.notify({
                icon: 'fa fa-check',
                message: msg
            },{
                type: 'success',
                placement: { from: 'bottom', align: 'right' },
                delay: 2000,
                timer: 500
            });
        }

        // 3. SweetAlert Confirm untuk Delete
        $('.btn-delete-user').on('click', function(e) {
            e.preventDefault();
            var form = $(this).closest('form');
            swal({
                title: 'Yakin hapus user ini?',
                text: 'Data pengguna akan dihapus permanen dari sistem!',
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