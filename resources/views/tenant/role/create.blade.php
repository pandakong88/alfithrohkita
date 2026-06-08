@extends('layouts.tenant')

@section('title', 'Buat Role Baru')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark">Manajemen Akses</h3>
            <p class="text-muted small mb-0">Definisikan role baru dan tentukan hak akses spesifik yang diberikan.</p>
        </div>
        <a href="{{ route('tenant.role.index') }}" class="btn btn-outline-secondary btn-round shadow-sm">
            <i class="fas fa-arrow-left me-2"></i> Kembali
        </a>
    </div>

    <form method="POST" action="{{ route('tenant.role.store') }}">
        @csrf

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4">
                <div class="d-flex">
                    <i class="fas fa-exclamation-circle mt-1 me-3"></i>
                    <ul class="mb-0 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4 card-custom card-accent-primary">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="card-title mb-0 fw-bold text-dark">Detail Role</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Nama Role <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   placeholder="Contoh: Bendahara, Pengasuh" required>
                            <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle me-1"></i> Gunakan nama yang deskriptif untuk fungsi jabatan.</small>
                        </div>
                        
                        <hr class="my-4">
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm btn-save">
                            <i class="fas fa-save me-2"></i> Simpan Role
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm card-custom">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
                        <h5 class="card-title mb-0 fw-bold text-dark">Pilih Permissions</h5>
                        <div class="custom-checkbox-all d-flex align-items-center">
                            <input type="checkbox" class="form-check-input" id="checkAll" style="cursor: pointer;">
                            <label class="form-check-label fw-bold text-primary ms-2 mb-0" for="checkAll" style="cursor: pointer; user-select: none;">Pilih Semua</label>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        @php
                            $groupedPermissions = [
                                'Santri' => [
                                    'view_santri' => 'Melihat daftar dan profil detail santri.',
                                    'manage_santri' => 'Membuat, mengubah, menghapus, dan mengimpor data santri.',
                                ],
                                'Wali Murid' => [
                                    'view_wali' => 'Melihat daftar wali murid dan detailnya.',
                                    'manage_wali' => 'Membuat, mengubah, menghapus, dan mengimpor data wali.',
                                ],
                                'Asrama (Kamar & Komplek)' => [
                                    'view_asrama' => 'Melihat daftar kamar, komplek, lemari, dan penghuninya.',
                                    'manage_asrama' => 'Mengelola komplek, kamar, lemari, slot lemari, dan penempatan santri.',
                                ],
                                'Kehadiran (Absensi)' => [
                                    'view_absensi' => 'Melihat rekap kehadiran santri dan data riwayat.',
                                    'manage_absensi' => 'Menginput presensi harian santri, mengelola sesi absensi, dan cetak lembar absen.',
                                ],
                                'Izin Keluar-Masuk' => [
                                    'view_perizinan' => 'Melihat status izin keluar-masuk santri dan riwayat perizinan.',
                                    'manage_perizinan' => 'Mengelola surat izin (pembuatan izin baru, scan kembali, setup template).',
                                ],
                                'Pelanggaran Santri' => [
                                    'view_pelanggaran' => 'Melihat riwayat catatan poin pelanggaran santri.',
                                    'manage_pelanggaran' => 'Mencatat pelanggaran baru, mengubah, membatalkan, dan kelola kategori hukuman.',
                                ],
                                'Buku Pedoman (CMS)' => [
                                    'view_cms' => 'Melihat daftar buku pedoman santri.',
                                    'manage_cms' => 'Mengunggah, mengedit, dan mengarsipkan versi buku pedoman santri.',
                                ],
                                'Manajemen Staf & Akses' => [
                                    'view_users' => 'Melihat daftar staf/user dan hak akses role.',
                                    'manage_users' => 'Mengelola staf/user baru, menonaktifkan akun, dan edit hak akses role.',
                                ],
                                'Pengaturan Sistem & Keuangan' => [
                                    'view_keuangan' => 'Melihat ringkasan data keuangan pondok.',
                                    'manage_keuangan' => 'Mengelola transaksi keuangan pondok.',
                                    'manage_settings' => 'Mengelola profil pondok, templat impor excel, dan kolom kustom.',
                                ]
                            ];
                        @endphp

                        <div class="row">
                            @foreach($groupedPermissions as $moduleName => $modulePerms)
                                <div class="col-12 mb-4">
                                    <div class="card border shadow-none mb-0" style="border-radius: 12px; background-color: #ffffff; border-color: #e2e8f0 !important;">
                                        <div class="card-header py-2.5 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                                            <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-th-large me-2 text-primary" style="font-size: 14px;"></i> Modul {{ $moduleName }}</h6>
                                            <button type="button" class="btn btn-xs btn-link text-primary fw-bold p-0 check-module-all decoration-none" data-module="{{ Str::slug($moduleName) }}" style="font-size: 12px; border: none; background: none;">Pilih Semua</button>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="row">
                                                @foreach($modulePerms as $permName => $permDesc)
                                                    @php
                                                        $permissionObj = $permissions->firstWhere('name', $permName);
                                                    @endphp
                                                    @if($permissionObj)
                                                        <div class="col-md-6 mb-3">
                                                            <div class="permission-card border rounded p-3 h-100 transition-all shadow-xs">
                                                                <div class="custom-checkbox d-flex align-items-start">
                                                                    <input type="checkbox" 
                                                                           name="permissions[]" 
                                                                           value="{{ $permName }}" 
                                                                           class="form-check-input permission-checkbox module-{{ Str::slug($moduleName) }} mt-1" 
                                                                           id="perm-{{ $permissionObj->id }}"
                                                                           {{ in_array($permName, old('permissions', [])) ? 'checked' : '' }}>
                                                                    <label class="form-check-label d-block ms-2 flex-grow-1" for="perm-{{ $permissionObj->id }}" style="cursor: pointer; user-select: none; min-width: 0;">
                                                                        <span class="fw-bold text-dark d-block text-capitalize mb-1" style="word-break: break-word; white-space: normal;">{{ str_replace('_', ' ', $permName) }}</span>
                                                                        <span class="text-muted small d-block" style="font-size: 11.5px; line-height: 1.4; word-break: break-word; white-space: normal;">{{ $permDesc }}</span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    /* Card Custom Styling */
    .card-custom {
        border-radius: 16px !important;
        background: #ffffff;
    }
    .card-accent-primary {
        border-left: 5px solid #4F46E5 !important;
    }
    
    /* Buttons Custom */
    .btn-round {
        border-radius: 50px;
        padding: 0.6rem 1.4rem;
        font-weight: 600;
    }
    .btn-save {
        border-radius: 12px;
        font-size: 1rem;
        padding: 0.8rem 1rem;
        background: linear-gradient(135deg, #4F46E5 0%, #3B82F6 100%);
        border: none;
        transition: all 0.2s ease;
    }
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
    }

    /* Form Control */
    .form-control {
        border-radius: 10px;
        padding: 0.75rem 1rem;
        border: 1px solid #E2E8F0;
        font-size: 0.95rem;
        color: #1E293B;
    }
    .form-control:focus {
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        border-color: #4F46E5;
    }

    /* Permission Card Effect */
    .permission-card {
        background-color: #F8FAFC;
        border: 1px solid #E2E8F0 !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 12px !important;
    }
    .permission-card:hover {
        background-color: #F1F5F9;
        border-color: #CBD5E1 !important;
        transform: translateY(-2px);
    }
    .permission-card.selected {
        background-color: rgba(79, 70, 229, 0.05);
        border-color: #4F46E5 !important;
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.08);
    }

    .custom-checkbox .form-check-input {
        width: 1.25em;
        height: 1.25em;
        border-radius: 6px;
        cursor: pointer;
        border-color: #CBD5E1;
        margin-left: 0 !important;
        margin-right: 0 !important;
        flex-shrink: 0;
    }
    .custom-checkbox .form-check-input:checked {
        background-color: #4F46E5;
        border-color: #4F46E5;
    }
    .custom-checkbox-all .form-check-input {
        width: 1.2em;
        height: 1.2em;
        border-radius: 4px;
        cursor: pointer;
        border-color: #CBD5E1;
        margin-left: 0 !important;
        margin-right: 0 !important;
        flex-shrink: 0;
    }
    .custom-checkbox-all .form-check-input:checked {
        background-color: #4F46E5;
        border-color: #4F46E5;
    }
</style>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll = document.getElementById('checkAll');
        const checkboxes = document.querySelectorAll('.permission-checkbox');

        // Logic Pilih Semua Global
        if (checkAll) {
            checkAll.addEventListener('change', function() {
                checkboxes.forEach(cb => {
                    cb.checked = this.checked;
                    updateCardStyle(cb);
                });
                
                // Update all module check buttons text
                document.querySelectorAll('.check-module-all').forEach(btn => {
                    btn.textContent = this.checked ? 'Batal Pilih Semua' : 'Pilih Semua';
                });
            });
        }

        // Logic Pilih Semua per Modul
        const moduleCheckers = document.querySelectorAll('.check-module-all');
        moduleCheckers.forEach(checker => {
            checker.addEventListener('click', function() {
                const moduleSlug = this.getAttribute('data-module');
                const moduleCheckboxes = document.querySelectorAll('.module-' + moduleSlug);
                
                // Determine if all are currently checked
                const allChecked = Array.from(moduleCheckboxes).every(cb => cb.checked);
                
                moduleCheckboxes.forEach(cb => {
                    cb.checked = !allChecked;
                    updateCardStyle(cb);
                });
                
                // Update checker text
                this.textContent = allChecked ? 'Pilih Semua' : 'Batal Pilih Semua';
                updateGlobalCheckAll();
            });
        });

        // Logic Highlight Card saat diklik
        checkboxes.forEach(cb => {
            updateCardStyle(cb);

            cb.addEventListener('change', function() {
                updateCardStyle(this);
                updateGlobalCheckAll();
                updateModuleCheckerText(this);
            });
        });

        function updateCardStyle(checkbox) {
            const card = checkbox.closest('.permission-card');
            if (checkbox.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
        }

        function updateGlobalCheckAll() {
            if (checkAll) {
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                checkAll.checked = allChecked;
            }
        }

        function updateModuleCheckerText(checkbox) {
            const moduleCard = checkbox.closest('.card');
            const checkerBtn = moduleCard.querySelector('.check-module-all');
            if (checkerBtn) {
                const moduleSlug = checkerBtn.getAttribute('data-module');
                const moduleCheckboxes = document.querySelectorAll('.module-' + moduleSlug);
                const allChecked = Array.from(moduleCheckboxes).every(cb => cb.checked);
                checkerBtn.textContent = allChecked ? 'Batal Pilih Semua' : 'Pilih Semua';
            }
        }

        // Run initial trigger for loaded roles
        document.querySelectorAll('.check-module-all').forEach(btn => {
            const moduleSlug = btn.getAttribute('data-module');
            const moduleCheckboxes = document.querySelectorAll('.module-' + moduleSlug);
            const allChecked = Array.from(moduleCheckboxes).every(cb => cb.checked);
            if (allChecked && moduleCheckboxes.length > 0) {
                btn.textContent = 'Batal Pilih Semua';
            }
        });
        updateGlobalCheckAll();
    });
</script>
@endpush