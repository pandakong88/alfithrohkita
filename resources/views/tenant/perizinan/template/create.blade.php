@extends('layouts.tenant')

@section('content')
<form id="main-form" method="POST" action="{{ isset($template) ? route('tenant.template-perizinan.update', $template->id) : route('tenant.template-perizinan.store') }}">
    @csrf
    @if(isset($template)) @method('PUT') @endif
    
    {{-- Dynamic Document Styling Rules --}}
    <input type="hidden" name="rules[styling][font_family]" id="style-font-family" value="{{ old('rules.styling.font_family', $template->rules['styling']['font_family'] ?? "'Times New Roman', Times, serif") }}">
    <input type="hidden" name="rules[styling][line_height]" id="style-line-height" value="{{ old('rules.styling.line_height', $template->rules['styling']['line_height'] ?? '1.4') }}">
    <input type="hidden" name="rules[styling][border_frame]" id="style-border-frame" value="{{ old('rules.styling.border_frame', $template->rules['styling']['border_frame'] ?? '0') }}">
    <input type="hidden" name="rules[design_mode]" id="design-mode" value="{{ old('rules.design_mode', $template->rules['design_mode'] ?? 'wizard') }}">

    <div class="studio-wrapper">
        {{-- 1. TOP BAR --}}
        <div class="studio-header d-flex align-items-center justify-content-between px-4">
            <div class="d-flex align-items-center">
                <a href="{{ route('tenant.template-perizinan.index') }}" class="btn-back text-dark me-3"><i class="fas fa-arrow-left"></i></a>
                <div class="document-info">
                    <input type="text" name="nama" id="doc-title" class="input-transparent-title" 
                           value="{{ old('nama', $template->nama ?? '') }}" placeholder="Nama Template Surat..." required>
                    <div class="d-flex align-items-center gap-3">
                        <div class="status-indicator"><span class="dot pulse"></span> <span id="save-status" class="small text-muted">Drafting...</span></div>
                        <div class="form-check p-0 m-0 d-flex align-items-center">
                            <input type="checkbox" name="is_active" id="isActive" value="1" {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }} class="form-check-input me-1">
                            <label class="small fw-bold text-muted mb-0" for="isActive" style="cursor:pointer;">Active</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="toggleZenMode()">
                    <i class="fas fa-expand-arrows-alt me-1"></i> Zen Mode
                </button>
                <div class="layout-control bg-light px-3 py-1 rounded-pill d-flex align-items-center border shadow-sm">
                    <label class="small fw-bold text-muted me-2 mb-0">FORMAT:</label>
                    <select name="layout_print" id="layout_print" class="border-0 bg-transparent fw-bold text-primary small">
                        <option value="1" {{ (old('layout_print', $template->layout_print ?? '') == 1) ? 'selected' : '' }}>A4/F4 (Full)</option>
                        <option value="2" {{ (old('layout_print', $template->layout_print ?? '') == 2) ? 'selected' : '' }}>A5 (2/Page)</option>
                        <option value="4" {{ (old('layout_print', $template->layout_print ?? '') == 4) ? 'selected' : '' }}>A6 (4/Page)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-round px-4 shadow">
                    <i class="fas fa-rocket me-2"></i> SIMPAN TEMPLATE
                </button>
            </div>
        </div>

        <div class="studio-body">
            {{-- 2. LEFT SIDEBAR --}}
            <div class="studio-sidebar-left shadow-sm" id="left-sidebar">
                {{-- Mode Switcher --}}
                <div class="p-3 border-bottom bg-white">
                    <div class="d-flex bg-light p-1 rounded-pill border" style="gap:2px;">
                        <button type="button" class="btn btn-sm btn-round w-50 py-1 border-0" id="btn-mode-wizard" style="font-size: 10px; font-weight: bold; border-radius: 50px;">
                            <i class="fas fa-magic me-1"></i> WIZARD
                        </button>
                        <button type="button" class="btn btn-sm btn-round w-50 py-1 border-0" id="btn-mode-manual" style="font-size: 10px; font-weight: bold; border-radius: 50px;">
                            <i class="fas fa-edit me-1"></i> MANUAL
                        </button>
                    </div>
                </div>

                {{-- PANE WIZARD --}}
                <div id="wizard-pane" class="p-3" style="display: none; overflow-y: auto; flex: 1;">
                    <h6 class="fw-bold text-dark mb-3" style="font-size:12px;"><i class="fas fa-magic text-primary me-2"></i>Wizard Pengisi Surat</h6>
                    
                    <div class="accordion" id="wizardAccordion">
                        {{-- Section 1: Kepala (Kop) Surat --}}
                        <div class="accordion-item border shadow-sm rounded mb-2 overflow-hidden">
                            <h2 class="accordion-header" id="headingKop">
                                <button class="accordion-button py-2 px-3 bg-light text-dark fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKop" style="font-size:11px; box-shadow:none;">
                                    <i class="fas fa-heading text-primary me-2"></i>1. Kepala (Kop) Surat
                                </button>
                            </h2>
                            <div id="collapseKop" class="accordion-collapse collapse" data-bs-parent="#wizardAccordion">
                                <div class="accordion-body p-3 bg-white border-top">
                                    <div class="form-check p-0 mb-2 d-flex align-items-center">
                                        <input class="form-check-input ms-0 me-2" type="checkbox" id="wz-use-kop" name="rules[wizard_state][use_kop]" value="1" {{ old('rules.wizard_state.use_kop', $template->rules['wizard_state']['use_kop'] ?? '1') === '1' ? 'checked' : '' }} style="width:14px; height:14px; cursor:pointer;">
                                        <label class="small fw-bold text-muted mb-0" for="wz-use-kop" style="cursor:pointer; font-size:10px;">Tampilkan Kop Surat</label>
                                    </div>
                                    <div id="wz-kop-fields" class="mt-2">
                                        <div class="mb-2">
                                            <label class="fw-bold text-muted mb-1" style="font-size:9px;">Yayasan / Organisasi</label>
                                            <input type="text" id="wz-kop-yayasan" name="rules[wizard_state][kop_yayasan]" class="form-control form-control-sm" style="font-size:10px;" value="{{ old('rules.wizard_state.kop_yayasan', $template->rules['wizard_state']['kop_yayasan'] ?? 'YAYASAN PONDOK PESANTREN AL-FITROH') }}">
                                        </div>
                                        <div class="mb-2">
                                            <label class="fw-bold text-muted mb-1" style="font-size:9px;">Nama Pondok / Instansi</label>
                                            <input type="text" id="wz-kop-pondok" name="rules[wizard_state][kop_pondok]" class="form-control form-control-sm" style="font-size:10px;" value="{{ old('rules.wizard_state.kop_pondok', $template->rules['wizard_state']['kop_pondok'] ?? 'PONDOK PESANTREN AL-FITROH KITA') }}">
                                        </div>
                                        <div class="mb-2">
                                            <label class="fw-bold text-muted mb-1" style="font-size:9px;">Alamat Lengkap</label>
                                            <input type="text" id="wz-kop-alamat" name="rules[wizard_state][kop_alamat]" class="form-control form-control-sm" style="font-size:10px;" value="{{ old('rules.wizard_state.kop_alamat', $template->rules['wizard_state']['kop_alamat'] ?? 'Jl. Raya Jombang No. 123, Jombang, Jawa Timur') }}">
                                        </div>
                                        <div class="mb-2">
                                            <label class="fw-bold text-muted mb-1" style="font-size:9px;">Kontak (Telp / Email)</label>
                                            <input type="text" id="wz-kop-kontak" name="rules[wizard_state][kop_kontak]" class="form-control form-control-sm" style="font-size:10px;" value="{{ old('rules.wizard_state.kop_kontak', $template->rules['wizard_state']['kop_kontak'] ?? 'Telp: 0812-3456-789 | Email: info@alfitroh.com') }}">
                                        </div>
                                        <div class="mb-2">
                                            <label class="fw-bold text-muted mb-1" style="font-size:9px;">URL Logo Lembaga</label>
                                            <input type="text" id="wz-kop-logo" name="rules[wizard_state][kop_logo]" class="form-control form-control-sm" style="font-size:10px;" value="{{ old('rules.wizard_state.kop_logo', $template->rules['wizard_state']['kop_logo'] ?? 'https://via.placeholder.com/80') }}">
                                        </div>
                                        <div class="form-check p-0 m-0 d-flex align-items-center">
                                            <input class="form-check-input ms-0 me-2" type="checkbox" id="wz-kop-double-line" name="rules[wizard_state][kop_double_line]" value="1" {{ old('rules.wizard_state.kop_double_line', $template->rules['wizard_state']['kop_double_line'] ?? '1') === '1' ? 'checked' : '' }} style="width:14px; height:14px; cursor:pointer;">
                                            <label class="small fw-bold text-muted mb-0" for="wz-kop-double-line" style="cursor:pointer; font-size:10px;">Garis Pembatas Ganda</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Judul & Pembuka --}}
                        <div class="accordion-item border shadow-sm rounded mb-2 overflow-hidden">
                            <h2 class="accordion-header" id="headingIsi">
                                <button class="accordion-button py-2 px-3 bg-light text-dark fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseIsi" style="font-size:11px; box-shadow:none;">
                                    <i class="fas fa-file-alt text-primary me-2"></i>2. Judul & Pembuka Surat
                                </button>
                            </h2>
                            <div id="collapseIsi" class="accordion-collapse collapse" data-bs-parent="#wizardAccordion">
                                <div class="accordion-body p-3 bg-white border-top">
                                    <div class="mb-2">
                                        <label class="fw-bold text-muted mb-1" style="font-size:9px;">Judul Surat</label>
                                        <input type="text" id="wz-judul" name="rules[wizard_state][judul]" class="form-control form-control-sm" style="font-size:10px;" value="{{ old('rules.wizard_state.judul', $template->rules['wizard_state']['judul'] ?? 'SURAT IZIN KELUAR RESMI') }}">
                                    </div>
                                    <div class="mb-2">
                                        <label class="fw-bold text-muted mb-1" style="font-size:9px;">Kalimat Pembuka</label>
                                        <textarea id="wz-pembuka" name="rules[wizard_state][pembuka]" class="form-control form-control-sm" rows="3" style="font-size:10px;">{{ old('rules.wizard_state.pembuka', $template->rules['wizard_state']['pembuka'] ?? 'Yang bertanda tangan di bawah ini menerangkan bahwa santri berikut:') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section 3: Data Identitas Santri --}}
                        <div class="accordion-item border shadow-sm rounded mb-2 overflow-hidden">
                            <h2 class="accordion-header" id="headingIdentitas">
                                <button class="accordion-button py-2 px-3 bg-light text-dark fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseIdentitas" style="font-size:11px; box-shadow:none;">
                                    <i class="fas fa-id-card text-primary me-2"></i>3. Kolom Identitas Santri
                                </button>
                            </h2>
                            <div id="collapseIdentitas" class="accordion-collapse collapse" data-bs-parent="#wizardAccordion">
                                <div class="accordion-body p-3 bg-white border-top">
                                    <p class="text-muted mb-2" style="font-size:9px;">Pilih data santri yang akan ditampilkan di dalam tabel identitas surat:</p>
                                    <div class="form-check p-0 mb-2 d-flex align-items-center">
                                        <input class="form-check-input ms-0 me-2" type="checkbox" id="wz-show-nama" name="rules[wizard_state][show_nama]" value="1" {{ old('rules.wizard_state.show_nama', $template->rules['wizard_state']['show_nama'] ?? '1') === '1' ? 'checked' : '' }} style="width:14px; height:14px; cursor:pointer;">
                                        <label class="small fw-bold text-muted mb-0" for="wz-show-nama" style="cursor:pointer; font-size:10px;">Nama Lengkap ({nama_santri})</label>
                                    </div>
                                    <div class="form-check p-0 mb-2 d-flex align-items-center">
                                        <input class="form-check-input ms-0 me-2" type="checkbox" id="wz-show-nis" name="rules[wizard_state][show_nis]" value="1" {{ old('rules.wizard_state.show_nis', $template->rules['wizard_state']['show_nis'] ?? '1') === '1' ? 'checked' : '' }} style="width:14px; height:14px; cursor:pointer;">
                                        <label class="small fw-bold text-muted mb-0" for="wz-show-nis" style="cursor:pointer; font-size:10px;">Nomor Induk / NIS ({nis})</label>
                                    </div>
                                    <div class="form-check p-0 mb-2 d-flex align-items-center">
                                        <input class="form-check-input ms-0 me-2" type="checkbox" id="wz-show-kamar" name="rules[wizard_state][show_kamar]" value="1" {{ old('rules.wizard_state.show_kamar', $template->rules['wizard_state']['show_kamar'] ?? '1') === '1' ? 'checked' : '' }} style="width:14px; height:14px; cursor:pointer;">
                                        <label class="small fw-bold text-muted mb-0" for="wz-show-kamar" style="cursor:pointer; font-size:10px;">Kamar Santri ({nama_kamar})</label>
                                    </div>
                                    <div class="form-check p-0 mb-2 d-flex align-items-center">
                                        <input class="form-check-input ms-0 me-2" type="checkbox" id="wz-show-status" name="rules[wizard_state][show_status]" value="1" {{ old('rules.wizard_state.show_status', $template->rules['wizard_state']['show_status'] ?? '0') === '1' ? 'checked' : '' }} style="width:14px; height:14px; cursor:pointer;">
                                        <label class="small fw-bold text-muted mb-0" for="wz-show-status" style="cursor:pointer; font-size:10px;">Status Keberadaan ({status_santri})</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section 4: Ketentuan & Penutup --}}
                        <div class="accordion-item border shadow-sm rounded mb-2 overflow-hidden">
                            <h2 class="accordion-header" id="headingPenutup">
                                <button class="accordion-button py-2 px-3 bg-light text-dark fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePenutup" style="font-size:11px; box-shadow:none;">
                                    <i class="fas fa-paragraph text-primary me-2"></i>4. Ketentuan & Penutup
                                </button>
                            </h2>
                            <div id="collapsePenutup" class="accordion-collapse collapse" data-bs-parent="#wizardAccordion">
                                <div class="accordion-body p-3 bg-white border-top">
                                    <div class="mb-2">
                                        <label class="fw-bold text-muted mb-1" style="font-size:9px;">Kalimat Ketentuan Keperluan</label>
                                        <textarea id="wz-ketentuan" name="rules[wizard_state][ketentuan]" class="form-control form-control-sm" rows="3" style="font-size:10px;">{{ old('rules.wizard_state.ketentuan', $template->rules['wizard_state']['ketentuan'] ?? 'Telah diberikan izin untuk keluar lingkungan pondok pesantren dengan alasan keperluan: <b>{keperluan}</b>.') }}</textarea>
                                    </div>
                                    <div class="mb-2">
                                        <label class="fw-bold text-muted mb-1" style="font-size:9px;">Masa Berlaku Izin</label>
                                        <textarea id="wz-berlaku" name="rules[wizard_state][berlaku]" class="form-control form-control-sm" rows="2" style="font-size:10px;">{{ old('rules.wizard_state.berlaku', $template->rules['wizard_state']['berlaku'] ?? 'Surat izin ini berlaku sejak tanggal <b>{tgl_keluar}</b> sampai batas kembali tanggal <b>{batas_kembali}</b>.') }}</textarea>
                                    </div>
                                    <div class="mb-2">
                                        <label class="fw-bold text-muted mb-1" style="font-size:9px;">Kalimat Penutup</label>
                                        <textarea id="wz-penutup" name="rules[wizard_state][penutup]" class="form-control form-control-sm" rows="3" style="font-size:10px;">{{ old('rules.wizard_state.penutup', $template->rules['wizard_state']['penutup'] ?? 'Demikian surat izin ini dibuat untuk dipergunakan sebagaimana mestinya. Harap kembali tepat waktu sesuai batas kembali.') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section 5: TTD & QR Code --}}
                        <div class="accordion-item border shadow-sm rounded mb-2 overflow-hidden">
                            <h2 class="accordion-header" id="headingTtd">
                                <button class="accordion-button py-2 px-3 bg-light text-dark fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTtd" style="font-size:11px; box-shadow:none;">
                                    <i class="fas fa-signature text-primary me-2"></i>5. TTD & QR Code
                                </button>
                            </h2>
                            <div id="collapseTtd" class="accordion-collapse collapse" data-bs-parent="#wizardAccordion">
                                <div class="accordion-body p-3 bg-white border-top">
                                    <div class="form-check p-0 mb-3 d-flex align-items-center">
                                        <input class="form-check-input ms-0 me-2" type="checkbox" id="wz-show-qrcode" name="rules[wizard_state][show_qrcode]" value="1" {{ old('rules.wizard_state.show_qrcode', $template->rules['wizard_state']['show_qrcode'] ?? '1') === '1' ? 'checked' : '' }} style="width:14px; height:14px; cursor:pointer;">
                                        <label class="small fw-bold text-muted mb-0" for="wz-show-qrcode" style="cursor:pointer; font-size:10px;">Tampilkan QR Code Verifikasi</label>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <label class="fw-bold text-muted mb-1" style="font-size:9px;">Tata Letak Tanda Tangan</label>
                                        <select id="wz-ttd-layout" name="rules[wizard_state][ttd_layout]" class="form-select form-select-sm" style="font-size:10px;">
                                            <option value="kanan" {{ old('rules.wizard_state.ttd_layout', $template->rules['wizard_state']['ttd_layout'] ?? 'kanan') === 'kanan' ? 'selected' : '' }}>Satu Orang (Kanan Saja)</option>
                                            <option value="dua" {{ old('rules.wizard_state.ttd_layout', $template->rules['wizard_state']['ttd_layout'] ?? 'dua') === 'dua' ? 'selected' : '' }}>Dua Orang (Kiri & Kanan)</option>
                                            <option value="none" {{ old('rules.wizard_state.ttd_layout', $template->rules['wizard_state']['ttd_layout'] ?? 'none') === 'none' ? 'selected' : '' }}>Tanpa Tanda Tangan</option>
                                        </select>
                                    </div>

                                    <div id="wz-ttd-kanan-fields">
                                        <div class="mb-2">
                                            <label class="fw-bold text-muted mb-1" style="font-size:9px;">Jabatan Penandatangan Kanan</label>
                                            <input type="text" id="wz-ttd-kanan-role" name="rules[wizard_state][ttd_kanan_role]" class="form-control form-control-sm" style="font-size:10px;" value="{{ old('rules.wizard_state.ttd_kanan_role', $template->rules['wizard_state']['ttd_kanan_role'] ?? 'Pengasuh Pondok') }}">
                                        </div>
                                        <div class="mb-2">
                                            <label class="fw-bold text-muted mb-1" style="font-size:9px;">Nama Penandatangan Kanan</label>
                                            <input type="text" id="wz-ttd-kanan-name" name="rules[wizard_state][ttd_kanan_name]" class="form-control form-control-sm" style="font-size:10px;" value="{{ old('rules.wizard_state.ttd_kanan_name', $template->rules['wizard_state']['ttd_kanan_name'] ?? 'KH. Ahmad Dahlan') }}">
                                        </div>
                                    </div>

                                    <div id="wz-ttd-kiri-fields" style="display: none;">
                                        <div class="mb-2">
                                            <label class="fw-bold text-muted mb-1" style="font-size:9px;">Jabatan Penandatangan Kiri</label>
                                            <input type="text" id="wz-ttd-kiri-role" name="rules[wizard_state][ttd_kiri_role]" class="form-control form-control-sm" style="font-size:10px;" value="{{ old('rules.wizard_state.ttd_kiri_role', $template->rules['wizard_state']['ttd_kiri_role'] ?? 'Keamanan Asrama') }}">
                                        </div>
                                        <div class="mb-2">
                                            <label class="fw-bold text-muted mb-1" style="font-size:9px;">Nama Penandatangan Kiri</label>
                                            <input type="text" id="wz-ttd-kiri-name" name="rules[wizard_state][ttd_kiri_name]" class="form-control form-control-sm" style="font-size:10px;" value="{{ old('rules.wizard_state.ttd_kiri_name', $template->rules['wizard_state']['ttd_kiri_name'] ?? 'Ust. Umar Bin Khattab') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tabs-header">
                    <button type="button" class="tab-btn active" data-tab="elements"><i class="fas fa-th-large mb-1"></i><span>Layout</span></button>
                    <button type="button" class="tab-btn" data-tab="variables"><i class="fas fa-database mb-1"></i><span>Data Tags</span></button>
                    <button type="button" class="tab-btn" data-tab="settings"><i class="fas fa-cog mb-1"></i><span>Settings</span></button>
                </div>
                
                <div class="tab-content-wrapper p-3" style="flex: 1; overflow-y: auto;">
                    {{-- Tab Elements --}}
                    <div id="elements" class="content-pane active">
                        <label class="section-label">Struktur Utama</label>
                        <div class="element-grid mb-3">
                            <div class="el-item" onclick="insertKop()"><i class="fas fa-window-maximize"></i><span>Kop Surat</span></div>
                            <div class="el-item" onclick="insertIdentitas()"><i class="fas fa-list-ul"></i><span>Identitas</span></div>
                            <div class="el-item" onclick="insertGaris()"><i class="fas fa-minus"></i><span>Garis Tebal</span></div>
                            <div class="el-item" onclick="insertQrCode()"><i class="fas fa-qrcode"></i><span>QR Code</span></div>
                        </div>

                        <label class="section-label">Tanda Tangan</label>
                        <div class="element-grid mb-3">
                            <div class="el-item" onclick="insertTandaTangan('kanan')"><i class="fas fa-file-signature"></i><span>TTD Kanan</span></div>
                            <div class="el-item" onclick="insertTandaTangan('dua')"><i class="fas fa-users"></i><span>TTD 2 Org</span></div>
                        </div>

                        <label class="section-label">Template Presets</label>
                        <div class="d-flex flex-column gap-2 mb-3">
                            <button type="button" class="btn btn-outline-primary btn-sm btn-round text-start w-100 px-3 py-2" onclick="loadPreset('formal')">
                                <i class="fas fa-file-invoice me-2"></i> Surat Resmi (A4)
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm btn-round text-start w-100 px-3 py-2" onclick="loadPreset('belanja')">
                                <i class="fas fa-shopping-cart me-2"></i> Izin Belanja (A6)
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm btn-round text-start w-100 px-3 py-2" onclick="loadPreset('sakit')">
                                <i class="fas fa-heartbeat me-2"></i> Surat Sakit (A4)
                            </button>
                        </div>

                        <label class="section-label">Generator Kop Surat</label>
                        <div class="bg-light p-2 rounded border mb-3">
                            <button type="button" class="btn btn-outline-primary btn-sm btn-round w-100 py-2" data-bs-toggle="modal" data-bs-target="#kopBuilderModal">
                                <i class="fas fa-tools me-2"></i> Buat Kop Surat Rapi
                            </button>
                        </div>

                        <label class="section-label">Pengaturan Gaya Dokumen</label>
                        <div class="bg-light p-3 rounded border mb-3">
                            <div class="mb-2">
                                <label class="small fw-bold text-muted mb-1" style="font-size:9px;">Jenis Font Keluarga</label>
                                <select id="doc-font-family" class="form-select form-select-sm" style="font-size:10px;">
                                    <option value="'Times New Roman', Times, serif">Times New Roman (Formal)</option>
                                    <option value="Arial, Helvetica, sans-serif">Arial (Modern)</option>
                                    <option value="Georgia, serif">Georgia (Klasik)</option>
                                    <option value="'Courier New', Courier, monospace">Courier (Ketik Manual)</option>
                                    <option value="'Inter', sans-serif">Inter (Sleek)</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold text-muted mb-1" style="font-size:9px;">Jarak Spasi Baris</label>
                                <select id="doc-line-height" class="form-select form-select-sm" style="font-size:10px;">
                                    <option value="1.2">Sempit (1.2)</option>
                                    <option value="1.4" selected>Normal (1.4)</option>
                                    <option value="1.6">Lebar (1.6)</option>
                                    <option value="2.0">Ganda (2.0)</option>
                                </select>
                            </div>
                            <div class="form-check p-0 m-0 d-flex align-items-center">
                                <input type="checkbox" id="doc-border-frame" class="form-check-input ms-0 me-2" style="cursor:pointer; width: 14px; height: 14px;">
                                <label class="small fw-bold text-muted mb-0" for="doc-border-frame" style="cursor: pointer; font-size:10px;">Bingkai Surat Ganda</label>
                            </div>
                        </div>

                        <label class="section-label">Gambar/Stempel</label>
                        <div class="upload-btn-wrapper mb-2" onclick="document.getElementById('image-upload-input').click()">
                            <i class="fas fa-upload mb-1"></i>
                            <span id="upload-text">UPLOAD ASSET</span>
                            <input type="file" id="image-upload-input" class="d-none" accept="image/*">
                        </div>
                        <div id="image-gallery" class="element-grid">
                            @foreach($assets ?? [] as $asset)
                            <div class="el-item position-relative" onclick="insertImg('{{ asset('storage/'.$asset->file_path) }}')" style="padding:4px;" id="asset-card-{{ $asset->id }}">
                                <img src="{{ asset('storage/'.$asset->file_path) }}" style="width:100%; height:45px; object-fit:contain;">
                                <button type="button" class="btn-delete-asset position-absolute" data-id="{{ $asset->id }}" 
                                        style="top:0; right:0; border:none; background:rgba(220,53,69,0.8); color:white; border-radius:50%; width:16px; height:16px; font-size:10px; display:flex; align-items:center; justify-content:center; padding:0; line-height:1;">×</button>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    {{-- Tab Variables --}}
                    <div id="variables" class="content-pane">
                        <input type="text" id="searchVar" class="form-control form-control-sm rounded-pill mb-3" placeholder="Cari data santri...">
                        <div class="variable-scroll" style="max-height: calc(100vh - 280px); overflow-y: auto;">
                            @foreach($variables as $cat => $items)
                                <div class="var-group">
                                    <div class="var-group-title" style="font-size: 10px; font-weight: bold; color: #94a3b8; margin: 10px 0 5px 0;">{{ strtoupper($cat) }}</div>
                                    @foreach($items as $key => $value)
                                        <div class="var-item var-btn shadow-sm" data-var="{{ $key }}" data-search="{{ strtolower($key) }}">
                                            <span class="var-label">{{ str_replace(['{','}'], '', $key) }}</span>
                                            <i class="fas fa-plus text-primary small"></i>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Tab Settings --}}
                    <div id="settings" class="content-pane">
                        <div class="form-group p-0 mb-3">
                            <label class="small fw-bold text-muted mb-1">Deskripsi Operasional</label>
                            <textarea name="deskripsi" class="form-control form-control-sm" rows="2" placeholder="Catatan penggunaan...">{{ old('deskripsi', $template->deskripsi ?? '') }}</textarea>
                        </div>
                        
                        <div class="mb-3">
                            <div class="custom-control custom-checkbox bg-light border rounded p-2">
                                <input type="checkbox" name="is_default" id="isDefault" value="1" {{ old('is_default', $template->is_default ?? false) ? 'checked' : '' }} class="custom-control-input">
                                <label class="custom-control-label small fw-bold text-primary mb-0" for="isDefault" style="cursor:pointer;">Default Template</label>
                            </div>
                        </div>
                        
                        <label class="section-label">Kebijakan Keterlambatan</label>
                        <div class="bg-light p-2 rounded border mb-3">
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" id="rule_late_penalty_enabled" name="rules[late_penalty][enabled]" value="1" {{ old('rules.late_penalty.enabled', $template->rules['late_penalty']['enabled'] ?? false) ? 'checked' : '' }} class="custom-control-input">
                                <label class="custom-control-label small fw-bold text-dark" for="rule_late_penalty_enabled" style="cursor:pointer;">Denda Poin Otomatis</label>
                            </div>
                            <div class="row g-1 mt-1" id="late-penalty-settings" style="display: {{ old('rules.late_penalty.enabled', $template->rules['late_penalty']['enabled'] ?? false) ? 'flex' : 'none' }};">
                                <div class="col-6">
                                    <label class="small text-muted" style="font-size:9px;">Tiap (Menit)</label>
                                    <input type="number" name="rules[late_penalty][interval_minutes]" class="form-control form-control-sm" value="{{ old('rules.late_penalty.interval_minutes', $template->rules['late_penalty']['interval_minutes'] ?? 60) }}">
                                </div>
                                <div class="col-6">
                                    <label class="small text-muted" style="font-size:9px;">Denda Poin</label>
                                    <input type="number" name="rules[late_penalty][points_per_interval]" class="form-control form-control-sm" value="{{ old('rules.late_penalty.points_per_interval', $template->rules['late_penalty']['points_per_interval'] ?? 5) }}">
                                </div>
                            </div>
                        </div>

                        <label class="section-label">Alur Persetujuan</label>
                        <div class="bg-light p-2 rounded border">
                            <p class="text-muted" style="font-size: 9px; line-height: 1.2;">Tentukan pos pemeriksaan/persetujuan yang harus dilewati sebelum izin aktif.</p>
                            <div id="workflow-steps-container" class="d-flex flex-column gap-2 mb-2" style="max-height: 200px; overflow-y: auto;">
                                {{-- Dynamically loaded steps --}}
                            </div>
                            <button type="button" class="btn btn-outline-info btn-xs w-100" id="btn-add-step">
                                <i class="fas fa-plus me-1"></i> Tambah Pos
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. CENTER: THE CANVAS --}}
            <div class="studio-canvas" id="canvas-area">
                <div class="paper-sheet shadow-2xl" style="position: relative;">
                    {{-- Overlay for Wizard Mode --}}
                    <div id="wizard-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255, 255, 255, 0.05); z-index: 1000; cursor: not-allowed; display: none;">
                        <div style="position: absolute; top: 15px; left: 50%; transform: translateX(-50%); background: rgba(30, 41, 59, 0.9); color: white; padding: 6px 15px; border-radius: 20px; font-size: 10px; font-weight: bold; border: 1px solid #334155; box-shadow: 0 4px 10px rgba(0,0,0,0.15); pointer-events: none; white-space: nowrap;">
                            <i class="fas fa-magic text-primary me-2"></i>Mode Wizard Aktif - Edit Teks Lewat Form Kiri
                        </div>
                    </div>
                    <textarea name="format_surat" id="suratEditor">{{ old('format_surat', $template->format_surat ?? '') }}</textarea>
                </div>
            </div>

            {{-- 4. RIGHT SIDEBAR --}}
            <div class="studio-sidebar-right" id="right-sidebar">
                <div class="preview-header-pro">
                    <span class="small fw-bold text-white-50 uppercase tracking-widest">LIVE PREVIEW</span>
                    <div class="pulse-indicator">ACTIVE</div>
                </div>
                <div class="p-3 border-bottom border-secondary" style="background: #1e293b; border-color: #334155 !important;">
                    <label class="small fw-bold text-white-50 d-block mb-1" style="font-size: 8px; letter-spacing: 0.5px;">PILIH SANTRI CONTOH (PREVIEW)</label>
                    <select id="preview-santri-select" class="form-select form-select-sm bg-dark text-white border-secondary" style="font-size: 11px;">
                        @foreach($sampleSantris ?? [] as $s)
                            <option value="{{ $s->id }}">{{ $s->nama_lengkap }} ({{ $s->nis }})</option>
                        @endforeach
                        @if(empty($sampleSantris) || count($sampleSantris) == 0)
                            <option value="">Tidak ada data santri</option>
                        @endif
                    </select>
                </div>
                <div class="preview-viewport">
                    <div id="mini-map-render"></div>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
    /* RESET & CORE LAYOUT */
    .studio-wrapper { position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999; background: #cbd5e1; display: flex; flex-direction: column; overflow: hidden; font-family: 'Inter', sans-serif; }
    .studio-header { height: 75px; background: white; border-bottom: 1px solid #cbd5e1; flex-shrink: 0; }
    .studio-body { flex: 1; display: flex; overflow: hidden; }

    /* SIDEBARS */
    .studio-sidebar-left { width: 300px; background: white; border-right: 1px solid #cbd5e1; display: flex; flex-direction: column; flex-shrink: 0; z-index: 10; }
    .studio-sidebar-right { width: 320px; background: #0f172a; border-left: 1px solid #1e293b; display: flex; flex-direction: column; flex-shrink: 0; }
    .tabs-header { display: flex; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
    .tab-btn { flex: 1; padding: 12px; border: none; background: none; color: #94a3b8; display: flex; flex-direction: column; align-items: center; cursor: pointer; transition: 0.3s; }
    .tab-btn.active { color: #2563eb; border-bottom: 2px solid #2563eb; background: white; }
    .tab-btn span { font-size: 9px; font-weight: 800; text-transform: uppercase; margin-top: 4px; }
    .content-pane { display: none; }
    .content-pane.active { display: block; }
    .section-label { font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; margin: 15px 0 10px 0; display: block; border-left: 3px solid #2563eb; padding-left: 8px; }

    /* ELEMENTS & VARS */
    .element-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .el-item { background: #f8fafc; border: 1.5px dashed #e2e8f0; border-radius: 10px; padding: 12px 5px; text-align: center; cursor: pointer; transition: 0.2s; }
    .el-item:hover { border-color: #2563eb; color: #2563eb; background: #eff6ff; transform: translateY(-2px); }
    .el-item i { display: block; margin-bottom: 4px; font-size: 1.1rem; }
    .el-item span { font-size: 9px; font-weight: 700; text-transform: uppercase; }
    .var-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: white; border: 1px solid #f1f5f9; border-radius: 8px; cursor: pointer; margin-bottom: 6px; transition: 0.2s; }
    .var-item:hover { border-color: #2563eb; background: #f0f9ff; }
    .var-label { font-size: 11px; font-weight: 600; color: #475569; }

    /* CANVAS & EDITOR */
    .studio-canvas { flex: 1; overflow-y: auto; padding: 40px; display: flex; flex-direction: column; align-items: center; }
    .paper-sheet { width: 210mm; min-height: 297mm; background: white; border-radius: 2px; transition: width 0.3s ease, min-height 0.3s ease; }
    .input-transparent-title { border:none; font-weight:800; font-size: 1.25rem; outline:none; width: 400px; color: #1e293b; }

    /* PREVIEW ENGINE */
    .preview-header-pro { padding: 15px; background: #1e293b; color: white; display: flex; justify-content: space-between; align-items: center; }
    .preview-viewport { flex: 1; overflow-y: auto; padding: 20px; background: #020617; display: flex; flex-direction: column; align-items: center; }
    #mini-map-render { background: white; width: 210mm; height: 297mm; transform-origin: top center; transform: scale(0.24); margin-bottom: -225mm; padding: 15mm; color: black; pointer-events: none; }

    /* SUMMERNOTE OVERRIDES */
    .note-editor.note-frame { border: none !important; }
    .note-editable { font-family: 'Times New Roman', serif; font-size: 16px; line-height: 1.3; }
    .note-editable p { margin-bottom: 0 !important; }
    .note-editable table { border: 1px dashed #eee !important; margin-bottom: 10px; }
    .note-toolbar { position: sticky !important; top: 0; z-index: 100; display: flex; justify-content: center; padding: 10px !important; background: #f8fafc !important; }

    /* ZEN MODE */
    .zen-mode .studio-sidebar-left { margin-left: -300px; }
    .zen-mode .studio-sidebar-right { margin-right: -320px; }
    .upload-btn-wrapper { border: 2px dashed #cbd5e1; border-radius: 8px; padding: 10px; text-align: center; cursor: pointer; color: #64748b; font-size: 9px; font-weight: 800; }
    
    .workflow-step-mini { background: white; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; position: relative; }
    .workflow-step-mini label { font-size: 9px; margin-bottom: 2px; display: block; font-weight: bold; }
    .btn-delete-asset:hover { background: #dc3545 !important; }
</style>

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    const flatData = @json(collect($variables)->collapse());

    // 1. INIT SUMMERNOTE
    $('#suratEditor').summernote({
        height: '1000px',
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'fontsize', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['picture', 'hr']],
            ['view', ['codeview']]
        ],
        callbacks: {
            onChange: function(contents) { renderPreview(contents); }
        }
    });

    if($('#suratEditor').summernote('isEmpty')) {
        insertDefaultTemplate();
    }

    // 2. AJAX UPLOAD
    $('#image-upload-input').on('change', function() {
        let formData = new FormData();
        formData.append("image", $(this)[0].files[0]);
        formData.append("_token", "{{ csrf_token() }}");
        $('#upload-text').text('UPLOADING...');

        $.ajax({
            url: "{{ route('tenant.template-perizinan.upload-image') }}",
            type: "POST", data: formData, contentType: false, processData: false,
            success: function(res) {
                if(res.success) {
                    $('#image-gallery').prepend(`
                        <div class="el-item position-relative" onclick="insertImg('${res.url}')" style="padding:4px;" id="asset-card-${res.id}">
                            <img src="${res.url}" style="width:100%; height:45px; object-fit:contain;">
                            <button type="button" class="btn-delete-asset position-absolute" data-id="${res.id}" 
                                    style="top:0; right:0; border:none; background:rgba(220,53,69,0.8); color:white; border-radius:50%; width:16px; height:16px; font-size:10px; display:flex; align-items:center; justify-content:center; padding:0; line-height:1;">×</button>
                        </div>`);
                    $('#upload-text').text('SUCCESS');
                }
            },
            complete: function() { setTimeout(() => $('#upload-text').text('UPLOAD ASSET'), 2000); }
        });
    });

    // 3. AJAX DELETE ASSET
    $(document).on('click', '.btn-delete-asset', function(e) {
        e.stopPropagation();
        const id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Asset?',
            text: 'Asset gambar ini akan dihapus dari pondok.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/dashboard/perizinan/template-perizinan/delete-asset/${id}`,
                    type: 'DELETE',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(res) {
                        if (res.success) {
                            $(`#asset-card-${id}`).remove();
                        }
                    }
                });
            }
        });
    });

    // 4. PREVIEW ENGINE
    function renderPreview(content) {
        const layout = parseInt($('#layout_print').val()) || 1;
        let html = content;
        for (let key in flatData) {
            let regex = new RegExp(key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), "g");
            html = html.replace(regex, `<b style="color:#2563eb;">${flatData[key]}</b>`);
        }

        let gridHtml = `<div style="display:grid; grid-template-columns:${layout == 4 ? '1fr 1fr' : '1fr'}; grid-template-rows:${layout >= 2 ? '1fr 1fr' : '1fr'}; height:100%;">`;
        for (let i = 0; i < layout; i++) {
            gridHtml += `<div style="border:0.5px dashed #ccc; padding:10px; overflow:hidden;"><div style="zoom: ${layout == 4 ? '0.5' : (layout == 2 ? '0.7' : '1')}">${html || ''}</div></div>`;
        }
        gridHtml += `</div>`;
        $('#mini-map-render').html(gridHtml);
    }

    // 5. UI LOGIC
    $('.tab-btn').click(function() {
        $('.tab-btn').removeClass('active'); $(this).addClass('active');
        $('.content-pane').removeClass('active'); $('#' + $(this).data('tab')).addClass('active');
    });

    // Cari Data Tag / Variabel
    $('#searchVar').on('input', function() {
        const query = $(this).val().toLowerCase().trim();
        $('.var-item').each(function() {
            const searchVal = $(this).data('search') || '';
            const label = $(this).find('.var-label').text().toLowerCase();
            if (searchVal.includes(query) || label.includes(query)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        
        // Sembunyikan judul kategori jika semua anak tersembunyi
        $('.var-group').each(function() {
            const visibleChildren = $(this).find('.var-item:visible').length;
            if (visibleChildren === 0) {
                $(this).find('.var-group-title').hide();
            } else {
                $(this).find('.var-group-title').show();
            }
        });
    });

    // Insert tag pada posisi kursor di Summernote
    $('.var-btn').click(function() {
        const val = $(this).data('var');
        $('#suratEditor').summernote('focus');
        $('#suratEditor').summernote('insertText', val);
    });

    // Menyesuaikan ukuran kertas kanvas secara dinamis
    function updatePaperSize() {
        const layout = $('#layout_print').val();
        const paper = $('.paper-sheet');
        if (layout == '1') {
            paper.css({ 'width': '210mm', 'min-height': '297mm' });
        } else if (layout == '2') {
            paper.css({ 'width': '210mm', 'min-height': '148mm' });
        } else if (layout == '4') {
            paper.css({ 'width': '105mm', 'min-height': '148mm' });
        }
    }

        $('#layout_print').change(function() { 
            renderPreview($('#suratEditor').val()); 
            updatePaperSize();
        });

        // Dropdown Santri Contoh untuk Preview
        $('#preview-santri-select').change(function() {
            const id = $(this).val();
            if (!id) return;
            $.get(`{{ url('/dashboard/perizinan/santri-data') }}/${id}`, function(data) {
                flatData['{nama_santri}'] = data.nama_lengkap;
                flatData['{nis}'] = data.nis;
                flatData['{status_santri}'] = data.status || 'aktif';
                flatData['{nama_kamar}'] = (data.kamar ? data.kamar.nama : 'Kamar Belum Diatur');
                renderPreview($('#suratEditor').val());
            });
        });
        
        // Logika Styling Dokumen
        function applyDocumentStyles() {
            const fontFamily = $('#doc-font-family').val();
            const lineHeight = $('#doc-line-height').val();
            const hasBorder = $('#doc-border-frame').is(':checked');

            $('#style-font-family').val(fontFamily);
            $('#style-line-height').val(lineHeight);
            $('#style-border-frame').val(hasBorder ? '1' : '0');

            const editable = $('.note-editable');
            if (editable.length) {
                editable.css({
                    'font-family': fontFamily,
                    'line-height': lineHeight
                });

                if (hasBorder) {
                     editable.css({
                         'border': '10px double #000',
                         'padding': '15px'
                     });
                } else {
                     editable.css({
                         'border': 'none',
                         'padding': '0'
                     });
                }
            }
        }

        // Set default dropdown values
        $('#doc-font-family').val($('#style-font-family').val());
        $('#doc-line-height').val($('#style-line-height').val());
        $('#doc-border-frame').prop('checked', $('#style-border-frame').val() === '1');

        $('#doc-font-family, #doc-line-height, #doc-border-frame').change(applyDocumentStyles);

        // -------------------------------------------------------------
        // LOGIKA WIZARD MODE
        // -------------------------------------------------------------
        function toggleTtdFields() {
            const layout = $('#wz-ttd-layout').val();
            if (layout === 'kanan') {
                $('#wz-ttd-kanan-fields').show();
                $('#wz-ttd-kiri-fields').hide();
            } else if (layout === 'dua') {
                $('#wz-ttd-kanan-fields').show();
                $('#wz-ttd-kiri-fields').show();
            } else {
                $('#wz-ttd-kanan-fields').hide();
                $('#wz-ttd-kiri-fields').hide();
            }
        }

        $('#wz-use-kop').change(function() {
            if ($(this).is(':checked')) {
                $('#wz-kop-fields').slideDown();
            } else {
                $('#wz-kop-fields').slideUp();
            }
        });

        $('#wz-ttd-layout').change(toggleTtdFields);

        function compileWizardToHtml() {
            if ($('#design-mode').val() !== 'wizard') return;

            let html = '';

            // 1. KOP SURAT
            if ($('#wz-use-kop').is(':checked')) {
                const yayasan = $('#wz-kop-yayasan').val().toUpperCase();
                const pondok = $('#wz-kop-pondok').val().toUpperCase();
                const alamat = $('#wz-kop-alamat').val();
                const kontak = $('#wz-kop-kontak').val();
                const logo = $('#wz-kop-logo').val() || 'https://via.placeholder.com/80';
                const useDoubleLine = $('#wz-kop-double-line').is(':checked');
                const borderStyle = useDoubleLine ? 'border-bottom: 3px double #000;' : 'border-bottom: 2px solid #000;';

                html += `
<table style="width: 100%; ${borderStyle} margin-bottom: 20px;">
    <tr>
        <td style="width: 80px; padding: 10px; vertical-align: middle;">
            <img src="${logo}" style="width: 70px;">
        </td>
        <td style="text-align: center; padding-right: 80px; vertical-align: middle;">
            <h3 style="margin: 0; font-size: 13px; font-weight: normal; color: #555;">${yayasan}</h3>
            <h2 style="margin: 0; font-size: 16px; font-weight: bold; color: #000;">${pondok}</h2>
            <p style="margin: 3px 0 0 0; font-size: 10px; color: #333;">${alamat}</p>
            <p style="margin: 1px 0 0 0; font-size: 9px; color: #444;">${kontak}</p>
        </td>
    </tr>
</table><p><br></p>
                `;
            }

            // 2. JUDUL & PEMBUKA
            const judul = $('#wz-judul').val().trim();
            const pembuka = $('#wz-pembuka').val().trim();
            if (judul) {
                html += `<p style="text-align: center; margin-bottom: 15px; font-size: 14px;"><b><u>${judul.toUpperCase()}</u></b></p>`;
            }
            if (pembuka) {
                html += `<p style="margin-bottom: 15px;">${pembuka}</p>`;
            }

            // 3. TABEL IDENTITAS
            let hasIdentitas = false;
            let identitasHtml = `<table style="width: 100%; margin-left: 20px; line-height: 1.5; margin-bottom: 15px;">`;
            if ($('#wz-show-nama').is(':checked')) {
                identitasHtml += `<tr><td style="width: 130px;">Nama Santri</td><td style="width: 10px;">:</td><td><b>{nama_santri}</b></td></tr>`;
                hasIdentitas = true;
            }
            if ($('#wz-show-nis').is(':checked')) {
                identitasHtml += `<tr><td>Nomor Induk (NIS)</td><td>:</td><td>{nis}</td></tr>`;
                hasIdentitas = true;
            }
            if ($('#wz-show-kamar').is(':checked')) {
                identitasHtml += `<tr><td>Kamar Santri</td><td>:</td><td>{nama_kamar}</td></tr>`;
                hasIdentitas = true;
            }
            if ($('#wz-show-status').is(':checked')) {
                identitasHtml += `<tr><td>Status Santri</td><td>:</td><td>{status_santri}</td></tr>`;
                hasIdentitas = true;
            }
            identitasHtml += `</table><p><br></p>`;
            if (hasIdentitas) {
                html += identitasHtml;
            }

            // 4. KETENTUAN, BERLAKU & PENUTUP
            const ketentuan = $('#wz-ketentuan').val().trim();
            const berlaku = $('#wz-berlaku').val().trim();
            const penutup = $('#wz-penutup').val().trim();

            if (ketentuan) {
                html += `<p style="margin-bottom: 10px;">${ketentuan}</p>`;
            }
            if (berlaku) {
                html += `<p style="margin-bottom: 15px;">${berlaku}</p>`;
            }
            if (penutup) {
                html += `<p style="margin-bottom: 20px;">${penutup}</p>`;
            }

            // 5. QR CODE
            if ($('#wz-show-qrcode').is(':checked')) {
                html += `<div style="text-align: center; margin: 15px 0;">{qr_code}<br><small style="font-size: 8px; font-family: monospace; color: #555;">{kode_surat}</small></div>`;
            }

            // 6. TANDA TANGAN LAYOUT
            const ttdLayout = $('#wz-ttd-layout').val();
            if (ttdLayout === 'kanan') {
                const roleKanan = $('#wz-ttd-kanan-role').val();
                const nameKanan = $('#wz-ttd-kanan-name').val();
                html += `
<table style="width: 100%; margin-top: 25px;">
    <tr>
        <td style="width: 60%;"></td>
        <td style="text-align: center; width: 40%;">
            <p style="margin-bottom: 55px;">Jombang, {tanggal_sekarang}<br><b>${roleKanan},</b></p>
            <p><b>( ${nameKanan} )</b></p>
        </td>
    </tr>
</table>
                `;
            } else if (ttdLayout === 'dua') {
                const roleKanan = $('#wz-ttd-kanan-role').val();
                const nameKanan = $('#wz-ttd-kanan-name').val();
                const roleKiri = $('#wz-ttd-kiri-role').val();
                const nameKiri = $('#wz-ttd-kiri-name').val();
                html += `
<table style="width: 100%; margin-top: 25px;">
    <tr>
        <td style="text-align: center; width: 50%;">
            <p style="margin-bottom: 55px;">Mengetahui,<br><b>${roleKiri}</b></p>
            <p><b>( ${nameKiri} )</b></p>
        </td>
        <td style="text-align: center; width: 50%;">
            <p style="margin-bottom: 55px;">Jombang, {tanggal_sekarang}<br><b>${roleKanan},</b></p>
            <p><b>( ${nameKanan} )</b></p>
        </td>
    </tr>
</table>
                `;
            }

            $('#suratEditor').summernote('code', html);
            renderPreview(html);
        }

        // Live compile listeners on wizard fields
        $('#wizard-pane input, #wizard-pane textarea, #wizard-pane select').on('input change', function() {
            compileWizardToHtml();
        });

        function setDesignMode(mode) {
            $('#design-mode').val(mode);
            if (mode === 'wizard') {
                // Style switcher buttons
                $('#btn-mode-wizard').removeClass('btn-light').addClass('btn-primary').css({ color: 'white' });
                $('#btn-mode-manual').removeClass('btn-primary').addClass('btn-light').css({ color: '#64748b' });

                // Panels visibility
                $('.tabs-header').hide();
                $('.tab-content-wrapper').hide();
                $('#wizard-pane').show();

                // Summernote status
                $('#wizard-overlay').show();
                $('#suratEditor').summernote('disable');

                // Initial compilation
                compileWizardToHtml();
            } else {
                // Style switcher buttons
                $('#btn-mode-manual').removeClass('btn-light').addClass('btn-primary').css({ color: 'white' });
                $('#btn-mode-wizard').removeClass('btn-primary').addClass('btn-light').css({ color: '#64748b' });

                // Panels visibility
                $('.tabs-header').show();
                $('.tab-content-wrapper').show();
                $('#wizard-pane').hide();

                // Summernote status
                $('#wizard-overlay').hide();
                $('#suratEditor').summernote('enable');
            }
        }

        $('#btn-mode-wizard').on('click', function() {
            if ($('#design-mode').val() === 'wizard') return;

            Swal.fire({
                title: 'Aktifkan Mode Wizard?',
                text: 'Mengaktifkan kembali mode Wizard akan menimpa seluruh perubahan manual yang telah Anda ketik. Lanjutkan?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Aktifkan Wizard',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    setDesignMode('wizard');
                }
            });
        });

        $('#btn-mode-manual').on('click', function() {
            if ($('#design-mode').val() === 'manual') return;
            setDesignMode('manual');
        });

        // Initialize default UI mode based on stored value
        const initialMode = $('#design-mode').val();
        toggleTtdFields();
        setDesignMode(initialMode);

        // Jalankan pertama kali
        updatePaperSize();
        renderPreview($('#suratEditor').val());
        setTimeout(applyDocumentStyles, 500);

    // Toggle late penalty
    $('#rule_late_penalty_enabled').on('change', function() {
        if ($(this).is(':checked')) {
            $('#late-penalty-settings').slideDown().css('display', 'flex');
        } else {
            $('#late-penalty-settings').slideUp();
        }
    });

    // Validasi Pre-flight untuk QR Code
    $('#main-form').on('submit', function(e) {
        const content = $('#suratEditor').val();
        if (!content.includes('{qr_code}')) {
            e.preventDefault();
            Swal.fire({
                title: 'QR Code Belum Ditambahkan',
                text: 'Anda belum menyertakan tag {qr_code} di template. Sangat disarankan menyertakan QR Code agar surat izin dapat diverifikasi instan saat santri kembali. Tetap simpan?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Tetap Simpan',
                cancelButtonText: 'Batal, Tambahkan QR'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Unbind dan submit langsung untuk menghindari loop submit
                    $('#main-form').off('submit').submit();
                }
            });
        }
    });

    // 6. WORKFLOW STEPS DESIGNER
    const roles = @json($roles);
    let stepCount = 0;

    function addWorkflowStep(name = '', roleName = '') {
        stepCount++;
        let optionsHtml = '';
        roles.forEach(role => {
            const selected = role.name === roleName ? 'selected' : '';
            optionsHtml += `<option value="${role.name}" ${selected}>${role.name.replace('_', ' ').toUpperCase()}</option>`;
        });

        const stepHtml = `
            <div class="workflow-step-mini d-flex flex-column gap-1 mb-2" id="step-item-${stepCount}">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge badge-info py-0 px-2 fw-bold" style="font-size: 8px;">Pos ${stepCount}</span>
                    <button type="button" class="btn btn-link text-danger p-0 btn-remove-step" data-id="${stepCount}" style="font-size:10px;">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
                <div class="p-0">
                    <label>Nama Pos</label>
                    <input type="text" name="rules[approval_workflow][${stepCount}][name]" class="form-control form-control-sm py-0 px-1" style="font-size: 10px; height: auto;" placeholder="Persetujuan Asrama" value="${name}" required>
                </div>
                <div class="p-0">
                    <label>Otoritas Role</label>
                    <select name="rules[approval_workflow][${stepCount}][required_role]" class="form-select form-select-sm py-0 px-1" style="font-size: 10px; height: auto;" required>
                        <option value="">Pilih...</option>
                        ${optionsHtml}
                    </select>
                </div>
                <input type="hidden" name="rules[approval_workflow][${stepCount}][step]" value="${stepCount}">
            </div>
        `;
        $('#workflow-steps-container').append(stepHtml);
        reindexSteps();
    }

    $('#btn-add-step').on('click', function() {
        addWorkflowStep();
    });

    $(document).on('click', '.btn-remove-step', function() {
        const id = $(this).data('id');
        $(`#step-item-${id}`).remove();
        reindexSteps();
    });

    function reindexSteps() {
        let index = 1;
        $('.workflow-step-mini').each(function() {
            $(this).find('.badge-info').text(`Pos ${index}`);
            $(this).find('input[type="hidden"]').val(index);
            
            $(this).find('input[type="text"]').attr('name', `rules[approval_workflow][${index}][name]`);
            $(this).find('select').attr('name', `rules[approval_workflow][${index}][required_role]`);
            $(this).find('input[type="hidden"]').attr('name', `rules[approval_workflow][${index}][step]`);
            
            index++;
        });
        stepCount = index - 1;
    }

    // Prefill steps on edit mode
    @if(isset($template) && !empty($template->rules['approval_workflow']))
        @foreach($template->rules['approval_workflow'] as $step)
            addWorkflowStep("{{ $step['name'] }}", "{{ $step['required_role'] }}");
        @endforeach
    @endif
});

function insertQrCode() {
    $('#suratEditor').summernote('insertHTML', '<div style="text-align: center; margin: 15px 0;">{qr_code}<br><small style="font-size: 9px; font-family: monospace;">{kode_surat}</small></div>');
}

function loadPreset(type) {
    Swal.fire({
        title: 'Muat Preset?',
        text: 'Memuat preset akan menggantikan seluruh teks surat yang sedang Anda edit saat ini.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Gantikan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#suratEditor').summernote('code', '');
            if (type === 'formal') {
                insertKop();
                $('#suratEditor').summernote('insertHTML', `
                    <p style="text-align: center; margin-bottom: 20px;"><b><u>SURAT IZIN KELUAR RESMI</u></b></p>
                    <p>Yang bertanda tangan di bawah ini menerangkan bahwa santri berikut:</p>
                `);
                insertIdentitas();
                $('#suratEditor').summernote('insertHTML', `
                    <p style="margin-top: 15px;">Telah diberikan izin untuk keluar lingkungan pondok pesantren dengan alasan keperluan: <b>{keperluan}</b>.</p>
                    <p>Surat izin ini berlaku sejak tanggal <b>{tgl_keluar}</b> sampai batas kembali tanggal <b>{batas_kembali}</b>.</p>
                    <p>Demikian surat izin ini dibuat untuk dipergunakan sebagaimana mestinya. Harap kembali tepat waktu sesuai batas kembali.</p>
                `);
                insertQrCode();
                insertTandaTangan('dua');
            } else if (type === 'belanja') {
                $('#suratEditor').summernote('insertHTML', `
                    <div style="text-align: center; font-size: 14px; font-weight: bold; border-bottom: 1px dashed #000; padding-bottom: 5px; margin-bottom: 10px;">
                        IZIN KELUAR SINGKAT (BELANJA)
                    </div>
                    <table style="width: 100%; font-size: 11px; margin-bottom: 10px;">
                        <tr><td style="width: 70px;">Santri</td><td>: <b>{nama_santri}</b> ({nis})</td></tr>
                        <tr><td>Keluar</td><td>: {tgl_keluar}</td></tr>
                        <tr><td>Kembali</td><td>: <b>{batas_kembali}</b></td></tr>
                    </table>
                `);
                insertQrCode();
                $('#suratEditor').summernote('insertHTML', `
                    <div style="text-align: center; font-size: 9px; margin-top: 10px; border-top: 1px dashed #000; padding-top: 5px;">
                        Harap bawa struk ini saat kembali untuk dipindai oleh Keamanan.
                    </div>
                `);
            } else if (type === 'sakit') {
                insertKop();
                $('#suratEditor').summernote('insertHTML', `
                    <p style="text-align: center; margin-bottom: 20px;"><b><u>SURAT KETERANGAN SAKIT</u></b></p>
                    <p>Menerangkan bahwa santri di bawah ini:</p>
                `);
                insertIdentitas();
                $('#suratEditor').summernote('insertHTML', `
                    <p style="margin-top: 15px;">Sedang dalam kondisi sakit/kurang sehat dan memerlukan perawatan medis di luar lingkungan pondok pesantren.</p>
                    <p>Surat izin pemulangan/istirahat sakit ini diberikan mulai tanggal <b>{tgl_keluar}</b> sampai batas kembali tanggal <b>{batas_kembali}</b>.</p>
                    <p>Demikian surat keterangan ini diberikan agar dapat dimaklumi dan dipergunakan dengan semestinya.</p>
                `);
                insertQrCode();
                insertTandaTangan('kanan');
            }
        }
    });
}

// FUNCTIONS UNTUK INSERT STRUKTUR "GHAIB"
function insertKop() {
    const kop = `
        <table style="width: 100%; border-bottom: 3px double #000; margin-bottom: 20px;">
            <tr>
                <td style="width: 80px; padding: 10px;">
                    <img src="https://via.placeholder.com/80" style="width: 70px;">
                </td>
                <td style="text-align: center; padding-right: 80px;">
                    <h2 style="margin: 0; font-size: 18px; font-weight: bold;">YAYASAN PONDOK PESANTREN AL-FITROH</h2>
                    <p style="margin: 0; font-size: 12px;">Jl. Raya Jombang No. 123, Kabupaten Jombang, Jawa Timur</p>
                    <p style="margin: 0; font-size: 11px;">Telp: 0812-3456-789 | Email: info@alfitroh.com</p>
                </td>
            </tr>
        </table><p><br></p>`;
    $('#suratEditor').summernote('insertHTML', kop);
}

function insertIdentitas() {
    const table = `
        <table style="width: 100%; margin-left: 20px; line-height: 1.5;">
            <tr><td style="width: 130px;">Nama Santri</td><td style="width: 10px;">:</td><td><b>{nama_santri}</b></td></tr>
            <tr><td>NIS / Kamar</td><td>:</td><td>{nis} / {nama_kamar}</td></tr>
            <tr><td>Keperluan</td><td>:</td><td>_______________________</td></tr>
        </table><p><br></p>`;
    $('#suratEditor').summernote('insertHTML', table);
}

function insertTandaTangan(type) {
    let content = '';
    if(type === 'kanan') {
        content = `
            <table style="width: 100%; margin-top: 30px;">
                <tr><td style="width: 60%;"></td>
                    <td style="text-align: center; width: 40%;">
                        <p style="margin-bottom: 60px;">Jombang, {tanggal_sekarang}<br><b>Pengasuh,</b></p>
                        <p><b>( ____________________ )</b></p>
                    </td>
                </tr>
            </table>`;
    } else {
        content = `
            <table style="width: 100%; margin-top: 30px;">
                <tr>
                    <td style="text-align: center; width: 50%;">
                        <p style="margin-bottom: 60px;">Mengetahui,<br><b>Wali Santri</b></p>
                        <p><b>( ____________________ )</b></p>
                    </td>
                    <td style="text-align: center; width: 50%;">
                        <p style="margin-bottom: 60px;">Jombang, {tanggal_sekarang}<br><b>Keamanan,</b></p>
                        <p><b>( ____________________ )</b></p>
                    </td>
                </tr>
            </table>`;
    }
    $('#suratEditor').summernote('insertHTML', content);
}

function insertGaris() { $('#suratEditor').summernote('insertHTML', '<hr style="border-top: 2px solid #000; margin: 10px 0;">'); }
function insertImg(url) { $('#suratEditor').summernote('insertImage', url); }
function toggleZenMode() { $('body').toggleClass('zen-mode'); }

function insertDefaultTemplate() {
    insertKop();
    $('#suratEditor').summernote('insertHTML', '<p style="text-align:center;"><b><u>SURAT IZIN KELUAR</u></b></p><p>Yang bertanda tangan di bawah ini memberikan izin kepada:</p>');
    insertIdentitas();
    $('#suratEditor').summernote('insertHTML', '<p>Demikian surat izin ini dibuat untuk dipergunakan sebagaimana mestinya.</p>');
    insertTandaTangan('kanan');
}

function generateKopSurat() {
    const yayasan = $('#kop-yayasan').val().toUpperCase();
    const pondok = $('#kop-pondok').val().toUpperCase();
    const alamat = $('#kop-alamat').val();
    const kontak = $('#kop-kontak').val();
    const logo = $('#kop-logo').val() || 'https://via.placeholder.com/80';
    const useDoubleLine = $('#kop-double-line').is(':checked');

    const borderStyle = useDoubleLine ? 'border-bottom: 3px double #000;' : 'border-bottom: 2px solid #000;';

    const kopHtml = `
        <table style="width: 100%; ${borderStyle} margin-bottom: 20px;">
            <tr>
                <td style="width: 80px; padding: 10px; vertical-align: middle;">
                    <img src="${logo}" style="width: 70px;">
                </td>
                <td style="text-align: center; padding-right: 80px; vertical-align: middle;">
                    <h3 style="margin: 0; font-size: 14px; font-weight: normal; color: #555;">${yayasan}</h3>
                    <h2 style="margin: 0; font-size: 18px; font-weight: bold; color: #000;">${pondok}</h2>
                    <p style="margin: 3px 0 0 0; font-size: 11px; color: #333;">${alamat}</p>
                    <p style="margin: 1px 0 0 0; font-size: 10px; color: #444;">${kontak}</p>
                </td>
            </tr>
        </table><p><br></p>
    `;

    $('#suratEditor').summernote('focus');
    $('#suratEditor').summernote('insertHTML', kopHtml);
    bootstrap.Modal.getInstance(document.getElementById('kopBuilderModal')).hide();
    Swal.fire('Sukses', 'Kop Surat berhasil ditambahkan!', 'success');
}
</script>
@endpush

<!-- Kop Surat Builder Modal -->
<div class="modal fade" id="kopBuilderModal" tabindex="-1" aria-hidden="true" style="z-index: 100000;">
    <div class="modal-dialog">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-heading text-primary me-2"></i>Kop Surat Builder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start">
                <div class="mb-3">
                    <label class="small fw-bold">Nama Yayasan / Lembaga</label>
                    <input type="text" id="kop-yayasan" class="form-control form-control-sm" value="YAYASAN PONDOK PESANTREN AL-FITROH">
                </div>
                <div class="mb-3">
                    <label class="small fw-bold">Nama Sub-Lembaga / Pondok</label>
                    <input type="text" id="kop-pondok" class="form-control form-control-sm" value="PONDOK PESANTREN AL-FITROH KITA">
                </div>
                <div class="mb-3">
                    <label class="small fw-bold">Alamat Lengkap</label>
                    <input type="text" id="kop-alamat" class="form-control form-control-sm" value="Jl. Raya Jombang No. 123, Jombang, Jawa Timur">
                </div>
                <div class="mb-3">
                    <label class="small fw-bold">Kontak (Telp / Email)</label>
                    <input type="text" id="kop-kontak" class="form-control form-control-sm" value="Telp: 0812-3456-789 | Email: info@alfitroh.com">
                </div>
                <div class="mb-3">
                    <label class="small fw-bold">URL Logo Kiri</label>
                    <input type="text" id="kop-logo" class="form-control form-control-sm" value="https://via.placeholder.com/80">
                </div>
                <div class="form-check p-0 m-0 d-flex align-items-center">
                    <input class="form-check-input ms-0 me-2" type="checkbox" id="kop-double-line" checked style="width: 14px; height: 14px;">
                    <label class="small fw-bold text-muted mb-0" for="kop-double-line" style="cursor: pointer;">Gunakan Garis Pembatas Ganda</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-round btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-round btn-sm" onclick="generateKopSurat()">
                    <i class="fas fa-check me-1"></i>Masukkan ke Surat
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
