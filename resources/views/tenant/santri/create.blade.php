@extends('layouts.tenant')

@section('title', 'Pendaftaran Santri Baru')

@section('content')
        {{-- BREADCRUMB --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style-1 mb-0" style="background: transparent; padding: 0;">
                <li class="breadcrumb-item"><a href="{{ route('tenant.santri.index') }}">Database Santri</a></li>
                <li class="breadcrumb-item active" aria-current="page">Registrasi Baru</li>
            </ol>
        </nav>

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div class="d-flex align-items-center">
                <div class="icon-avatar bg-primary-gradient text-white me-3 shadow-sm">
                    <i class="fas fa-user-plus fa-lg"></i>
                </div>
                <div>
                    <h3 class="text-dark fw-bold mb-0" style="font-size: 1.6rem;">Registrasi Santri Baru</h3>
                    <p class="text-muted mb-0 small">Daftarkan data personal, kontak, akademik, dan hubungkan dengan wali santri.</p>
                </div>
            </div>
            <a href="{{ route('tenant.santri.index') }}" class="btn btn-light btn-round border shadow-sm btn-sm">
                <i class="fas fa-arrow-left me-1.5"></i> Kembali
            </a>
        </div>

        {{-- Alert Error --}}
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm p-4 mb-4 rounded-4" style="background-color: #fde8e8; color: #9b1c1c;">
                <div class="d-flex">
                    <i class="fas fa-exclamation-circle me-3 fa-lg mt-1"></i>
                    <div>
                        <strong class="d-block mb-1.5" style="font-size: 14px;">Terjadi Kesalahan Pengisian:</strong>
                        <ul class="mb-0 ps-3 text-xs">
                            @foreach ($errors->all() as $error)
                                <li class="mb-1"><small class="fw-bold">{{ $error }}</small></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('tenant.santri.store') }}" id="mainForm">
            @csrf
            <div class="row g-4">
                {{-- Kolom Kiri: Identitas & Kontak --}}
                <div class="col-lg-7">
                    <div class="d-flex flex-column gap-4">
                        <div class="card card-custom mb-0">
                            <div class="card-header bg-white py-3">
                                <h6 class="fw-bold mb-0 text-dark">
                                    <i class="fas fa-user me-2 text-primary"></i>Informasi Pribadi & Kontak
                                </h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        @if(auth()->user()->pondok->nis_auto_generate)
                                            <label class="form-label fw-bold text-slate small mb-1">Nomor Induk Santri (NIS) <small class="text-muted">(Opsional)</small></label>
                                            <input type="text" name="nis" value="{{ old('nis') }}" 
                                                   class="form-control @error('nis') is-invalid @enderror" placeholder="Kosongkan untuk otomatis">
                                        @else
                                            <label class="form-label fw-bold text-slate small mb-1">Nomor Induk Santri (NIS) <span class="text-danger">*</span></label>
                                            <input type="text" name="nis" value="{{ old('nis') }}" 
                                                   class="form-control @error('nis') is-invalid @enderror" placeholder="Contoh: 20260012" required>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-slate small mb-1">Jenis Kelamin <span class="text-danger">*</span></label>
                                        <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                                            <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki (Putra)</option>
                                            <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan (Putri)</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-slate small mb-1">Nama Lengkap Santri <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" 
                                               class="form-control @error('nama_lengkap') is-invalid @enderror" placeholder="Tulis sesuai nama di KK / Akta" required>
                                    </div>
    
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-slate small mb-1">Tempat Lahir</label>
                                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" 
                                               class="form-control" placeholder="Contoh: Sleman">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-slate small mb-1">Tanggal Lahir</label>
                                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" 
                                               class="form-control">
                                    </div>
    
                                    <div class="col-12">
                                        <hr class="my-2 opacity-25">
                                    </div>
    
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-slate small mb-1">Nomor HP Santri (Opsional)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-slate" style="border-right: none;"><i class="fas fa-phone text-muted"></i></span>
                                            <input type="text" name="no_hp" value="{{ old('no_hp') }}" 
                                                   class="form-control" placeholder="Contoh: 081234567xxx">
                                        </div>
                                        <small class="text-muted" style="font-size: 11px;">Gunakan kode area HP indonesia (contoh: 0812xxxx atau 62812xxxx).</small>
                                    </div>
    
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-slate small mb-1">Alamat Asal Lengkap</label>
                                        <textarea name="alamat" class="form-control" rows="3" placeholder="Tulis alamat rumah, RT/RW, Dusun, Desa, Kecamatan, dan Kabupaten asal">{{ old('alamat') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Card Informasi Tambahan --}}
                        <div class="card card-custom mb-0">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0 text-dark">
                                    <i class="fas fa-id-card-alt me-2 text-primary"></i>Informasi Tambahan (Custom Fields)
                                </h6>
                                <button type="button" class="btn btn-xs btn-primary btn-round px-3 shadow-none" id="btn-add-custom-field">
                                    <i class="fas fa-plus me-1"></i> Tambah Field
                                </button>
                            </div>
                            <div class="card-body p-4">
                                <p class="text-muted text-xs mb-3">Tambahkan informasi tambahan kustom sesuai kebutuhan (misal: Golongan Darah, Silsilah/Status Sibling, Alergi, Riwayat Penyakit).</p>
                                <div id="custom-fields-container" class="d-flex flex-column gap-3">
                                    <div class="text-center py-3 text-muted" id="no-custom-fields-text">
                                        <i class="fas fa-info-circle me-1.5"></i>Belum ada informasi tambahan kustom. Klik "Tambah Field" untuk menambahkan.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Status & Wali --}}
                <div class="col-lg-5">
                    <div class="d-flex flex-column gap-4 h-100">
                        {{-- Card Status Akademik --}}
                        <div class="card card-custom mb-0">
                            <div class="card-header bg-white py-3">
                                <h6 class="fw-bold mb-0 text-dark">
                                    <i class="fas fa-graduation-cap me-2 text-primary"></i>Status Akademik
                                </h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-slate small mb-1">Status Keaktifan <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select fw-bold @error('status') is-invalid @enderror" required>
                                        @foreach(['active' => 'Aktif', 'nonaktif' => 'Non-Aktif', 'lulus' => 'Lulus', 'keluar' => 'Keluar'] as $status => $lbl)
                                            <option value="{{ $status }}" {{ old('status', 'active') == $status ? 'selected' : '' }}>
                                                {{ $lbl }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-slate small mb-1">Tanggal Masuk Pondok <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk', date('Y-m-d')) }}" 
                                           class="form-control" required>
                                    <small class="text-muted" style="font-size: 11px;">Pendaftaran pertama masuk pondok pesantren.</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-slate small mb-1">Pilih Kelas</label>
                                    <select name="kelas_id" id="kelasSelect" class="form-control">
                                        <option value="">-- Tanpa Kelas --</option>
                                        @foreach($kelas as $k)
                                            <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                                {{ $k->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label fw-bold text-slate small mb-1">Pilih Kamar & Komplek</label>
                                    <select name="kamar_id" id="kamarSelect" class="form-control">
                                        <option value="">-- Tanpa Kamar --</option>
                                        @foreach($kamars as $kmr)
                                            <option value="{{ $kmr->id }}" {{ old('kamar_id') == $kmr->id ? 'selected' : '' }}>
                                                {{ $kmr->nama }} @if($kmr->kompleks) ({{ $kmr->kompleks->nama }}) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Card Wali Murid --}}
                        <div class="card card-custom mb-0">
                            <div class="card-header bg-white py-3">
                                <h6 class="fw-bold mb-0 text-dark">
                                    <i class="fas fa-user-shield me-2 text-primary"></i>Hubungkan Wali Murid
                                </h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-slate small mb-1">Pilih Wali Santri</label>
                                    <select name="wali_id" id="waliSelect" class="form-control">
                                        <option value="">-- Cari Nama/No.HP Wali --</option>
                                        @foreach($walis as $wali)
                                            <option value="{{ $wali->id }}" {{ old('wali_id') == $wali->id ? 'selected' : '' }}>
                                                {{ $wali->nama }} ({{ $wali->no_hp }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary btn-round w-100" data-bs-toggle="modal" data-bs-target="#modalTambahWali" style="font-size: 12px; font-weight: 600;">
                                    <i class="fas fa-plus-circle me-1.5"></i> Wali Belum Terdaftar di Sistem?
                                </button>
                            </div>
                        </div>

                        {{-- Card Simpan --}}
                        <div class="card card-custom bg-primary text-white mb-0 mt-auto">
                            <div class="card-body p-4 text-center">
                                <h6 class="mb-3.5 opacity-90 text-sm">Pastikan semua data bertanda merah (*) telah diisi dengan benar.</h6>
                                <button type="submit" class="btn btn-white btn-round fw-bold w-100 shadow-sm mb-2 text-primary py-2.5">
                                    <i class="fas fa-save me-2"></i> Daftarkan Santri Baru
                                </button>
                                <a href="{{ route('tenant.santri.index') }}" class="btn btn-link text-white btn-sm opacity-75 mt-1 decoration-none fw-semibold">Batalkan</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

{{-- Modal Tambah Wali Cepat --}}
<div class="modal fade" id="modalTambahWali" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-primary text-white border-bottom-0 px-4 pt-4">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-shield me-2"></i>Registrasi Wali Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formWaliCepat">
                @csrf
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-slate small mb-1">Nama Wali <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" placeholder="Tulis nama lengkap wali" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-slate small mb-1">No. HP / WhatsApp <span class="text-danger">*</span></label>
                        <input type="text" name="no_hp" class="form-control" placeholder="Contoh: 081234567xxx" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold text-slate small mb-1">Pekerjaan</label>
                        <input type="text" name="pekerjaan" class="form-control" placeholder="Contoh: Wiraswasta, Guru, dll">
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 px-4 pb-4">
                    <button type="button" class="btn btn-light btn-round border btn-sm px-4 fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSimpanWali" class="btn btn-primary btn-round btn-sm px-4 fw-bold shadow-xs">Simpan Wali</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* CSS Utility Variables */
    .page-inner {
        padding-top: 15px !important;
    }
    
    .max-w-5xl {
        max-width: 1024px;
        margin: 0 auto;
    }

    /* Icon Avatar Styles */
    .icon-avatar {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .bg-primary-gradient {
        background: linear-gradient(135deg, #1572e8 0%, #064095 100%) !important;
    }
    
    /* Card design */
    .card-custom {
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03), 0 1px 3px rgba(0, 0, 0, 0.02) !important;
        background: #ffffff;
        overflow: hidden;
    }
    
    .card-custom .card-header {
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        padding: 20px 24px;
    }

    .form-control, .form-select { 
        border-radius: 10px; 
        padding: 0.65rem 1rem; 
        border: 1px solid #cbd5e1; 
        font-size: 14px;
    }

    .form-control:focus, .form-select:focus { 
        box-shadow: 0 0 0 4px rgba(21, 114, 232, 0.1); 
        border-color: #1572e8; 
    }

    .input-group-text { 
        border-radius: 10px 0 0 10px; 
        border: 1px solid #cbd5e1; 
        border-right: none; 
    }

    .input-group .form-control { 
        border-radius: 0 10px 10px 0; 
    }

    .border-slate {
        border-color: #cbd5e1 !important;
    }

    .text-slate {
        color: #475569 !important;
    }

    .btn-round { border-radius: 50px; }
    
    .btn-white { 
        background: #ffffff; 
        color: #1572e8 !important; 
        border: none; 
    }

    .btn-white:hover { 
        background: #f8fafc; 
        color: #0d5cb3 !important; 
    }
    
    /* Select2 customizations to fit design */
    .select2-container--default .select2-selection--single {
        height: 42px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 10px !important;
        display: flex;
        align-items: center;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
    }

    .mb-3.5 { margin-bottom: 0.875rem; }
    .text-xs { font-size: 0.75rem; }
    .text-sm { font-size: 0.875rem; }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Handle dynamic custom fields
        $('#btn-add-custom-field').on('click', function() {
            $('#no-custom-fields-text').hide();
            
            let rowHtml = `
                <div class="row g-2 align-items-center custom-field-row mb-2">
                    <div class="col-5">
                        <input type="text" name="custom_keys[]" class="form-control form-control-sm" placeholder="Nama Field (misal: Golongan Darah)" required style="padding: 0.45rem 0.8rem; font-size: 13px;">
                    </div>
                    <div class="col-6">
                        <input type="text" name="custom_values[]" class="form-control form-control-sm" placeholder="Nilai" required style="padding: 0.45rem 0.8rem; font-size: 13px;">
                    </div>
                    <div class="col-1 text-end">
                        <button type="button" class="btn btn-link btn-xs text-danger btn-remove-custom-field p-0 border-0 bg-transparent shadow-none">
                            <i class="fas fa-trash-alt fa-lg"></i>
                        </button>
                    </div>
                </div>
            `;
            
            $('#custom-fields-container').append(rowHtml);
        });
        
        $(document).on('click', '.btn-remove-custom-field', function() {
            $(this).closest('.custom-field-row').remove();
            if ($('.custom-field-row').length === 0) {
                $('#no-custom-fields-text').show();
            }
        });

        // Initialize Select2 search
        $('#waliSelect').select2({
            placeholder: "Ketik nama/nomor HP wali...",
            allowClear: true,
            width: '100%'
        });

        $('#kelasSelect').select2({
            placeholder: "Pilih kelas...",
            allowClear: true,
            width: '100%'
        });

        $('#kamarSelect').select2({
            placeholder: "Pilih kamar...",
            allowClear: true,
            width: '100%'
        });

        // AJAX Quick Wali Store form handler
        $('#formWaliCepat').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#btnSimpanWali');
            btn.html('<i class="fas fa-spinner fa-spin me-1.5"></i> Memproses...').attr('disabled', true);

            $.ajax({
                url: "{{ route('tenant.wali.ajax.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    if(response.success) {
                        // Create option item and force Select2 update
                        let newOption = new Option(
                            response.data.nama + ' (' + response.data.no_hp + ')', 
                            response.data.id, 
                            true, 
                            true
                        );
                        $('#waliSelect').append(newOption).trigger('change');

                        // Hide modal and reset quick form
                        $('#modalTambahWali').modal('hide');
                        $('#formWaliCepat')[0].reset();
                        
                        // Notify user success
                        if ($.notify) {
                            $.notify({
                                icon: 'fas fa-check-circle',
                                title: 'Berhasil!',
                                message: 'Wali baru berhasil disimpan dan terpilih.',
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

        // Submit form spinner
        $('#mainForm').on('submit', function() {
            $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin me-2"></i> Mendaftarkan...').attr('disabled', true);
        });
    });
</script>
@endpush