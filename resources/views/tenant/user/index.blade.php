@extends('layouts.tenant')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Manajemen User</h3>
        <p class="text-muted small mb-0">Daftar pengguna yang memiliki akses ke sistem.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('tenant.user.trash') }}" class="btn btn-outline-danger btn-round">
            <i class="fas fa-trash-alt me-1"></i> User Trash
        </a>
        
        <a href="{{ route('tenant.user.create') }}" class="btn btn-primary btn-round">
            <i class="fas fa-plus me-1"></i> Tambah User
        </a>
    </div>
</div>

@if(session('success'))
    <div id="user-success-message" data-message="{{ session('success') }}"></div>
@endif

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle" id="datatable-user">
                <thead class="table-light">
                    <tr>
                        <th style="width: 250px">Nama & Email</th>
                        <th>Role</th>
                        <th class="text-center">Status</th>
                        <th width="150" class="text-center align-middle">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3">
                                        <span class="avatar-title rounded-circle border border-white bg-primary">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $user->name }}</div>
                                        <div class="small text-muted">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge bg-info text-dark mb-1">
                                        {{ format_role_name($role->name) }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="text-center">
                                @if($user->is_active)
                                    <span class="badge bg-success px-3">Aktif</span>
                                @else
                                    <span class="badge bg-dark px-3 text-white">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('tenant.user.edit', $user) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('tenant.user.toggle', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-info" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <i class="fas {{ $user->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('tenant.user.destroy', $user) }}" method="POST" class="d-inline delete-user-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger btn-delete-user" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Belum ada data user</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Library yang sama dengan referensi Role --}}


<script>
    $(document).ready(function() {
        // 1. Inisialisasi DataTable
        $('#datatable-user').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
            },
            "pageLength": 10,
        });

        // 2. SweetAlert + Notify untuk Success (Sama dengan Role)
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

        // 3. SweetAlert Confirm untuk Delete (Sama dengan Role)
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