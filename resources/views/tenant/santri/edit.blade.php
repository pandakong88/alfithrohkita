@extends('layouts.tenant')

@section('content')
<div class="page-inner" style="padding-top: 15px !important;">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Edit Profil Santri</h3>
            <p class="text-muted small mb-0">Memperbarui data akademik dan personal santri.</p>
        </div>
        <a href="{{ route('tenant.santri.index') }}" class="btn btn-outline-info btn-round btn-sm">
            <i class="fas fa-arrow-left me-2"></i> Kembali
        </a>
    </div>

    {{-- Alert Error --}}
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li><small class="fw-bold">{{ $error }}</small></li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('tenant.santri.update', $santri) }}" id="formEditSantri">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- Kolom Kiri: Identitas --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold text-info">
                            <i class="fas fa-id-card me-2"></i>Identitas Diri
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">NIS <span class="text-danger">*</span></label>
                                <input type="text" name="nis" value="{{ old('nis', $santri->nis) }}" 
                                       class="form-control @error('nis') is-invalid @enderror" placeholder="Nomor Induk Santri">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror">
                                    <option value="L" {{ old('jenis_kelamin', $santri->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('jenis_kelamin', $santri->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $santri->nama_lengkap) }}" 
                                   class="form-control @error('nama_lengkap') is-invalid @enderror">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tempat Lahir</label>
                                <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $santri->tempat_lahir) }}" 
                                       class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" 
                                       value="{{ old('tanggal_lahir', optional($santri->tanggal_lahir)->format('Y-m-d')) }}" 
                                       class="form-control">
                            </div>
                        </div>

                        <hr class="my-4 opacity-25">

                        <div class="mb-3">
                            <label class="form-label fw-bold">No. HP Santri</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-phone"></i></span>
                                <input type="text" name="no_hp" value="{{ old('no_hp', $santri->no_hp) }}" class="form-control">
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold">Alamat Domisili</label>
                            <textarea name="alamat" class="form-control" rows="3">{{ old('alamat', $santri->alamat) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Akademik & Wali --}}
            <div class="col-lg-5">
                {{-- Card Status --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold text-info">
                            <i class="fas fa-graduation-cap me-2"></i>Status Akademik
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status Keaktifan <span class="text-danger">*</span></label>
                            <select name="status" class="form-select fw-bold @error('status') is-invalid @enderror">
                                @foreach(['active','nonaktif','lulus','keluar'] as $status)
                                    <option value="{{ $status }}" {{ old('status', $santri->status) == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Tanggal Masuk</label>
                                <input type="date" name="tanggal_masuk" 
                                       value="{{ old('tanggal_masuk', optional($santri->tanggal_masuk)->format('Y-m-d')) }}" 
                                       class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">Tanggal Keluar</label>
                                <input type="date" name="tanggal_keluar" 
                                       value="{{ old('tanggal_keluar', optional($santri->tanggal_keluar)->format('Y-m-d')) }}" 
                                       class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card Wali Murid --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold text-info">
                            <i class="fas fa-users me-2"></i>Wali Murid
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="form-label fw-bold">Pilih Wali</label>
                            <select name="wali_id" id="waliSelect" class="form-control">
                                <option value="">-- Cari Wali --</option>
                                @foreach($walis as $wali)
                                    <option value="{{ $wali->id }}"
                                        {{ old('wali_id', $santri->wali_id ?? '') == $wali->id ? 'selected' : '' }}>
                                        {{ $wali->nama }} ({{ $wali->no_hp }})
                                    </option>
                                @endforeach
                            </select>
                            
                            <div class="mt-2 text-end">
                                <button type="button" class="btn btn-sm btn-link text-info p-0 fw-bold" onclick="openWaliModal()">
                                    <i class="fas fa-plus-circle me-1"></i> Wali Belum Terdaftar?
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card Action --}}
                <div class="card border-0 shadow-sm bg-info text-white">
                    <div class="card-body p-4 text-center">
                        <h6 class="mb-3 opacity-75">Simpan perubahan data santri?</h6>
                        <button type="submit" class="btn btn-white btn-round fw-bold w-100 shadow-sm mb-2 text-info btn-update-santri">
                            <i class="fas fa-save me-2"></i> Update Data Santri
                        </button>
                        <a href="{{ route('tenant.santri.index') }}" class="btn btn-link text-white btn-sm opacity-75">Batalkan & Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="modal fade" id="waliModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold">Tambah Wali Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Wali <span class="text-danger">*</span></label>
                    <input type="text" id="modal_wali_nama" class="form-control" placeholder="Masukkan nama lengkap">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">No HP <span class="text-danger">*</span></label>
                    <input type="text" id="modal_wali_no_hp" class="form-control" placeholder="Contoh: 081234567xxx">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Pekerjaan</label>
                    <input type="text" id="modal_wali_pekerjaan" class="form-control" placeholder="Contoh: Wiraswasta, Guru, dll">
                </div>
                <div id="modal_wali_error" class="text-danger small mt-2"></div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm btn-round px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-info btn-sm btn-round px-4 btn-save-wali" onclick="saveWali()">Simpan Wali</button>
            </div>
        </div>
    </div>
</div>

<style>
    .card { border-radius: 12px; }
    .btn-round { border-radius: 50px; }
    .form-control, .form-select { border-radius: 8px; padding: 0.6rem 1rem; border: 1px solid #e0e0e0; }
    .select2-container--default .select2-selection--single { height: 42px !important; border: 1px solid #e0e0e0 !important; border-radius: 8px !important; }
    .btn-white { background: white; color: #48abf7; border: none; }
</style>
@endsection

@push('scripts')
<script>
    function openWaliModal() {
        $('#modal_wali_nama').val('');
        $('#modal_wali_no_hp').val('');
        $('#modal_wali_error').html('');
        $('#waliModal').modal('show');
    }

    function saveWali() 
    {
        let nama = $('#modal_wali_nama').val();
        let no_hp = $('#modal_wali_no_hp').val();
        let pekerjaan = $('#modal_wali_pekerjaan').val();
        let btn = $('.btn-save-wali');

        if (!nama || !no_hp) {
            $('#modal_wali_error').html('Nama dan No HP wajib diisi.');
            return;
        }

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: "{{ route('tenant.wali.ajax.store') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                nama: nama,
                no_hp: no_hp,
                pekerjaan: pekerjaan,
            },
            success: function(response) {
                // CEK DISINI: Karena controller kirim 'success' => true
                if (response.success) {
                    // Ambil data dari dalam object 'data' sesuai controller
                    let newOption = new Option(response.data.text, response.data.id, true, true);
                    
                    // Masukkan ke Select2
                    $('#waliSelect').append(newOption).trigger('change');
                    
                    // Pilih secara manual untuk memastikan (double check)
                    $('#waliSelect').val(response.data.id).trigger('change');

                    // Tutup modal
                    $('#waliModal').modal('hide');
                    
                    // Bersihkan form modal
                    $('#modal_wali_nama').val('');
                    $('#modal_wali_no_hp').val('');
                    $('#modal_wali_pekerjaan').val('');

                    // Notifikasi
                    $.notify({
                        icon: 'fas fa-check',
                        title: 'Berhasil',
                        message: 'Wali berhasil ditambahkan dan dipilih.'
                    }, { type: 'info', placement: { from: "top", align: "right" } });
                }
                btn.prop('disabled', false).text('Simpan Wali');
            },
            error: function(xhr) {
                btn.prop('disabled', false).text('Simpan Wali');
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    $('#modal_wali_error').html(errors);
                } else {
                    $('#modal_wali_error').html('Terjadi kesalahan server.');
                }
            }
        });
    }

    $(document).ready(function() {
        $('#waliSelect').select2({
            placeholder: "Cari nama atau No. HP wali...",
            allowClear: true,
            width: '100%'
        });

        $('#formEditSantri').on('submit', function() {
            $('.btn-update-santri').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Memproses...');
        });
    });
</script>
@endpush