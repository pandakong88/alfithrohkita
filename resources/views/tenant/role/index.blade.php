@extends('layouts.tenant')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark">Manajemen Role</h3>
            <p class="text-muted small mb-0">Atur peran fungsional dan tingkat kewenangan akses untuk pengguna sistem.</p>
        </div>
        @can('manage_users')
        <a href="{{ route('tenant.role.create') }}" class="btn btn-primary btn-round shadow-sm">
            <i class="fas fa-plus me-1"></i> Tambah Role
        </a>
        @endcan
    </div>

    @if(session('success'))
        <div id="role-success-message" data-message="{{ session('success') }}"></div>
    @endif

    {{-- STATS SECTION --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4">
            <div class="card card-stats card-round border-0 shadow-sm mb-0 card-custom">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small bg-primary text-white" style="border-radius: 12px; width: 45px; height: 45px; line-height: 45px;">
                                <i class="fas fa-user-tag"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3">
                            <div class="numbers">
                                <p class="card-category text-muted text-xs fw-semibold mb-0">TOTAL ROLE</p>
                                <h4 class="card-title fw-bold text-dark mb-0 mt-0.5">{{ $roles->count() }}</h4>
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
                                <i class="fas fa-shield-alt"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3">
                            <div class="numbers">
                                <p class="card-category text-muted text-xs fw-semibold mb-0">ROLE BAWAAN</p>
                                <h4 class="card-title fw-bold text-success mb-0 mt-0.5">
                                    {{ $roles->whereIn('name', ['super_admin', 'admin_pondok'])->count() }}
                                </h4>
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
                            <div class="icon-big text-center icon-warning bubble-shadow-small bg-warning text-white" style="border-radius: 12px; width: 45px; height: 45px; line-height: 45px;">
                                <i class="fas fa-user-cog"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3">
                            <div class="numbers">
                                <p class="card-category text-muted text-xs fw-semibold mb-0">ROLE KUSTOM</p>
                                <h4 class="card-title fw-bold text-warning mb-0 mt-0.5">
                                    {{ $roles->whereNotIn('name', ['super_admin', 'admin_pondok'])->count() }}
                                </h4>
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
                <table class="table align-middle" id="datatable-role">
                    <thead>
                        <tr>
                            <th style="min-width: 200px;">Nama Role</th>
                            <th>Hak Akses (Permissions)</th>
                            @can('manage_users')
                            <th style="width: 120px;" class="text-center">Aksi</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="role-icon-wrapper me-3">
                                            <i class="fas fa-user-tag text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ format_role_name($role->name) }}</div>
                                            <span class="text-muted small font-monospace">{{ $role->name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @forelse($role->permissions as $permission)
                                            <span class="badge badge-permission shadow-xs">
                                                <span class="dot-indicator bg-primary"></span>
                                                {{ str_replace('_', ' ', $permission->name) }}
                                            </span>
                                        @empty
                                            <span class="text-muted small italic">Tidak ada permission khusus</span>
                                        @endforelse
                                    </div>
                                </td>
                                @can('manage_users')
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('tenant.role.edit', $role) }}" 
                                           class="btn btn-action btn-edit shadow-sm" 
                                           data-bs-toggle="tooltip" 
                                           title="Edit Role">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('tenant.role.destroy', $role) }}" method="POST" class="d-inline delete-role-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-action btn-delete btn-delete-role shadow-sm" 
                                                    data-bs-toggle="tooltip" 
                                                    title="Hapus Role">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                @endcan
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-5">
                                    <div class="py-4">
                                        <i class="fas fa-shield-alt text-muted mb-3" style="font-size: 40px;"></i>
                                        <p class="mb-0 fw-bold">Belum ada data role</p>
                                        <small class="text-muted">Buat peran kustom untuk memisahkan wewenang staf Anda.</small>
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
    /* Card Custom Styling */
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
    
    /* Role Icon Styling */
    .role-icon-wrapper {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background-color: rgba(79, 70, 229, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        box-shadow: inset 0 2px 4px rgba(79, 70, 229, 0.05);
    }
    
    /* Buttons Custom */
    .btn-round {
        border-radius: 50px;
        padding: 0.6rem 1.4rem;
        font-weight: 600;
    }
    
    /* Permission Badges */
    .badge-permission {
        border-radius: 8px;
        padding: 6px 12px;
        font-weight: 600;
        font-size: 11px;
        background-color: #F8FAFC;
        color: #475569;
        border: 1px solid #E2E8F0;
        display: inline-flex;
        align-items: center;
        text-transform: capitalize;
    }
    .dot-indicator {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        margin-right: 6px;
        display: inline-block;
    }
    
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
        $('#datatable-role').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
            },
            "pageLength": 10,
        });

        // SweetAlert + Notify for success
        var msg = $('#role-success-message').data('message');
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
                placement: {
                    from: 'bottom',
                    align: 'right'
                },
                delay: 2000,
                timer: 500
            });
        }

        // SweetAlert confirm for delete
        $('.btn-delete-role').on('click', function(e) {
            e.preventDefault();
            var form = $(this).closest('form');
            swal({
                title: 'Yakin hapus role ini?',
                text: 'Role akan dihapus permanen dari sistem!',
                icon: 'warning',
                buttons: {
                    cancel: {
                        text: 'Batal',
                        visible: true,
                        className: 'btn btn-secondary'
                    },
                    confirm: {
                        text: 'Hapus',
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
