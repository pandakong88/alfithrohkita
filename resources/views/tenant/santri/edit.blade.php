@extends('layouts.tenant')

@section('title', 'Edit Profil Santri')

@section('content')
        {{-- BREADCRUMB --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style-1 mb-0" style="background: transparent; padding: 0;">
                <li class="breadcrumb-item"><a href="{{ route('tenant.santri.index') }}">Database Santri</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Profil</li>
            </ol>
        </nav>

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div class="d-flex align-items-center">
                <div class="icon-avatar bg-primary-gradient text-white me-3 shadow-sm">
                    <i class="fas fa-user-edit fa-lg"></i>
                </div>
                <div>
                    <h3 class="text-dark fw-bold mb-0" style="font-size: 1.6rem;">Edit Profil Santri</h3>
                    <p class="text-muted mb-0 small">Memperbarui data personal, status keaktifan akademik, dan wali murid terpilih.</p>
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

        <form method="POST" action="{{ route('tenant.santri.update', $santri) }}" id="formEditSantri">
            @csrf
            @method('PUT')

            <div class="row g-4">
                {{-- Kolom Kiri: Identitas & Kontak --}}
                <div class="col-lg-7">
                    <div class="card card-custom h-100 mb-0">
                        <div class="card-header bg-white py-3">
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="fas fa-id-card me-2 text-primary"></i>Identitas Diri & Kontak
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-slate small mb-1">Nomor Induk Santri (NIS) <span class="text-danger">*</span></label>
                                    <input type="text" name="nis" value="{{ old('nis', $santri->nis) }}" 
                                           class="form-control @error('nis') is-invalid @enderror" placeholder="Contoh: 20260012" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-slate small mb-1">Jenis Kelamin <span class="text-danger">*</span></label>
                                    <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                                        <option value="L" {{ old('jenis_kelamin', $santri->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki (Putra)</option>
                                        <option value="P" {{ old('jenis_kelamin', $santri->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan (Putri)</option>
                                    </select>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label fw-bold text-slate small mb-1">Nama Lengkap Santri <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $santri->nama_lengkap) }}" 
                                           class="form-control @error('nama_lengkap') is-invalid @enderror" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-slate small mb-1">Tempat Lahir</label>
                                    <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $santri->tempat_lahir) }}" 
                                           class="form-control" placeholder="Contoh: Sleman">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-slate small mb-1">Tanggal Lahir</label>
                                    <input type="date" name="tanggal_lahir" 
                                           value="{{ old('tanggal_lahir', optional($santri->tanggal_lahir)->format('Y-m-d')) }}" 
                                           class="form-control">
                                </div>

                                <div class="col-12">
                                    <hr class="my-2 opacity-25">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold text-slate small mb-1">Nomor HP Santri</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-slate" style="border-right: none;"><i class="fas fa-phone text-muted"></i></span>
                                        <input type="text" name="no_hp" value="{{ old('no_hp', $santri->no_hp) }}" class="form-control" placeholder="628xxxx">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold text-slate small mb-1">Alamat Domisili Lengkap</label>
                                    <textarea name="alamat" class="form-control" rows="3">{{ old('alamat', $santri->alamat) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Akademik & Wali --}}
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
                                            <option value="{{ $status }}" {{ old('status', $santri->status) == $status ? 'selected' : '' }}>
                                                {{ $lbl }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-slate small mb-1">Tanggal Masuk</label>
                                        <input type="date" name="tanggal_masuk" 
                                               value="{{ old('tanggal_masuk', optional($santri->tanggal_masuk)->format('Y-m-d')) }}" 
                                               class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-slate small mb-1 text-muted">Tanggal Keluar</label>
                                        <input type="date" name="tanggal_keluar" 
                                               value="{{ old('tanggal_keluar', optional($santri->tanggal_keluar)->format('Y-m-d')) }}" 
                                               class="form-control">
                                    </div>
                                </div>

                                <div class="row g-3 mt-1">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-slate small mb-1">Pilih Kelas</label>
                                        <select name="kelas_id" id="kelasSelect" class="form-control">
                                            <option value="">-- Tanpa Kelas --</option>
                                            @foreach($kelas as $k)
                                                <option value="{{ $k->id }}" {{ old('kelas_id', $santri->kelas_id) == $k->id ? 'selected' : '' }}>
                                                    {{ $k->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-slate small mb-1">Pilih Kamar & Komplek</label>
                                        <select name="kamar_id" id="kamarSelect" class="form-control">
                                            <option value="">-- Tanpa Kamar --</option>
                                            @foreach($kamars as $kmr)
                                                <option value="{{ $kmr->id }}" {{ old('kamar_id', $santri->kamar_id) == $kmr->id ? 'selected' : '' }}>
                                                    {{ $kmr->nama }} @if($kmr->kompleks) ({{ $kmr->kompleks->nama }}) @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
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
                                <div class="mb-2">
                                    <label class="form-label fw-bold text-slate small mb-1">Pilih Wali</label>
                                    <select name="wali_id" id="waliSelect" class="form-control">
                                        <option value="">-- Cari Wali --</option>
                                        @foreach($walis as $wali)
                                            <option value="{{ $wali->id }}"
                                                {{ old('wali_id', $santri->wali_id ?? '') == $wali->id ? 'selected' : '' }}>
                                                {{ $wali->nama }} ({{ $wali->no_hp }})
                                            </option>
                                        @endforeach
                                    </select>
                                    
                                    <div class="mt-2.5 text-end">
                                        <button type="button" class="btn btn-sm btn-link text-primary p-0 fw-bold decoration-none" onclick="openWaliModal()" style="font-size: 12.5px;">
                                            <i class="fas fa-plus-circle me-1"></i> Wali Belum Terdaftar?
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card Action --}}
                        <div class="card card-custom bg-primary text-white mb-0 mt-auto">
                            <div class="card-body p-4 text-center">
                                <h6 class="mb-3.5 opacity-90 text-sm">Simpan seluruh perubahan profil data santri ini?</h6>
                                <button type="submit" class="btn btn-white btn-round fw-bold w-100 shadow-sm mb-2 text-primary py-2.5 btn-update-santri">
                                    <i class="fas fa-save me-2"></i> Update Data Santri
                                </button>
                                <a href="{{ route('tenant.santri.index') }}" class="btn btn-link text-white btn-sm opacity-75 mt-1 decoration-none fw-semibold">Batalkan</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

{{-- Modal Tambah Wali Cepat --}}
<div class="modal fade" id="waliModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-primary text-white border-bottom-0 px-4 pt-4">
                <h5 class="modal-title fw-bold">Tambah Wali Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <div class="mb-3">
                    <label class="form-label fw-bold text-slate small mb-1">Nama Wali <span class="text-danger">*</span></label>
                    <input type="text" id="modal_wali_nama" class="form-control" placeholder="Masukkan nama lengkap">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold text-slate small mb-1">No HP / WhatsApp <span class="text-danger">*</span></label>
                    <input type="text" id="modal_wali_no_hp" class="form-control" placeholder="Contoh: 081234567xxx">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold text-slate small mb-1">Pekerjaan</label>
                    <input type="text" id="modal_wali_pekerjaan" class="form-control" placeholder="Contoh: Wiraswasta, Guru, dll">
                </div>
                <div id="modal_wali_error" class="text-danger small mt-2 fw-semibold"></div>
            </div>
            <div class="modal-footer bg-light border-top-0 px-4 pb-4">
                <button type="button" class="btn btn-light btn-round border btn-sm px-4 fw-semibold" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-round btn-sm px-4 fw-bold btn-save-wali" onclick="saveWali()">Simpan Wali</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* CSS Utility Variables */
    .page-inner {
        padding-top: 15px !important;
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
    .mt-2.5 { margin-top: 0.625rem; }
</style>
@endsection

@push('scripts')
<script>
    function openWaliModal() {
        $('#modal_wali_nama').val('');
        $('#modal_wali_no_hp').val('');
        $('#modal_wali_pekerjaan').val('');
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

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1.5"></i> Menyimpan...');

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
                if (response.success) {
                    // Prepend and select item
                    let newOption = new Option(response.data.text, response.data.id, true, true);
                    $('#waliSelect').append(newOption).trigger('change');
                    $('#waliSelect').val(response.data.id).trigger('change');

                    // Close modal and reset
                    $('#waliModal').modal('hide');
                    $('#modal_wali_nama').val('');
                    $('#modal_wali_no_hp').val('');
                    $('#modal_wali_pekerjaan').val('');

                    // Notification
                    if ($.notify) {
                        $.notify({
                            icon: 'fas fa-check-circle',
                            title: 'Berhasil!',
                            message: 'Wali baru berhasil dibuat dan dipilih otomatis.'
                        }, { type: 'info', placement: { from: "top", align: "right" } });
                    }
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
        // Initialize Select2 search dropdown
        $('#waliSelect').select2({
            placeholder: "Cari nama atau No. HP wali...",
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

        // Submit form spinner
        $('#formEditSantri').on('submit', function() {
            $('.btn-update-santri').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Memproses...');
        });
    });
</script>
@endpush