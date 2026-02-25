@extends('layouts.tenant')

@section('content')
<div class="page-inner" style="padding-top: 15px !important;">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Registrasi Santri Baru</h3>
            <p class="text-muted small mb-0">Input data personal, kontak, dan akademik santri secara lengkap.</p>
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

    <form method="POST" action="{{ route('tenant.santri.store') }}" id="mainForm">
        @csrf
        <div class="row">
            {{-- Kolom Kiri: Identitas & Kontak --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold text-primary">
                            <i class="fas fa-user-plus me-2"></i>Informasi Pribadi & Kontak
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">NIS <span class="text-danger">*</span></label>
                                <input type="text" name="nis" value="{{ old('nis') }}" 
                                       class="form-control @error('nis') is-invalid @enderror" placeholder="Nomor Induk Santri">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror">
                                    <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" 
                                   class="form-control @error('nama_lengkap') is-invalid @enderror" placeholder="Nama sesuai ijazah/KK">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tempat Lahir</label>
                                <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" 
                                       class="form-control" placeholder="Kota Kelahiran">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" 
                                       class="form-control">
                            </div>
                        </div>

                        <hr class="my-4 opacity-25">

                        <div class="mb-3">
                            <label class="form-label fw-bold">No. HP Santri (Opsional)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-phone"></i></span>
                                <input type="text" name="no_hp" value="{{ old('no_hp') }}" 
                                       class="form-control" placeholder="628xxx">
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold">Alamat Domisili</label>
                            <textarea name="alamat" class="form-control" rows="3" placeholder="Alamat lengkap tempat tinggal">{{ old('alamat') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Status & Wali --}}
            <div class="col-lg-5">
                {{-- Card Status Akademik --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold text-primary">
                            <i class="fas fa-graduation-cap me-2"></i>Status Akademik
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status Keaktifan <span class="text-danger">*</span></label>
                            <select name="status" class="form-select fw-bold @error('status') is-invalid @enderror">
                                @foreach(['active','nonaktif','lulus','keluar'] as $status)
                                    <option value="{{ $status }}" {{ old('status', 'active') == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold">Tanggal Masuk <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk', date('Y-m-d')) }}" 
                                   class="form-control">
                            <small class="text-muted">Default diatur ke tanggal hari ini.</small>
                        </div>
                    </div>
                </div>

                {{-- Card Wali Murid --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold text-primary">
                            <i class="fas fa-users me-2"></i>Wali Murid
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Wali Santri</label>
                            <select name="wali_id" id="waliSelect" class="form-control">
                                <option value="">-- Cari Nama/No.HP Wali --</option>
                                @foreach($walis as $wali)
                                    <option value="{{ $wali->id }}" {{ old('wali_id') == $wali->id ? 'selected' : '' }}>
                                        {{ $wali->nama }} ({{ $wali->no_hp }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-round w-100" data-bs-toggle="modal" data-bs-target="#modalTambahWali">
                            <i class="fas fa-plus me-1"></i> Wali Belum Terdaftar?
                        </button>
                    </div>
                </div>

                {{-- Card Simpan --}}
                <div class="card border-0 shadow-sm bg-primary text-white">
                    <div class="card-body p-4 text-center">
                        <h6 class="mb-3 opacity-75">Pastikan semua data bertanda (*) telah diisi.</h6>
                        <button type="submit" class="btn btn-white btn-round fw-bold w-100 shadow-sm mb-2 text-primary">
                            <i class="fas fa-save me-2"></i> Daftarkan Santri
                        </button>
                        <a href="{{ route('tenant.santri.index') }}" class="btn btn-link text-white btn-sm opacity-75">Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Modal Tambah Wali Cepat --}}
<div class="modal fade" id="modalTambahWali" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-shield me-2"></i>Registrasi Wali Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formWaliCepat">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="fw-bold">Nama Wali <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" placeholder="Nama lengkap wali" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">No. HP (WhatsApp) <span class="text-danger">*</span></label>
                        <input type="text" name="no_hp" class="form-control" placeholder="08xxxx" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Pekerjaan</label>
                        <input type="text" name="pekerjaan" class="form-control" placeholder="Contoh: Petani, PNS, dll">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-round btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSimpanWali" class="btn btn-primary btn-round btn-sm px-4">Simpan Wali</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .card { border-radius: 12px; }
    .btn-round { border-radius: 50px; }
    .form-control, .form-select { 
        border-radius: 8px; 
        padding: 0.6rem 1rem; 
        border: 1px solid #e0e0e0; 
    }
    .form-control:focus, .form-select:focus { 
        box-shadow: 0 0 0 4px rgba(26, 108, 241, 0.1); 
        border-color: #1a6cf1; 
    }
    .input-group-text { border-radius: 8px 0 0 8px; border: 1px solid #e0e0e0; border-right: none; }
    .input-group .form-control { border-radius: 0 8px 8px 0; }
    .btn-white { background: white; color: #1a6cf1; border: none; }
    .btn-white:hover { background: #f8f9fa; color: #1557be; }
    
    .select2-container--default .select2-selection--single {
        height: 42px !important;
        border: 1px solid #e0e0e0 !important;
        border-radius: 8px !important;
        display: flex;
        align-items: center;
    }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#waliSelect').select2({
            placeholder: "Ketik nama wali...",
            allowClear: true,
            width: '100%'
        });

        $('#formWaliCepat').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#btnSimpanWali');
            btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Memproses').attr('disabled', true);

            $.ajax({
                url: "{{ route('tenant.wali.ajax.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    if(response.success) {
                        // 1. Buat object option baru
                        // Format: new Option(text, id, defaultSelected, selected)
                        let newOption = new Option(
                            response.data.nama + ' (' + response.data.no_hp + ')', 
                            response.data.id, 
                            true, 
                            true
                        );

                        // 2. Masukkan ke Select2 dan paksa trigger 'change'
                        $('#waliSelect').append(newOption).trigger('change');

                        // 3. Tutup modal secara otomatis
                        $('#modalTambahWali').modal('hide');

                        // 4. Reset isi form modal agar bersih saat dibuka lagi
                        $('#formWaliCepat')[0].reset();
                        
                        // 5. Notifikasi (Opsional)
                        if ($.notify) {
                            $.notify({
                                icon: 'fas fa-check',
                                title: 'Berhasil!',
                                message: 'Wali baru berhasil dibuat dan terpilih otomatis.',
                            },{
                                type: 'success',
                                placement: { from: "top", align: "right" },
                                time: 1000,
                            });
                        }
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Gagal menyimpan data wali.';
                    if(xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    }
                    alert(errorMsg);
                },
                complete: function() {
                    btn.html('Simpan Wali').attr('disabled', false);
                }
            });
        });

        $('#mainForm').on('submit', function() {
            $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin me-2"></i> Mendaftarkan...').attr('disabled', true);
        });
    });
</script>
@endpush