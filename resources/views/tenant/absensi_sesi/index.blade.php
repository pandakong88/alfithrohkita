@extends('layouts.tenant')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Master Sesi Absensi</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="#"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Absensi</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Sesi</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Daftar Sesi Kegiatan</h4>
                        <button class="btn btn-primary btn-round ml-auto" onclick="tambahSesi()">
                            <i class="fa fa-plus"></i>
                            Tambah Sesi Baru
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="add-row" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nama Sesi</th>
                                    <th>Target Absensi</th>
                                    <th>Jam Mulai</th>
                                    <th>Jam Selesai</th>
                                    <th style="width: 10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sesis as $s)
                                <tr>
                                    <td><strong>{{ $s->nama_sesi }}</strong></td>
                                    <td>
                                        @php
                                            $badgeClass = [
                                                'global' => 'badge-primary',
                                                'kelas' => 'badge-success',
                                                'kamar' => 'badge-warning',
                                                'komplek' => 'badge-info',
                                                'plotting' => 'badge-secondary'
                                            ][$s->target_tipe] ?? 'badge-dark';
                                        @endphp
                                        
                                        <span class="badge {{ $badgeClass }}">
                                            {{-- Menggunakan accessor display_name agar logic IF di blade hilang --}}
                                            {{ ucfirst($s->target_tipe) }} - {{ $s->target_display_name }}
                                        </span>
                                    </td>
                                    <td><span class="badge badge-count">{{ date('H:i', strtotime($s->jam_mulai)) }}</span></td>
                                    <td><span class="badge badge-count">{{ date('H:i', strtotime($s->jam_selesai)) }}</span></td>
                                    <td>
                                        <div class="form-button-action">
                                            {{-- Link ke Manage Print yang kita buat tadi --}}
                                            <a href="{{ route('tenant.absensi-sesi.manage-print', $s->id) }}" 
                                               class="btn btn-link btn-secondary" 
                                               data-toggle="tooltip" 
                                               title="Cetak Absen Fisik">
                                                <i class="fa fa-print"></i>
                                            </a>
                                
                                            {{-- Tombol khusus plotting --}}
                                            @if($s->target_tipe === 'plotting')
                                            <a href="{{ route('tenant.absensi-sesi.manage', $s->id) }}" 
                                               class="btn btn-link btn-info" 
                                               data-toggle="tooltip" 
                                               title="Atur Anggota Plotting">
                                                <i class="fa fa-users"></i>
                                            </a>
                                            @endif
                                
                                            <button type="button" class="btn btn-link btn-primary" 
                                                    onclick="editSesi({{ json_encode($s) }})" 
                                                    data-toggle="tooltip" title="Edit Sesi">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                
                                            <button type="button" class="btn btn-link btn-danger" 
                                                    onclick="confirmDelete('{{ $s->id }}')" 
                                                    data-toggle="tooltip" title="Hapus">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada data sesi.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div class="modal fade" id="modalSesi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <span class="fw-mediumbold" id="modalTitle">Tambah</span> 
                    <span class="fw-light">Sesi</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formSesi" action="{{ route('tenant.absensi-sesi.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id" id="field_id">
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group form-group-default">
                                <label>Nama Sesi</label>
                                <input type="text" name="nama_sesi" id="field_nama" class="form-control" placeholder="Contoh: Jamaah Subuh / Madrasah Pagi" required>
                            </div>
                        </div>
                        
                        <div class="col-sm-12">
                            <div class="form-group form-group-default">
                                <label>Target Absensi</label>
                                <select name="target_tipe" id="field_target" class="form-control" required onchange="handleTargetChange()">
                                    <option value="global">Seluruh Santri (Global)</option>
                                    <option value="kelas">Per Kelas Madrasah</option>
                                    <option value="kamar">Per Kamar/Gedung</option>
                                    <option value="komplek">Per Komplek</option>
                                    <option value="plotting">Per Kelompok/Plotting (Manual)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Dropdown ID Target (Dinamis) -->
                        <div class="col-sm-12" id="wrapper_target_id" style="display: none;">
                            <div class="form-group form-group-default">
                                <label id="label_target_id">Pilih Item</label>
                                <select name="target_id" id="field_target_id" class="form-control">
                                    <!-- Isi di-generate via JS -->
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 pr-0">
                            <div class="form-group form-group-default">
                                <label>Jam Mulai</label>
                                <input type="time" name="jam_mulai" id="field_mulai" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group form-group-default">
                                <label>Jam Selesai</label>
                                <input type="time" name="jam_selesai" id="field_selesai" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary">Simpan Sesi</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="delete-form" action="" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
    // 1. Ambil data dari Controller (Pastikan variabel di controller namanya sesuai)
    const dataKelas = @json($kelass);
    const dataKamar = @json($kamars);
    const dataKomplek = @json($kompleks); // Tambahkan ini

    function handleTargetChange(selectedId = null) {
        const tipe = $('#field_target').val();
        const wrapper = $('#wrapper_target_id');
        const selectId = $('#field_target_id');
        const label = $('#label_target_id');

        // Reset state awal
        selectId.empty();
        wrapper.hide();
        selectId.prop('required', false);

        // Logic pengisian dropdown berdasarkan tipe
        let currentData = [];
        let labelText = '';

        if (tipe === 'kelas') {
            currentData = dataKelas;
            labelText = 'Pilih Kelas Madrasah';
        } else if (tipe === 'kamar') {
            currentData = dataKamar;
            labelText = 'Pilih Kamar/Gedung';
        } else if (tipe === 'komplek') { // Tambahkan logic Komplek
            currentData = dataKomplek;
            labelText = 'Pilih Komplek';
        }

        // Jika ada data (bukan global atau plotting)
        if (currentData.length > 0 || ['kelas', 'kamar', 'komplek'].includes(tipe)) {
            label.text(labelText);
            selectId.append('<option value="">-- Pilih --</option>');
            
            currentData.forEach(item => {
                selectId.append(`<option value="${item.id}">${item.nama}</option>`);
            });

            wrapper.show();
            selectId.prop('required', true);
        }

        // Set value jika sedang mode Edit
        if (selectedId) {
            selectId.val(selectedId);
        }
    }

    function tambahSesi() {
        $('#modalTitle').text('Tambah');
        $('#formSesi')[0].reset();
        
        // Pastikan action form kembali ke STORE dan hapus spoofing PUT
        $('#formSesi').attr('action', "{{ route('tenant.absensi-sesi.store') }}");
        $('input[name="_method"]').remove(); 
        
        $('#field_id').val('');
        handleTargetChange();
        $('#modalSesi').modal('show');
    }

    function editSesi(data) {
        $('#modalTitle').text('Edit');
        
        // Update URL action ke UPDATE
        let url = "{{ route('tenant.absensi-sesi.update', ':id') }}";
        url = url.replace(':id', data.id);
        $('#formSesi').attr('action', url);

        // Tambahkan spoofing method PUT untuk Laravel
        if ($('input[name="_method"]').length === 0) {
            $('#formSesi').append('<input type="hidden" name="_method" value="PUT">');
        }

        $('#field_id').val(data.id);
        $('#field_nama').val(data.nama_sesi);
        $('#field_target').val(data.target_tipe);
        
        // Format jam (substring diambil untuk jaga-jaga kalau format dari DB itu HH:mm:ss)
        if(data.jam_mulai) $('#field_mulai').val(data.jam_mulai.substring(0, 5));
        if(data.jam_selesai) $('#field_selesai').val(data.jam_selesai.substring(0, 5));
        
        handleTargetChange(data.target_id);
        $('#modalSesi').modal('show');
    }

    function confirmDelete(id) {
        swal({
            title: 'Hapus Sesi?',
            text: "Data absensi yang terkait sesi ini akan hilang secara permanen!",
            icon: 'warning',
            buttons: {
                cancel: { visible: true, text: 'Batal', className: 'btn btn-danger' },
                confirm: { text: 'Ya, Hapus!', className: 'btn btn-success' }
            }
        }).then((willDelete) => {
            if (willDelete) {
                // Buat form delete secara dinamis agar lebih aman
                let url = "{{ route('tenant.absensi-sesi.destroy', ':id') }}";
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