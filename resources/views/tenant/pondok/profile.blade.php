@extends('layouts.tenant')

@section('title', 'Profil & Pengaturan Pondok')

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb breadcrumb-style-1 mb-0" style="background: transparent; padding: 0;">
            <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Profil Pondok</li>
        </ol>
    </nav>

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center">
            <div class="icon-avatar bg-primary-gradient text-white me-3 shadow-sm">
                <i class="fas fa-mosque fa-lg"></i>
            </div>
            <div>
                <h3 class="text-dark fw-bold mb-0" style="font-size: 1.6rem;">Profil & Pengaturan Pondok</h3>
                <p class="text-muted mb-0 small">Kelola identitas utama pondok pesantren Anda serta aturan penomoran otomatis NIS.</p>
            </div>
        </div>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm p-3 mb-4 rounded-4 d-flex align-items-center" style="background-color: #def7ec; color: #03543f;">
            <i class="fas fa-check-circle me-2.5 fa-lg"></i>
            <div>
                <small class="fw-bold">{{ session('success') }}</small>
            </div>
        </div>
    @endif

    {{-- Error Alert --}}
    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm p-3 mb-4 rounded-4" style="background-color: #fde8e8; color: #9b1c1c;">
            <div class="d-flex">
                <i class="fas fa-exclamation-circle me-2.5 fa-lg mt-0.5"></i>
                <div>
                    <strong class="d-block mb-1 text-sm">Terjadi Kesalahan:</strong>
                    <ul class="mb-0 ps-3 text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('tenant.pondok.profile.update') }}" enctype="multipart/form-data" id="profileForm">
        @csrf
        @method('PUT')
        
        <div class="row g-4">
            {{-- Kolom Kiri: Identitas Pondok --}}
            <div class="col-lg-7">
                <div class="card card-custom h-100 mb-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-id-card me-2 text-primary"></i>Identitas Utama Pondok
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12 text-center mb-3">
                                <div class="position-relative d-inline-block">
                                    <div class="avatar-preview shadow-sm" style="width: 120px; height: 120px; border-radius: 20px; overflow: hidden; border: 3px solid #f1f5f9; background: #f8fafc; margin: 0 auto; display: flex; align-items: center; justify-content: center;">
                                        @if($pondok->logo)
                                            <img src="{{ asset('storage/' . $pondok->logo) }}" alt="Logo Pondok" id="logoPreviewImg" style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            <i class="fas fa-mosque text-muted fa-3x" id="logoFallbackIcon"></i>
                                        @endif
                                    </div>
                                    <label for="logoInput" class="btn btn-primary btn-round position-absolute shadow-sm" style="bottom: -10px; right: -10px; padding: 4px 10px; font-size: 11px; cursor: pointer;">
                                        <i class="fas fa-camera"></i> Ubah Logo
                                    </label>
                                    <input type="file" name="logo" id="logoInput" class="d-none" accept="image/*">
                                </div>
                                <div class="mt-2.5">
                                    <small class="text-muted text-xs d-block">Rekomendasi format: PNG, JPG maksimal 2MB (Rasio 1:1)</small>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold text-slate small mb-1">Nama Pondok Pesantren <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $pondok->name) }}" 
                                       class="form-control" placeholder="Tulis nama lengkap pondok" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold text-slate small mb-1">Nomor Telepon / HP Pondok</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-slate"><i class="fas fa-phone text-muted"></i></span>
                                    <input type="text" name="phone" value="{{ old('phone', $pondok->phone) }}" 
                                           class="form-control" placeholder="Contoh: 0274-123xxx atau 0812xxxx">
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold text-slate small mb-1">Alamat Lengkap Pondok</label>
                                <textarea name="address" class="form-control" rows="4" placeholder="Tulis alamat fisik pondok secara lengkap">{{ old('address', $pondok->address) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Pengaturan Pola NIS --}}
            <div class="col-lg-5">
                <div class="d-flex flex-column gap-4 h-100">
                    <div class="card card-custom mb-0">
                        <div class="card-header bg-white py-3">
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="fas fa-cog me-2 text-primary"></i>Pengaturan Format NIS/NIM
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            {{-- Checkbox Auto Generate --}}
                            <div class="form-check form-switch mb-3.5 p-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <label class="form-check-label fw-bold text-slate small mb-0" for="nisAutoGenerate">Aktifkan Auto-Generate NIS</label>
                                    <small class="text-muted d-block text-xs mt-0.5">Sistem otomatis menghasilkan nomor NIS jika dikosongkan.</small>
                                </div>
                                <input class="form-check-input ms-0" type="checkbox" name="nis_auto_generate" id="nisAutoGenerate" value="1" 
                                       {{ old('nis_auto_generate', $pondok->nis_auto_generate) ? 'checked' : '' }} style="width: 2.8em; height: 1.5em; cursor: pointer;">
                            </div>

                            <hr class="my-3 opacity-25">

                            {{-- Pola NIS Input --}}
                            <div class="mb-4" id="patternFormGroup">
                                <label class="form-label fw-bold text-slate small mb-1">Pola Format NIS <span class="text-danger">*</span></label>
                                <input type="text" name="nis_pattern" id="nisPattern" value="{{ old('nis_pattern', $pondok->nis_pattern ?? '[YEAR][SEQ:4]') }}" 
                                       class="form-control fw-bold text-primary" placeholder="Contoh: [YEAR][SEQ:4]" required>
                                <small class="text-muted d-block text-xs mt-1.5">Pola default: <code>[YEAR][SEQ:4]</code> (e.g. 20260001)</small>
                            </div>

                            {{-- Card Info & Contoh --}}
                            <div class="card border-0 p-3 rounded-4 mb-0" style="background-color: #f8fafc; border: 1px solid #e2e8f0 !important;">
                                <h6 class="fw-bold text-dark text-xs mb-2"><i class="fas fa-info-circle text-info me-1"></i> Aturan & Pola Pengganti:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless mb-0" style="font-size: 11px;">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold py-1" style="width: 70px;"><code>[YEAR]</code></td>
                                                <td class="text-muted py-1">Tahun masuk 4 digit (contoh: <b>2026</b>)</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold py-1"><code>[YEAR2]</code></td>
                                                <td class="text-muted py-1">Tahun masuk 2 digit (contoh: <b>26</b>)</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold py-1"><code>[SEQ:N]</code></td>
                                                <td class="text-muted py-1">Urutan otomatis N digit (contoh: <code>[SEQ:4]</code> untuk <b>0001</b>)</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <hr class="my-2.5 opacity-25">
                                <h6 class="fw-bold text-dark text-xs mb-1.5"><i class="fas fa-lightbulb text-warning me-1"></i> Contoh Pola:</h6>
                                <ul class="mb-0 ps-3 text-muted" style="font-size: 11px; line-height: 1.5;">
                                    <li><code>[YEAR][SEQ:4]</code> &rarr; <b>20260001</b></li>
                                    <li><code>ALF-[YEAR2]-[SEQ:3]</code> &rarr; <b>ALF-26-001</b></li>
                                    <li><code>99[SEQ:4]</code> &rarr; <b>990001</b></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Simpan --}}
                    <div class="card card-custom bg-primary text-white mb-0 mt-auto">
                        <div class="card-body p-4 text-center">
                            <h6 class="mb-3 opacity-90 text-sm">Pastikan pengaturan format pola NIS telah sesuai dengan standar penomoran Anda.</h6>
                            <button type="submit" class="btn btn-white btn-round fw-bold w-100 shadow-sm py-2.5">
                                <i class="fas fa-save me-2"></i> Simpan Perubahan Profil
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <style>
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

        .form-control { 
            border-radius: 10px; 
            padding: 0.65rem 1rem; 
            border: 1px solid #cbd5e1; 
            font-size: 14px;
        }

        .form-control:focus { 
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

        .mb-3.5 { margin-bottom: 0.875rem; }
        .text-xs { font-size: 0.75rem; }
        .text-sm { font-size: 0.875rem; }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Live logo image preview handler
            $('#logoInput').on('change', function(e) {
                let file = e.target.files[0];
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function(event) {
                        // Remove fallback icon if any, and set image source
                        $('#logoFallbackIcon').addClass('d-none');
                        
                        let img = $('#logoPreviewImg');
                        if (img.length === 0) {
                            $('.avatar-preview').html('<img src="" alt="Logo Preview" id="logoPreviewImg" style="width: 100%; height: 100%; object-fit: cover;">');
                            img = $('#logoPreviewImg');
                        }
                        img.attr('src', event.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Toggle input field state based on switch status
            function togglePatternInput() {
                let isChecked = $('#nisAutoGenerate').is(':checked');
                if (isChecked) {
                    $('#patternFormGroup').slideDown(250);
                    $('#nisPattern').prop('required', true);
                } else {
                    $('#patternFormGroup').slideUp(250);
                    $('#nisPattern').prop('required', false);
                }
            }

            $('#nisAutoGenerate').on('change', togglePatternInput);
            
            // Trigger check initially
            if (!$('#nisAutoGenerate').is(':checked')) {
                $('#patternFormGroup').hide();
                $('#nisPattern').prop('required', false);
            }

            // Spinner on submit
            $('#profileForm').on('submit', function() {
                $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin me-2"></i> Menyimpan...').attr('disabled', true);
            });
        });
    </script>
@endpush
