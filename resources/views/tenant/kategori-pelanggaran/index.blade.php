@extends('layouts.tenant')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Master Kategori Pelanggaran</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="#"><i class="flaticon-home"></i></a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <h4 class="card-title mb-0">Daftar Kategori Pelanggaran</h4>
                        @can('manage_pelanggaran')
                        <button class="btn btn-primary btn-round" onclick="tambahKategori()">
                            <i class="fa fa-plus me-1"></i>
                            Tambah Kategori Baru
                        </button>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-kategori" class="display table table-striped table-hover width-100">
                            <thead>
                                <tr>
                                    <th>Nama Pelanggaran</th>
                                    <th>Tingkat</th>
                                    <th>Poin</th>
                                    @can('manage_pelanggaran')
                                    <th style="width: 10%; text-align: center;">Aksi</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $c)
                                <tr>
                                    <td><strong>{{ $c->nama_pelanggaran }}</strong></td>
                                    <td>
                                        @php
                                            $badgeClass = [
                                                'ringan' => 'badge-info',
                                                'sedang' => 'badge-warning',
                                                'berat'  => 'badge-danger',
                                            ][$c->tingkat] ?? 'badge-dark';
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">
                                            {{ ucfirst($c->tingkat) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-black text-white font-weight-bold">{{ $c->poin }} Poin</span>
                                    </td>
                                    @can('manage_pelanggaran')
                                    <td>
                                        <div class="form-button-action justify-content-center">
                                            <button type="button" class="btn btn-link btn-primary btn-lg" 
                                                    onclick="editKategori({{ json_encode($c) }})" 
                                                    data-toggle="tooltip" title="Edit Kategori">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                
                                            <button type="button" class="btn btn-link btn-danger" 
                                                    onclick="confirmDelete('{{ $c->id }}')" 
                                                    data-toggle="tooltip" title="Hapus">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                    @endcan
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form (Dual Function: Store & Update) -->
<div class="modal fade" id="modalKategori" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <span class="fw-mediumbold" id="modalTitle">Tambah</span> 
                    <span class="fw-light">Kategori Pelanggaran</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formKategori" action="{{ route('tenant.kategori-pelanggaran.store') }}" method="POST">
                @csrf
                <!-- ID penentu Create / Update di Controller -->
                <input type="hidden" name="id" id="field_id">
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group form-group-default">
                                <label>Nama Pelanggaran</label>
                                <input type="text" name="nama_pelanggaran" id="field_nama" class="form-control" placeholder="Contoh: Terlambat Berjamaah / Kabur dari Pondok" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6 pr-0">
                            <div class="form-group form-group-default">
                                <label>Tingkat</label>
                                <select name="tingkat" id="field_tingkat" class="form-control" required>
                                    <option value="ringan">Ringan</option>
                                    <option value="sedang">Sedang</option>
                                    <option value="berat">Berat</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group form-group-default">
                                <label>Bobot Poin</label>
                                <input type="number" name="poin" id="field_poin" class="form-control" placeholder="Min: 1" min="1" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary">Simpan Kategori</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Mengubah ID tabel agar tidak bentrok dengan script default bawaan template jika ada
        $('#table-kategori').DataTable({
            "pageLength": 10,
            "columnDefs": [
                { "orderable": false, "targets": 3 } // Kolom Aksi tidak bisa di-sorting
            ],
            "language": {
                "emptyTable": "Belum ada data kategori pelanggaran.",
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ data",
                "zeroRecords": "Data tidak ditemukan",
                "paginate": {
                    "next": "Lanjut",
                    "previous": "Kembali"
                },
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                "infoFiltered": "(disaring dari _MAX_ total data)"
            },
            "drawCallback": function(settings) {
                // Re-inisialisasi Bootstrap Tooltip setiap kali DataTables menggambar ulang tabel (pindah page/search)
                $('[data-toggle="tooltip"]').tooltip({
                    trigger: 'hover'
                });
            }
        });
    });

    function tambahKategori() {
        $('#modalTitle').text('Tambah');
        $('#formKategori')[0].reset();
        $('#field_id').val('');
        $('#modalKategori').modal('show');
    }

    function editKategori(data) {
        $('#modalTitle').text('Edit');
        $('#formKategori')[0].reset();

        $('#field_id').val(data.id);
        $('#field_nama').val(data.nama_pelanggaran);
        $('#field_tingkat').val(data.tingkat);
        $('#field_poin').val(data.poin);
        
        $('#modalKategori').modal('show');
    }

    function confirmDelete(id) {
        swal({
            title: 'Hapus Kategori Pelanggaran?',
            text: "Kategori yang dihapus tidak bisa dikembalikan dan memengaruhi riwayat poin santri!",
            icon: 'warning',
            buttons: {
                cancel: { visible: true, text: 'Batal', className: 'btn btn-danger' },
                confirm: { text: 'Ya, Hapus!', className: 'btn btn-success' }
            }
        }).then((willDelete) => {
            if (willDelete) {
                let url = "{{ route('tenant.kategori-pelanggaran.destroy', ':id') }}";
                url = url.replace(':id', id);
                
                let form = $(`<form action="${url}" method="POST">
                    @csrf
                    @method('DELETE')
                </form>`).appendTo('body');
                
                form.submit();
            }
        });
    }
</script>
@endpush