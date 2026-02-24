@extends('layouts.tenant')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">Manajemen Role</h3>
    <a href="{{ route('tenant.role.create') }}" class="btn btn-primary btn-round">
        <i class="fas fa-plus"></i> Tambah Role
    </a>
</div>

@if(session('success'))
    <div id="role-success-message" data-message="{{ session('success') }}"></div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle" id="datatable-role">
                <thead class="table-light">
                    <tr>
                        <th>Nama Role</th>
                        <th>Permissions</th>
                        <th width="180" class="text-center align-middle">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td>
                                {{ format_role_name($role->name) }}
                            </td>
                            <td>
                                @foreach($role->permissions as $permission)
                                    <span class="badge bg-info text-dark mb-1">{{ $permission->name }}</span>
                                @endforeach
                            </td>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('tenant.role.edit', $role) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('tenant.role.destroy', $role) }}" method="POST" class="d-inline delete-role-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger btn-delete-role" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">Belum ada data role</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('kaiadmin/assets/js/core/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('kaiadmin/assets/js/plugin/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('kaiadmin/assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>
<script src="{{ asset('kaiadmin/assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#datatable-role').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
            }
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
                text: 'Role akan dihapus!',
                icon: 'warning',
                buttons: {
                    cancel: {
                        text: 'Batal',
                        visible: true,
                        className: 'btn btn-secondary',
                        value: null
                    },
                    confirm: {
                        text: 'Hapus',
                        visible: true,
                        className: 'btn btn-danger',
                        value: true
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
