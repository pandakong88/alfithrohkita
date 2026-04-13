@extends('layouts.tenant')

@section('content')
<form id="main-form" method="POST" action="{{ isset($template) ? route('tenant.template-perizinan.update', $template->id) : route('tenant.template-perizinan.store') }}">
    @csrf
    @if(isset($template)) @method('PUT') @endif

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
                <div class="tabs-header">
                    <button type="button" class="tab-btn active" data-tab="elements"><i class="fas fa-th-large mb-1"></i><span>Layout</span></button>
                    <button type="button" class="tab-btn" data-tab="variables"><i class="fas fa-database mb-1"></i><span>Data Tags</span></button>
                </div>
                
                <div class="tab-content-wrapper p-3">
                    {{-- Tab Elements --}}
                    <div id="elements" class="content-pane active">
                        <label class="section-label">Struktur Utama</label>
                        <div class="element-grid mb-3">
                            <div class="el-item" onclick="insertKop()"><i class="fas fa-window-maximize"></i><span>Kop Surat</span></div>
                            <div class="el-item" onclick="insertIdentitas()"><i class="fas fa-list-ul"></i><span>Identitas</span></div>
                            <div class="el-item" onclick="insertGaris()"><i class="fas fa-minus"></i><span>Garis Tebal</span></div>
                        </div>

                        <label class="section-label">Tanda Tangan</label>
                        <div class="element-grid mb-3">
                            <div class="el-item" onclick="insertTandaTangan('kanan')"><i class="fas fa-file-signature"></i><span>TTD Kanan</span></div>
                            <div class="el-item" onclick="insertTandaTangan('dua')"><i class="fas fa-users"></i><span>TTD 2 Org</span></div>
                        </div>

                        <label class="section-label">Gambar/Stempel</label>
                        <div class="upload-btn-wrapper mb-2" onclick="document.getElementById('image-upload-input').click()">
                            <i class="fas fa-upload mb-1"></i>
                            <span id="upload-text">UPLOAD ASSET</span>
                            <input type="file" id="image-upload-input" class="d-none" accept="image/*">
                        </div>
                        <div id="image-gallery" class="element-grid"></div>
                    </div>
                    
                    {{-- Tab Variables --}}
                    <div id="variables" class="content-pane">
                        <input type="text" id="searchVar" class="form-control form-control-sm rounded-pill mb-3" placeholder="Cari data santri...">
                        <div class="variable-scroll">
                            @foreach($variables as $cat => $items)
                                <div class="var-group">
                                    <div class="var-group-title">{{ strtoupper($cat) }}</div>
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
                </div>
            </div>

            {{-- 3. CENTER: THE CANVAS --}}
            <div class="studio-canvas" id="canvas-area">
                <div class="paper-sheet shadow-2xl">
                    <textarea name="format_surat" id="suratEditor">{{ old('format_surat', $template->format_surat ?? '') }}</textarea>
                </div>
            </div>

            {{-- 4. RIGHT SIDEBAR --}}
            <div class="studio-sidebar-right" id="right-sidebar">
                <div class="preview-header-pro">
                    <span class="small fw-bold text-white-50 uppercase tracking-widest">LIVE PREVIEW</span>
                    <div class="pulse-indicator">ACTIVE</div>
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
    .variable-scroll { height: calc(100vh - 280px); overflow-y: auto; }

    /* CANVAS & EDITOR */
    .studio-canvas { flex: 1; overflow-y: auto; padding: 40px; display: flex; flex-direction: column; align-items: center; }
    .paper-sheet { width: 210mm; min-height: 297mm; background: white; border-radius: 2px; }
    .input-transparent-title { border:none; font-weight:800; font-size: 1.25rem; outline:none; width: 400px; color: #1e293b; }

    /* PREVIEW ENGINE */
    .preview-header-pro { padding: 15px; background: #1e293b; color: white; display: flex; justify-content: space-between; align-items: center; }
    .preview-viewport { flex: 1; overflow-y: auto; padding: 20px; background: #020617; display: flex; flex-direction: column; align-items: center; }
    #mini-map-render { background: white; width: 210mm; height: 297mm; transform-origin: top center; transform: scale(0.24); margin-bottom: -225mm; padding: 15mm; color: black; pointer-events: none; }

    /* SUMMERNOTE OVERRIDES - PENTING UNTUK KERAPIHAN */
    .note-editor.note-frame { border: none !important; }
    .note-editable { font-family: 'Times New Roman', serif; font-size: 16px; line-height: 1.3; }
    .note-editable p { margin-bottom: 0 !important; } /* Menghapus margin bawah paragraf agar spasi rapat */
    .note-editable table { border: 1px dashed #eee !important; margin-bottom: 10px; } /* Border ghaib saat edit */
    .note-toolbar { position: sticky !important; top: 0; z-index: 100; display: flex; justify-content: center; padding: 10px !important; background: #f8fafc !important; }

    /* ZEN MODE */
    .zen-mode .studio-sidebar-left { margin-left: -300px; }
    .zen-mode .studio-sidebar-right { margin-right: -320px; }
    .upload-btn-wrapper { border: 2px dashed #cbd5e1; border-radius: 8px; padding: 10px; text-align: center; cursor: pointer; color: #64748b; font-size: 9px; font-weight: 800; }
</style>

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

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

    // JIKA KOSONG, KASIH TEMPLATE AWAL BIAR USER NGGAK BINGUNG
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
                        <div class="el-item" onclick="insertImg('${res.url}')" style="padding:4px;">
                            <img src="${res.url}" style="width:100%; height:45px; object-fit:contain;">
                        </div>`);
                    $('#upload-text').text('SUCCESS');
                }
            },
            complete: function() { setTimeout(() => $('#upload-text').text('UPLOAD ASSET'), 2000); }
        });
    });

    // 3. PREVIEW ENGINE
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

    // 4. UI LOGIC
    $('.tab-btn').click(function() {
        $('.tab-btn').removeClass('active'); $(this).addClass('active');
        $('.content-pane').removeClass('active'); $('#' + $(this).data('tab')).addClass('active');
    });

    $('.var-btn').click(function() {
        $('#suratEditor').summernote('insertText', $(this).data('var'));
    });

    $('#layout_print').change(function() { renderPreview($('#suratEditor').val()); });
    
    renderPreview($('#suratEditor').val());
});

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
    $('#suratEditor').summernote('pasteHTML', kop);
}

function insertIdentitas() {
    const table = `
        <table style="width: 100%; margin-left: 20px; line-height: 1.5;">
            <tr><td style="width: 130px;">Nama Santri</td><td style="width: 10px;">:</td><td><b>{nama_santri}</b></td></tr>
            <tr><td>NIS / Kamar</td><td>:</td><td>{nis} / {nama_kamar}</td></tr>
            <tr><td>Keperluan</td><td>:</td><td>_______________________</td></tr>
        </table><p><br></p>`;
    $('#suratEditor').summernote('pasteHTML', table);
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
    $('#suratEditor').summernote('pasteHTML', content);
}

function insertGaris() { $('#suratEditor').summernote('pasteHTML', '<hr style="border-top: 2px solid #000; margin: 10px 0;">'); }
function insertImg(url) { $('#suratEditor').summernote('insertImage', url); }
function toggleZenMode() { $('body').toggleClass('zen-mode'); }

function insertDefaultTemplate() {
    insertKop();
    $('#suratEditor').summernote('pasteHTML', '<p style="text-align:center;"><b><u>SURAT IZIN KELUAR</u></b></p><p>Yang bertanda tangan di bawah ini memberikan izin kepada:</p>');
    insertIdentitas();
    $('#suratEditor').summernote('pasteHTML', '<p>Demikian surat izin ini dibuat untuk dipergunakan sebagaimana mestinya.</p>');
    insertTandaTangan('kanan');
}
</script>
@endpush
@endsection