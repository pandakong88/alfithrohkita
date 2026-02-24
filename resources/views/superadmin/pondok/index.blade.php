@extends('layouts.superadmin')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">Manajemen Pondok</h3>

    <a href="{{ route('superadmin.pondok.create') }}" 
       class="btn btn-primary btn-round">
        <i class="fas fa-plus"></i> Tambah Pondok
    </a>
</div>

@if(session('success'))
    <div id="pondok-success-message" data-message="{{ session('success') }}"></div>
@endif

<div class="card">
    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle" id="datatable-pondok">
                <thead class="table-light">
                    <tr>
                        <th>Nama PonPes</th>
                        <th>Alamat</th>
                        <th>No .Telp</th>
                        <th>Status</th>
                        <th width="220" class="text-center align-middle">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pondoks as $pondok)
                        <tr>
                            <td>{{ $pondok->name }}</td>
                            <td>{{ $pondok->address }}</td>
                            <td>{{ $pondok->phone }}</td>
                            <td>
                                @if($pondok->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center align-middle" style="vertical-align: middle;">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('superadmin.pondok.edit', $pondok) }}" 
                                       class="btn btn-sm btn-warning" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('superadmin.pondok.toggle', $pondok) }}" 
                                          method="POST" 
                                          class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="btn btn-sm btn-info" 
                                                title="Ubah Status">
                                            <i class="fas fa-power-off"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('superadmin.pondok.destroy', $pondok) }}" 
                                          method="POST" 
                                          class="d-inline delete-pondok-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-sm btn-danger btn-delete-pondok" 
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Belum ada data pondok
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('kaiadmin/assets/js/plugin/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('kaiadmin/assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>
<script src="{{ asset('kaiadmin/assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#datatable-pondok').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
            }
        });

        // SweetAlert + Notify for success
        var msg = $('#pondok-success-message').data('message');
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
        $('.btn-delete-pondok').on('click', function(e) {
            // 1. Tambahkan ini untuk mencegah form submit langsung
            e.preventDefault(); 
            
            var form = $(this).closest('form');
            
            swal({
                title: 'Yakin hapus pondok ini?',
                text: 'Data pondok akan dihapus (soft delete)!',
                icon: 'warning',
                buttons: {
                    cancel: {
                        text: 'Batal',
                        visible: true,
                        className: 'btn btn-secondary',
                        value: null // Pastikan value null untuk batal
                    },
                    confirm: {
                        text: 'Hapus',
                        visible: true,
                        className: 'btn btn-danger',
                        value: true // Memberikan nilai true jika diklik
                    }
                },
                dangerMode: true,
            }).then(function(willDelete) {
                // 2. Jika user menekan tombol 'Hapus' (willDelete bernilai true)
                if (willDelete) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush