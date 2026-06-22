@extends('layouts.tenant')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Edit Template PDF</h4>
        <ul class="breadcrumbs">
            <li class="nav-home"><a href="#"><i class="flaticon-home"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a>Template</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a>Edit</a></li>
        </ul>
    </div>

    <form method="POST" action="{{ route('tenant.template-perizinan.update', $template->id) }}" enctype="multipart/form-data" id="template-form">
        @csrf
        @method('PUT')
        
        <div id="coordinate-inputs-container">
            {{-- Hidden inputs koordinat variable akan ditaruh di sini --}}
        </div>

        <div class="row">
            {{-- KOLOM KIRI: PREVIEW & OVERLAY --}}
            <div class="col-lg-7">
                <div class="card card-round">
                    <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0">
                        <div class="card-title fw-bold text-dark">
                            <i class="fas fa-file-pdf me-2 text-danger"></i>
                            <span id="preview-title">Penempatan Data Tag (Drag & Drop)</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary btn-round" onclick="document.getElementById('file_pdf_input').click()">
                            <i class="fas fa-sync-alt me-1"></i> Ganti File PDF
                        </button>
                    </div>
                    <div class="card-body bg-light p-3 text-center" style="overflow-x: auto;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="alert alert-info border-0 shadow-sm text-start mb-0 py-2 px-3" style="font-size: 11px; flex: 1; margin-right: 15px;">
                                <i class="fas fa-info-circle me-1"></i> <b>Petunjuk:</b> Klik tag di kanan untuk menempelkan, lalu seret ke kolom PDF yang sesuai.
                            </div>
                            <div class="form-check form-switch bg-white border rounded px-3 py-2 shadow-sm d-flex align-items-center" style="cursor: pointer; user-select: none;">
                                <input class="form-check-input me-2 ms-0" type="checkbox" role="switch" id="show-grid-lines" style="cursor: pointer;">
                                <label class="form-check-label small fw-bold text-muted mb-0" for="show-grid-lines" style="cursor: pointer;">Garis Bantu Grid</label>
                            </div>
                        </div>

                        <div id="pdf-wrapper" style="position: relative; display: inline-block; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border-radius: 8px; overflow: hidden; background: white;">
                            <canvas id="pdf-canvas"></canvas>
                            <div id="grid-lines" class="grid-lines-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; background-size: 20px 20px; background-image: linear-gradient(to right, rgba(0,0,0,0.06) 1px, transparent 1px), linear-gradient(to bottom, rgba(0,0,0,0.06) 1px, transparent 1px); display: none; z-index: 5;"></div>
                            <div id="pdf-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: auto; z-index: 10;">
                                {{-- Element drag variables dimasukkan secara dinamis --}}
                            </div>
                        </div>

                        {{-- Hidden File Input --}}
                        <input type="file" name="file_pdf" id="file_pdf_input" class="d-none" accept="application/pdf" onchange="handleFileChange(this)">
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: FORM CONFIG --}}
            <div class="col-lg-5">
                <div class="card card-round">
                    <div class="card-header bg-white border-bottom-0">
                        <div class="card-title fw-bold text-dark">Pengaturan Template & Alur</div>
                    </div>
                    <div class="card-body">
                        {{-- NAMA --}}
                        <div class="form-group p-0 mb-3">
                            <label class="fw-bold">Nama Template</label>
                            <input type="text" name="nama" class="form-control" value="{{ old('nama', $template->nama) }}" required>
                        </div>

                        {{-- DESKRIPSI --}}
                        <div class="form-group p-0 mb-3">
                            <label class="fw-bold">Deskripsi Operasional</label>
                            <textarea name="deskripsi" class="form-control" rows="2">{{ old('deskripsi', $template->deskripsi) }}</textarea>
                        </div>

                        {{-- DEFAULT --}}
                        <div class="mb-4">
                            <div class="custom-control custom-checkbox border rounded p-3 bg-light shadow-none">
                                <input type="checkbox" class="custom-control-input" id="is_default" name="is_default" value="1" {{ $template->is_default ? 'checked' : '' }}>
                                <label class="custom-control-label fw-bold mb-0 text-primary" for="is_default" style="cursor:pointer;">
                                    <i class="fas fa-star me-1"></i> Jadikan Template Utama (Default)
                                </label>
                            </div>
                        </div>

                        {{-- LAYOUT --}}
                        <div class="form-group p-0 mb-4">
                            <label class="fw-bold mb-2">Pilih Layout Cetak</label>
                            <div class="row g-2">
                                @foreach([1 => 'A4 (Full)', 2 => 'A5 (Setengah)', 4 => 'A6 (Kecil)'] as $val => $text)
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="layout_print" id="layout{{$val}}" value="{{$val}}" {{ $template->layout_print == $val ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center" for="layout{{$val}}" style="cursor: pointer;">
                                        <i class="fas {{ $val == 1 ? 'fa-file' : ($val == 2 ? 'fa-columns' : 'fa-th-large') }} mb-1"></i>
                                        <span style="font-size: 10px">{{ $text }}</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- CLICKABLE VARIABLES FOR OVERLAY --}}
                        <div class="form-group p-0 mb-4">
                            <label class="fw-bold mb-2">Pilih & Tempel Data Tag ke PDF</label>
                            
                            {{-- Cari Data Tag --}}
                            <input type="text" id="search-var-pdf" class="form-control form-control-sm mb-3 rounded-pill" placeholder="Cari data tag...">

                            {{-- Input Variabel Kustom --}}
                            <div class="input-group input-group-sm mb-3">
                                <input type="text" id="custom-var-input" class="form-control" placeholder="Nama variabel kustom (misal: nopol_motor)">
                                <button type="button" class="btn btn-primary btn-sm" id="btn-add-custom-var">
                                    <i class="fas fa-plus me-1"></i> Tempel
                                </button>
                            </div>

                            <div class="bg-light p-3 rounded border" style="max-height: 250px; overflow-y: auto;">
                                <div class="row g-2">
                                    @foreach($variables as $var)
                                    <div class="col-6">
                                        <button type="button" class="btn btn-sm btn-white w-100 border text-start d-flex justify-content-between align-items-center var-trigger-btn"
                                                data-key="{{ $var->key }}" data-label="{{ $var->label }}">
                                            <span class="text-truncate me-1" style="font-size: 11px;">{{ $var->label }}</span>
                                            <i class="fas fa-plus text-primary" style="font-size: 10px;"></i>
                                        </button>
                                    </div>
                                    @endforeach
                                    <div class="col-6">
                                        <button type="button" class="btn btn-sm btn-white w-100 border text-start d-flex justify-content-between align-items-center var-trigger-btn"
                                                data-key="qr_code" data-label="QR Code">
                                            <span class="text-truncate me-1" style="font-size: 11px; font-weight: bold; color: #28a745;"><i class="fas fa-qrcode me-1"></i>QR Code</span>
                                            <i class="fas fa-plus text-success" style="font-size: 10px;"></i>
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button type="button" class="btn btn-sm btn-white w-100 border text-start d-flex justify-content-between align-items-center var-trigger-btn"
                                                data-key="kode_surat" data-label="Kode Surat">
                                            <span class="text-truncate me-1" style="font-size: 11px; font-weight: bold; color: #fd7e14;"><i class="fas fa-barcode me-1"></i>Kode Surat</span>
                                            <i class="fas fa-plus text-warning" style="font-size: 10px;"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- RULES SECTION: LATE PENALTY --}}
                        <label class="fw-bold mb-2"><i class="fas fa-gavel text-warning me-1"></i> Kebijakan Keterlambatan</label>
                        <div class="bg-light p-3 rounded-3 mb-4 border">
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="rule_late_penalty_enabled" name="rules[late_penalty][enabled]" value="1" {{ old('rules.late_penalty.enabled', $template->rules['late_penalty']['enabled'] ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label fw-bold mb-0 text-dark" for="rule_late_penalty_enabled" style="cursor: pointer;">
                                    Aktifkan Poin Pelanggaran Otomatis
                                </label>
                            </div>
                            <div class="row g-2 mt-2" id="late-penalty-settings" style="display: {{ old('rules.late_penalty.enabled', $template->rules['late_penalty']['enabled'] ?? false) ? 'flex' : 'none' }};">
                                <div class="col-6">
                                    <label class="small text-muted fw-bold">Setiap Terlambat (Menit)</label>
                                    <input type="number" name="rules[late_penalty][interval_minutes]" class="form-control form-control-sm" value="{{ old('rules.late_penalty.interval_minutes', $template->rules['late_penalty']['interval_minutes'] ?? 60) }}">
                                </div>
                                <div class="col-6">
                                    <label class="small text-muted fw-bold">Denda Poin Pelanggaran</label>
                                    <input type="number" name="rules[late_penalty][points_per_interval]" class="form-control form-control-sm" value="{{ old('rules.late_penalty.points_per_interval', $template->rules['late_penalty']['points_per_interval'] ?? 5) }}">
                                </div>
                            </div>
                        </div>

                        {{-- RULES SECTION: CUSTOM APPROVAL WORKFLOW --}}
                        <label class="fw-bold mb-2"><i class="fas fa-stream text-info me-1"></i> Alur Persetujuan (Approval Workflow)</label>
                        <div class="bg-light p-3 rounded-3 border">
                            <p class="text-muted small mb-3">Tentukan pos pemeriksaan/persetujuan yang harus dilewati sebelum izin aktif secara digital.</p>
                            
                            <div id="workflow-steps-container" class="d-flex flex-column gap-2 mb-3">
                                {{-- Step items generated dynamically --}}
                            </div>

                            <button type="button" class="btn btn-outline-info btn-sm btn-round w-100" id="btn-add-step">
                                <i class="fas fa-plus me-1"></i> Tambah Pos Persetujuan
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-footer pb-4 px-4 bg-transparent border-0">
                        <button type="submit" class="btn btn-primary btn-round w-100 shadow fw-bold">
                            <i class="fas fa-save me-2"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('tenant.template-perizinan.index') }}" class="btn btn-link text-muted w-100 mt-2">Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .btn-check:checked + label {
        background-color: #1572e8 !important;
        color: white !important;
        border-color: #1572e8 !important;
    }
    .custom-control-label::before, .custom-control-label::after {
        top: 0.5rem;
        left: -1.5rem;
    }
    .placed-var-badge:hover {
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        transform: scale(1.05);
    }
    /* Scrollbar */
    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-thumb { background: #dcdde1; border-radius: 10px; }
</style>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

    let pdfDoc = null;
    const overlay = document.getElementById('pdf-overlay');
    const canvas = document.getElementById('pdf-canvas');
    const ctx = canvas.getContext('2d');
    const wrapper = document.getElementById('pdf-wrapper');
    const variablesList = @json($variables);
    const roles = @json($roles);
    let stepCount = 0;

    $(document).ready(function() {
        // Toggle late penalty forms
        $('#rule_late_penalty_enabled').on('change', function() {
            if ($(this).is(':checked')) {
                $('#late-penalty-settings').slideDown().css('display', 'flex');
            } else {
                $('#late-penalty-settings').slideUp();
            }
        });

        // Toggle garis bantu grid
        $('#show-grid-lines').on('change', function() {
            if ($(this).is(':checked')) {
                $('#grid-lines').fadeIn(200);
            } else {
                $('#grid-lines').fadeOut(200);
            }
        });

        // Cari Data Tag/Variabel untuk PDF
        $('#search-var-pdf').on('input', function() {
            const query = $(this).val().toLowerCase().trim();
            $('.var-trigger-btn').each(function() {
                const key = ($(this).data('key') || '').toLowerCase();
                const label = ($(this).data('label') || '').toLowerCase();
                if (key.includes(query) || label.includes(query)) {
                    $(this).closest('.col-6').show();
                } else {
                    $(this).closest('.col-6').hide();
                }
            });
        });

        // WORKFLOW STEPS DESIGNER
        function addWorkflowStep(name = '', roleName = '') {
            stepCount++;
            let optionsHtml = '';
            roles.forEach(role => {
                const selected = role.name === roleName ? 'selected' : '';
                optionsHtml += `<option value="${role.name}" ${selected}>${role.name.replace('_', ' ').toUpperCase()}</option>`;
            });

            const stepHtml = `
                <div class="workflow-step-item bg-white p-3 border rounded shadow-sm d-flex flex-column gap-2" id="step-item-${stepCount}">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge badge-info text-white rounded-pill fw-bold">Pos ${stepCount}</span>
                        <button type="button" class="btn btn-link text-danger p-0 btn-remove-step" data-id="${stepCount}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                    <div class="form-group p-0">
                        <label class="small text-muted mb-1 fw-bold">Nama Pos Pemeriksaan</label>
                        <input type="text" name="rules[approval_workflow][${stepCount}][name]" class="form-control form-control-sm" placeholder="Contoh: Persetujuan Kamar" value="${name}" required>
                    </div>
                    <div class="form-group p-0">
                        <label class="small text-muted mb-1 fw-bold">Peran yang Berwenang (Spatie Role)</label>
                        <select name="rules[approval_workflow][${stepCount}][required_role]" class="form-select form-select-sm" required>
                            <option value="">Pilih Peran...</option>
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
            $('.workflow-step-item').each(function() {
                $(this).find('.badge-info').text(`Pos ${index}`);
                $(this).find('input[type="hidden"]').val(index);
                
                $(this).find('input[type="text"]').attr('name', `rules[approval_workflow][${index}][name]`);
                $(this).find('select').attr('name', `rules[approval_workflow][${index}][required_role]`);
                $(this).find('input[type="hidden"]').attr('name', `rules[approval_workflow][${index}][step]`);
                
                index++;
            });
            stepCount = index - 1;
        }

        // PREFILL WORKFLOW STEPS
        @if(!empty($template->rules['approval_workflow']))
            @foreach($template->rules['approval_workflow'] as $step)
                addWorkflowStep("{{ $step['name'] }}", "{{ $step['required_role'] }}");
            @endforeach
        @endif

        // PDF RENDERING LOGIC
        function loadPdf(pdfUrl) {
            pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
                pdfDoc = pdf;
                pdf.getPage(1).then(function(page) {
                    const viewport = page.getViewport({ scale: 1.2 });
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    
                    wrapper.style.width = viewport.width + 'px';
                    wrapper.style.height = viewport.height + 'px';

                    const renderContext = {
                        canvasContext: ctx,
                        viewport: viewport
                    };
                    page.render(renderContext).promise.then(function() {
                        console.log('PDF rendered successfully');
                        // Prefill existing placed variables after canvas size is set
                        prefillPlacedVariables();
                    });
                });
            });
        }

        const initialPdfUrl = "{{ asset('storage/'.$template->file_pdf) }}";
        loadPdf(initialPdfUrl);

        // Clickable variables list
        $('.var-trigger-btn').on('click', function() {
            const key = $(this).data('key');
            const label = $(this).data('label');
            spawnPlacedVariable(key, label, 10, 10);
        });

        // Tempel Custom Variable
        $('#btn-add-custom-var').on('click', function() {
            const rawKey = $('#custom-var-input').val().trim().toLowerCase().replace(/[^a-z0-9_]/g, '_');
            if (!rawKey) {
                Swal.fire('Perhatian', 'Nama variabel kustom tidak boleh kosong.', 'warning');
                return;
            }
            const key = 'custom_variables.' + rawKey;
            const label = rawKey.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
            
            spawnPlacedVariable(key, label, 10, 10);
            $('#custom-var-input').val('');
        });

        function getLabelForKey(key) {
            if (key === 'qr_code') return 'QR Code';
            if (key === 'kode_surat') return 'Kode Surat';
            if (key.startsWith('custom_variables.')) {
                return key.replace('custom_variables.', '').split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
            }
            const found = variablesList.find(v => v.key === key);
            return found ? found.label : key;
        }

        function spawnPlacedVariable(key, label, pctX, pctY) {
            if ($(`.placed-var-badge[data-key="${key}"]`).length > 0) {
                Swal.fire('Info', 'Variabel ini sudah ditempatkan di PDF.', 'info');
                return;
            }

            // Convert pct to pixels based on container dimensions
            const overlayWidth = $(overlay).width() || 500;
            const overlayHeight = $(overlay).height() || 700;
            const posX = (pctX / 100) * overlayWidth;
            const posY = (pctY / 100) * overlayHeight;

            let badgeStyle = "background: rgba(21, 114, 232, 0.9);";
            if (key === 'qr_code') {
                badgeStyle = "background: rgba(40, 167, 69, 0.9); min-width: 65px; min-height: 65px; display: flex; flex-direction: column; align-items: center; justify-content: center;";
            } else if (key === 'kode_surat') {
                badgeStyle = "background: rgba(253, 126, 20, 0.9);";
            } else if (key.startsWith('custom_variables.')) {
                badgeStyle = "background: rgba(111, 66, 193, 0.9);";
            }

            let contentHtml = `<span class="me-1">${label}</span>`;
            if (key === 'qr_code') {
                contentHtml = `<i class="fas fa-qrcode fa-lg mb-1"></i><span style="font-size: 8px;">QR CODE</span>`;
            }

            const badgeHtml = `
                <div class="placed-var-badge text-white px-2 py-1 rounded shadow-sm text-center" data-key="${key}"
                     style="position: absolute; left: ${posX}px; top: ${posY}px; cursor: move; font-size: 10px; font-weight: bold; ${badgeStyle} z-index: 100; transition: box-shadow 0.2s;">
                    ${contentHtml}
                    <button type="button" class="btn-remove-placed bg-transparent border-0 text-white p-0 position-absolute" style="font-size: 10px; font-weight: bold; top: 2px; right: 4px;">×</button>
                </div>
            `;
            $(overlay).append(badgeHtml);
            
            const badge = overlay.querySelector(`.placed-var-badge[data-key="${key}"]`);
            makeElementDraggable(badge);

            const inputHtml = `
                <div id="inputs-for-${key.replace('.', '_')}">
                    <input type="hidden" name="variables[${key}][x]" class="coord-x" id="coord-x-${key.replace('.', '_')}" value="${pctX}">
                    <input type="hidden" name="variables[${key}][y]" class="coord-y" id="coord-y-${key.replace('.', '_')}" value="${pctY}">
                </div>
            `;
            $('#coordinate-inputs-container').append(inputHtml);
        }

        // PREFILL PLACED VARIABLES FROM DATABASE
        function prefillPlacedVariables() {
            const initialVars = @json($template->required_variables ?? []);
            if (initialVars && typeof initialVars === 'object' && !Array.isArray(initialVars)) {
                for (let key in initialVars) {
                    const coords = initialVars[key];
                    const label = getLabelForKey(key);
                    spawnPlacedVariable(key, label, coords.x, coords.y);
                }
            }
        }

        // Remove variable from canvas
        $(document).on('click', '.btn-remove-placed', function(e) {
            e.stopPropagation();
            const badge = $(this).closest('.placed-var-badge');
            const key = badge.data('key');
            badge.remove();
            $(`#inputs-for-${key.replace('.', '_')}`).remove();
        });

        // Vanilla Drag Helper
        function makeElementDraggable(el) {
            let isDragging = false;
            let startX, startY, initialLeft, initialTop;
            
            el.addEventListener('mousedown', function(e) {
                isDragging = true;
                startX = e.clientX;
                startY = e.clientY;
                initialLeft = el.offsetLeft;
                initialTop = el.offsetTop;
                el.style.zIndex = 1000;
                e.preventDefault();
            });
            
            document.addEventListener('mousemove', function(e) {
                if (!isDragging) return;
                const dx = e.clientX - startX;
                const dy = e.clientY - startY;
                const container = document.getElementById('pdf-overlay');
                
                let newLeft = initialLeft + dx;
                let newTop = initialTop + dy;
                
                newLeft = Math.max(0, Math.min(newLeft, container.clientWidth - el.clientWidth));
                newTop = Math.max(0, Math.min(newTop, container.clientHeight - el.clientHeight));
                
                el.style.left = newLeft + 'px';
                el.style.top = newTop + 'px';
                
                const pctX = (newLeft / container.clientWidth) * 100;
                const pctY = (newTop / container.clientHeight) * 100;
                
                const key = el.getAttribute('data-key').replace('.', '_');
                document.getElementById(`coord-x-${key}`).value = pctX.toFixed(2);
                document.getElementById(`coord-y-${key}`).value = pctY.toFixed(2);
            });
            
            document.addEventListener('mouseup', function() {
                if (isDragging) {
                    isDragging = false;
                    el.style.zIndex = 100;
                }
            });
        }

        // Validate form
        $('#template-form').on('submit', function(e) {
            if ($('.placed-var-badge').length === 0) {
                e.preventDefault();
                Swal.fire('Perhatian', 'Harap tempel dan posisikan setidaknya satu variabel data di PDF sebelum menyimpan template.', 'warning');
            }
        });
    });

    // Handle new PDF file uploader dynamically
    function handleFileChange(input) {
        const file = input.files[0];
        const title = document.getElementById('preview-title');

        if (file && file.type === "application/pdf") {
            const fileURL = URL.createObjectURL(file);
            
            // Clear existing variables placed since it's a new PDF page layout
            $('#pdf-overlay').html('');
            $('#coordinate-inputs-container').html('');

            // Reload canvas with the new PDF URL
            pdfjsLib.getDocument(fileURL).promise.then(function(pdf) {
                pdf.getPage(1).then(function(page) {
                    const canvas = document.getElementById('pdf-canvas');
                    const ctx = canvas.getContext('2d');
                    const wrapper = document.getElementById('pdf-wrapper');
                    const viewport = page.getViewport({ scale: 1.2 });
                    
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    wrapper.style.width = viewport.width + 'px';
                    wrapper.style.height = viewport.height + 'px';

                    const renderContext = {
                        canvasContext: ctx,
                        viewport: viewport
                    };
                    page.render(renderContext).promise.then(function() {
                        title.innerHTML = "Pratinjau File PDF Baru (Belum Disimpan)";
                        title.parentElement.classList.add('text-success');
                        Swal.fire('Sukses', 'File PDF diperbarui. Silakan posisikan ulang variabel data tag.', 'success');
                    });
                });
            });
        }
    }
</script>
@endpush
@endsection