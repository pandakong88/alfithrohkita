@extends('layouts.tenant')

@section('content')
<div class="page-inner" style="padding-top: 15px !important;">
    {{-- HEADER --}}
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row mb-3">
        <div>
            <h3 class="fw-bold mb-1">Edit Template Import</h3>
            <h6 class="op-7 mb-0">Modifikasi susunan kolom Excel sesuai perkembangan data pondok Anda.</h6>
        </div>
        <div class="ms-md-auto py-2 py-md-0">
            <a href="{{ route('tenant.import-templates.index') }}" class="btn btn-black btn-border btn-round btn-sm">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- PETUNJUK PENGGUNAAN --}}
    <div class="alert alert-info border-0 shadow-sm d-flex align-items-start gap-3 mb-4 card-round" style="background: linear-gradient(135deg, #e8f4fd 0%, #d6ebff 100%); color: #1a4d80; padding: 15px 20px;">
        <div class="bg-primary text-white rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px; min-width: 38px; box-shadow: 0 4px 10px rgba(21, 114, 232, 0.3);">
            <i class="fas fa-info-circle fa-lg"></i>
        </div>
        <div style="flex-grow: 1;">
            <h5 class="fw-bold mb-1" style="font-size: 14px; color: #1a4d80;">Petunjuk Kustomisasi Template</h5>
            <p class="mb-0 small" style="line-height: 1.6; color: #2b5c8f; opacity: 0.95;">
                <i class="fas fa-check-circle me-1 text-success"></i> <strong>Langkah 1:</strong> Pilih komponen data di panel kiri dengan mengeklik tombol <span class="badge bg-primary px-1"><i class="fas fa-plus"></i></span> atau gunakan kolom pencarian untuk menyaring bidang.<br>
                <i class="fas fa-check-circle me-1 text-success"></i> <strong>Langkah 2:</strong> Atur urutan kolom Excel Anda dengan menyeret handle <i class="fas fa-grip-vertical mx-1 text-muted"></i> di panel sebelah kanan.<br>
                <i class="fas fa-exclamation-triangle me-1 text-warning"></i> <strong>Catatan:</strong> Bidang bertanda gembok <i class="fas fa-lock text-warning mx-1"></i> adalah kolom data inti santri wajib (tidak boleh dihapus).
            </p>
        </div>
    </div>

    <form action="{{ route('tenant.import-templates.update', $template->id) }}" method="POST" id="formTemplate">
        @csrf
        @method('PUT')
        <div class="row">
            {{-- SISI KIRI: PILIHAN KOMPONEN (DIKELOMPOKKAN BERDASARKAN ENTITAS) --}}
            <div class="col-md-5">
                <div class="card card-round border-0 shadow-sm">
                    <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold" style="font-size: 14px;">1. Pilih Komponen Data</h5>
                        {{-- Tombol Tambah Custom Field --}}
                        <button type="button" class="btn btn-xs btn-success btn-round" id="btnCreateCustomField">
                            <i class="fas fa-plus-circle me-1"></i> Kolom Kustom
                        </button>
                    </div>
                    <div class="card-body p-3" style="max-height: 550px; overflow-y: auto;">
                        {{-- Fitur Pencarian / Filter Field --}}
                        <div class="mb-3">
                            <div class="input-group input-group-sm border rounded bg-white">
                                <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" id="searchFieldInput" class="form-control bg-transparent border-0 ps-1" placeholder="Cari nama atau key kolom..." style="font-size: 12px; box-shadow: none;">
                                <button class="btn btn-link text-muted p-0 pe-2 border-0" type="button" id="btnClearSearch" style="display: none; box-shadow: none; text-decoration: none;">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="accordion accordion-secondary border-0" id="accordionFields">
                            @foreach($groupedFields as $entity => $fields)
                                <div class="card border-0 mb-2 shadow-none bg-light card-entity-group" data-entity="{{ $entity }}">
                                    {{-- Header Kategori --}}
                                    <div class="card-header p-2" id="heading-{{ $entity }}" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $entity }}">
                                        <span class="fw-bold text-uppercase small text-primary">
                                            <i class="fas {{ $entity === 'custom' ? 'fas fa-tags' : 'fa-folder' }} me-2"></i> 
                                            Data {{ str_replace('_', ' ', $entity) }}
                                        </span>
                                        <span class="badge bg-white text-primary float-end border field-count-badge">{{ count($fields) }}</span>
                                    </div>

                                    {{-- Isi Kategori Field --}}
                                    <div id="collapse-{{ $entity }}" class="collapse show" data-bs-parent="#accordionFields">
                                        <div class="card-body p-2 bg-white border-top">
                                            <div class="list-group list-group-flush field-list-container">
                                                @foreach($fields as $field)
                                                    <div class="list-group-item p-2 d-flex align-items-center justify-content-between item-field-source" 
                                                         data-id="{{ $field->id }}" 
                                                         data-label="{{ $field->label }}"
                                                         data-key="{{ $field->field_key }}"
                                                         data-required="{{ $field->is_required }}">
                                                        <div>
                                                            <span class="fw-bold text-dark small" style="font-size: 12px;">{{ $field->label }}</span>
                                                            @if($field->is_required) <span class="text-danger">*</span> @endif
                                                            <br><code class="text-muted" style="font-size: 10px;">{{ $field->field_key }}</code>
                                                        </div>
                                                        <div class="d-flex align-items-center gap-1">
                                                            {{-- Jika field kustom milik pondok (bukan core), tampilkan tombol hapus master row --}}
                                                            @if($entity === 'custom')
                                                                <button type="button" class="btn btn-xs btn-link text-danger btn-delete-master-field me-1" data-id="{{ $field->id }}">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            @endif
                                                            <button type="button" class="btn btn-xs btn-primary btn-round btn-add-field" data-id="{{ $field->id }}">
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            {{-- Wadah darurat jika folder custom belum lahir dari data loop backend --}}
                            @if(!isset($groupedFields['custom']))
                                <div class="card border-0 mb-2 shadow-none bg-light card-entity-group d-none" data-entity="custom">
                                    <div class="card-header p-2" id="heading-custom" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#collapse-custom">
                                        <span class="fw-bold text-uppercase small text-primary">
                                            <i class="fas fa-tags me-2"></i> Data custom
                                        </span>
                                        <span class="badge bg-white text-primary float-end border field-count-badge">0</span>
                                    </div>
                                    <div id="collapse-custom" class="collapse show" data-bs-parent="#accordionFields">
                                        <div class="card-body p-2 bg-white border-top">
                                            <div class="list-group list-group-flush field-list-container"></div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>

            {{-- SISI KANAN: RAKITAN STRUKTUR EXCEL ANDA --}}
            <div class="col-md-7">
                <div class="card card-round border-0 shadow-sm">
                    <div class="card-header bg-light py-3">
                        <h5 class="card-title mb-0 fw-bold" style="font-size: 14px;">2. Susunan Kolom Excel Anda</h5>
                    </div>
                    <div class="card-body p-3">
                        {{-- Input Nama Template --}}
                        <div class="form-group p-0 mb-3">
                            <label class="fw-bold mb-1 small text-dark">Nama Template <span class="text-danger">*</span></label>
                            <input type="text" name="nama_template" class="form-control" placeholder="Contoh: Sensus Santri Komplek A Juni 2026" value="{{ old('nama_template', $template->nama_template) }}" required>
                        </div>

                        <label class="fw-bold mb-1 small text-dark">Urutan Kolom (Tarik/Seret baris untuk mengatur posisi)</label>
                        
                        {{-- Container Drag & Drop --}}
                        <div id="selectedFieldsContainer" class="border rounded-3 p-3" style="min-height: 320px; background-color: #fcfdfe; border-color: #e3ebf6 !important; transition: all 0.3s ease;">
                            <div id="emptyState" class="text-center py-5 text-muted" style="display: none;">
                                <i class="fas fa-th-list fa-3x mb-3 text-disabled"></i>
                                <p class="mb-0 small">Belum ada kolom yang dipilih.<br>Klik tombol <span class="badge bg-primary px-1"><i class="fas fa-plus"></i></span> di sebelah kiri untuk merakit susunan kolom.</p>
                            </div>
                            <ul id="sortableColumns" class="list-group">
                                {{-- Data yang sudah ada di database akan dirender secara dinamis via JS di bawah --}}
                            </ul>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-success btn-round shadow-sm px-4">
                                <i class="fas fa-save me-2"></i> Perbarui Template
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .card-round { border-radius: 12px !important; }
    #sortableColumns .list-group-item { 
        cursor: move; 
        transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }
    #sortableColumns .list-group-item.locked-field { cursor: grab; }
    .ui-sortable-placeholder { background-color: #e8f4fd !important; border: 2px dashed #1572e8 !important; height: 52px; margin-bottom: 8px; border-radius: 8px; visibility: visible !important; }
    .gap-1 { gap: 0.25rem !important; }
    .gap-3 { gap: 1rem !important; }

    .locked-column-item {
        border-left: 4px solid #ffa534 !important;
        background-color: #fffbf5 !important;
    }
    
    .optional-column-item {
        border-left: 4px solid #1572e8 !important;
        background-color: #ffffff !important;
    }
    
    .column-item-hover:hover {
        background-color: #fbfcfe !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
        transform: translateY(-1px);
    }

    .item-field-source {
        transition: background-color 0.2s ease, transform 0.1s ease;
        border-radius: 6px;
        margin-bottom: 4px;
        border: 1px solid #f0f0f0;
    }

    .item-field-source:hover {
        background-color: #f8fafc !important;
        transform: scale(1.01);
    }

    .highlight-new-field {
        position: relative;
        animation: flashGreen 2.5s ease-out forwards !important;
    }

    @keyframes flashGreen {
        0% { background-color: #d4edda !important; box-shadow: inset 0 0 10px rgba(40, 167, 69, 0.3); }
        40% { background-color: #d4edda !important; }
        100% { background-color: #ffffff !important; }
    }
</style>
@endsection

@push('scripts')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
    $(document).ready(function() {
        // Aktifkan fitur seret urutan pada list kanan
        $("#sortableColumns").sortable({
            placeholder: "ui-sortable-placeholder",
            update: function(event, ui) {
                renumberColumns();
            }
        });

        // ==========================================
        // FITUR: CARI & FILTER FIELD DI PANEL KIRI
        // ==========================================
        $('#searchFieldInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            if (value.length > 0) {
                $('#btnClearSearch').show();
            } else {
                $('#btnClearSearch').hide();
            }

            $('.item-field-source').each(function() {
                var label = ($(this).data('label') || '').toString().toLowerCase();
                var key = ($(this).data('key') || '').toString().toLowerCase();
                if (label.indexOf(value) > -1 || key.indexOf(value) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            // Tampilkan/sembunyikan card kategori dan sesuaikan badge count data yang kelihatan
            $('.card-entity-group').each(function() {
                var visibleCount = $(this).find('.item-field-source:visible').length;
                if (visibleCount === 0) {
                    $(this).hide();
                } else {
                    $(this).show();
                    $(this).find('.field-count-badge').text(visibleCount);
                    // Jika sedang mencari sesuatu, otomatis buka accordion kategori
                    if (value.length > 0) {
                        $(this).find('.collapse').addClass('show');
                    }
                }
            });
        });

        $('#btnClearSearch').click(function() {
            $('#searchFieldInput').val('').trigger('keyup');
        });

        // ==========================================
        // SINKRONISASI INITIAL DATA (POPULATE EXISTING FIELDS)
        // ==========================================
        function initExistingFields() {
            // Definisikan kolom inti yang dilarang dihapus
            var targetKeys = ['nis', 'nama_lengkap', 'jenis_kelamin'];
            
            // Ambil data susunan field yang dikirim dari controller (sudah terurut berdasarkan pivot 'order')
            var existingFields = @json($template->fields);

            if (existingFields.length === 0) {
                $('#emptyState').show();
                return;
            }

            existingFields.forEach(function(field) {
                var isLocked = targetKeys.includes(field.field_key);
                var html = '';

                if (isLocked) {
                    // Jika field inti bawaan wajib (nis, nama_lengkap, jenis_kelamin)
                    html = `
                        <li class="list-group-item p-2 mb-2 rounded shadow-sm d-flex align-items-center justify-content-between border locked-field locked-column-item" id="col-${field.id}" data-source-id="${field.id}">
                            <div class="d-flex align-items-center">
                                <span class="handle-sort text-muted me-3" style="cursor: grab;"><i class="fas fa-grip-vertical"></i></span>
                                <span class="badge bg-primary text-white me-2 index-number" style="width: 25px;">0</span>
                                <div>
                                    <span class="fw-bold small text-dark">${field.label} <span class="text-danger">*</span></span>
                                    <br><code class="text-muted" style="font-size: 9px;">Key DB: ${field.field_key} (Wajib)</code>
                                </div>
                            </div>
                            <input type="hidden" name="fields[]" value="${field.id}">
                            <span class="text-muted me-2 small" data-bs-toggle="tooltip" title="Kolom inti wajib ada di template Excel">
                                <i class="fas fa-lock text-warning"></i>
                            </span>
                        </li>
                    `;
                } else {
                    // Jika field opsional atau custom field buatan user
                    html = `
                        <li class="list-group-item p-2 mb-2 rounded shadow-sm d-flex align-items-center justify-content-between border optional-column-item column-item-hover" id="col-${field.id}" data-source-id="${field.id}" style="transition: all 0.2s ease;">
                            <div class="d-flex align-items-center">
                                <span class="handle-sort text-muted me-3" style="cursor: grab;"><i class="fas fa-grip-vertical"></i></span>
                                <span class="badge bg-primary text-white me-2 index-number" style="width: 25px;">0</span>
                                <div>
                                    <span class="fw-bold small text-dark">${field.label}</span>
                                    <br><code class="text-muted" style="font-size: 9px;">Key DB: ${field.field_key}</code>
                                </div>
                            </div>
                            <input type="hidden" name="fields[]" value="${field.id}">
                            <button type="button" class="btn btn-xs btn-link text-danger btn-remove-column" data-id="${field.id}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </li>
                    `;
                }

                $('#sortableColumns').append(html);

                // Ubah indikator tombol di sisi kiri menjadi centang hijau aktif
                var leftItem = $(`.item-field-source[data-id="${field.id}"]`);
                if (leftItem.length > 0) {
                    var btnLeft = leftItem.find('.btn-add-field');
                    btnLeft.removeClass('btn-primary').addClass('btn-success').html('<i class="fas fa-check"></i>');
                }
            });

            renumberColumns();
        }

        // Jalankan pemuatan komponen lama sesaat setelah halaman dimuat
        initExistingFields();

        // Event Klik Tombol Tambah (+) ke Sisi Kanan / Hapus (✓) dari Kanan
        $('body').on('click', '.btn-add-field', function() {
            var btnLeft = $(this);
            var item = btnLeft.closest('.item-field-source');
            var id = item.data('id');
            var label = item.data('label');
            var key = item.data('key');

            if (btnLeft.hasClass('btn-success')) {
                // Toggling remove if already in template list
                $(`#col-${id}`).remove();
                renumberColumns();
                if ($('#sortableColumns li').length === 0) {
                    $('#emptyState').show();
                }
                btnLeft.removeClass('btn-success').addClass('btn-primary').html('<i class="fas fa-plus"></i>');
                
                $.notify({ 
                    icon: 'fas fa-info-circle', 
                    title: 'Dihapus', 
                    message: 'Kolom "' + label + '" dikeluarkan dari susunan.' 
                }, { 
                    type: 'info', 
                    placement: { from: "bottom", align: "right" },
                    delay: 2000
                });
                return;
            }

            $('#emptyState').hide();

            var html = `
                <li class="list-group-item p-2 mb-2 rounded shadow-sm d-flex align-items-center justify-content-between border optional-column-item column-item-hover" id="col-${id}" data-source-id="${id}" style="transition: all 0.2s ease;">
                    <div class="d-flex align-items-center">
                        <span class="handle-sort text-muted me-3" style="cursor: grab;"><i class="fas fa-grip-vertical"></i></span>
                        <span class="badge bg-primary text-white me-2 index-number" style="width: 25px;">0</span>
                        <div>
                            <span class="fw-bold small text-dark">${label}</span>
                            <br><code class="text-muted" style="font-size: 9px;">Key DB: ${key}</code>
                        </div>
                    </div>
                    <input type="hidden" name="fields[]" value="${id}">
                    <button type="button" class="btn btn-xs btn-link text-danger btn-remove-column" data-id="${id}">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </li>
            `;

            $('#sortableColumns').append(html);
            renumberColumns();

            btnLeft.removeClass('btn-primary').addClass('btn-success').html('<i class="fas fa-check"></i>');
            
            $.notify({ 
                icon: 'fas fa-check-circle', 
                title: 'Ditambahkan', 
                message: 'Kolom "' + label + '" dimasukkan ke susunan.' 
            }, { 
                type: 'success', 
                placement: { from: "bottom", align: "right" },
                delay: 2000
            });
        });

        // Event Klik Tombol Hapus Kolom di Sisi Kanan
        $('body').on('click', '.btn-remove-column', function() {
            var targetId = $(this).data('id');
            var item = $(this).closest('li');
            var label = item.find('.fw-bold').text();
            
            item.remove();
            renumberColumns();
            
            if ($('#sortableColumns li').length === 0) {
                $('#emptyState').show();
            }

            var btnLeft = $(`.btn-add-field[data-id="${targetId}"]`);
            if (btnLeft.length > 0) {
                btnLeft.removeClass('btn-success').addClass('btn-primary').html('<i class="fas fa-plus"></i>');
            }
        });

        // Helper hitung ulang indeks kanan
        function renumberColumns() {
            $('#sortableColumns li').each(function(index) {
                $(this).find('.index-number').text(index + 1);
            });
        }

        // ==========================================
        // FITUR: TAMBAH ROW CUSTOM FIELD BARU (AJAX)
        // ==========================================
        $('#btnCreateCustomField').click(function() {
            swal({
                title: 'Tambah Kolom Kustom',
                text: 'Masukkan nama kolom data baru yang Anda butuhkan (Misal: Ukuran Sarung, Nama Sekolah Asal)',
                content: {
                    element: "input",
                    attributes: {
                        placeholder: "Nama Kolom Tambahan",
                        type: "text",
                        id: "inputCustomLabel"
                    },
                },
                buttons: {
                    cancel: { text: "Batal", visible: true, className: "btn btn-danger" },
                    confirm: { text: "Simpan", className: "btn btn-success" }
                }
            }).then((value) => {
                if (!value || value.trim() === "") return;

                $.ajax({
                    url: "{{ route('tenant.custom-fields.store') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        label: value
                    },
                    success: function(response) {
                        if(response.success) {
                            var field = response.data;
                            
                            var customCard = $('.card-entity-group[data-entity="custom"]');
                            customCard.removeClass('d-none');

                            var collapseCustom = $('#collapse-custom');
                            if (!collapseCustom.hasClass('show')) {
                                collapseCustom.addClass('show');
                            }

                            var newRowHtml = `
                                <div class="list-group-item p-2 d-flex align-items-center justify-content-between item-field-source highlight-new-field" 
                                    data-id="${field.id}" 
                                    data-label="${field.label}"
                                    data-key="${field.field_key}"
                                    data-required="0">
                                    <div>
                                        <span class="fw-bold text-dark small" style="font-size: 12px;">${field.label}</span>
                                        <br><code class="text-muted" style="font-size: 10px;">${field.field_key}</code>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <button type="button" class="btn btn-xs btn-link text-danger btn-delete-master-field me-1" data-id="${field.id}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-primary btn-round btn-add-field" data-id="${field.id}">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                                                                                                                                                    
                            customCard.find('.field-list-container').append(newRowHtml);
                            
                            var currentCount = parseInt(customCard.find('.field-count-badge').text()) || 0;
                            customCard.find('.field-count-badge').text(currentCount + 1);

                            $.notify({ icon: 'fas fa-check-circle', title: 'Berhasil', message: 'Kolom kustom "' + field.label + '" sukses ditambahkan!' }, { type: 'success', placement: { from: "bottom", align: "right" } });
                        }
                    },
                    error: function(xhr) {
                        var errorMsg = xhr.responseJSON ? xhr.responseJSON.message : "Gagal menambah data.";
                        swal({ title: "Gagal", text: errorMsg, icon: "error" });
                    }
                });
            });
        });

        // ==========================================
        // FITUR: HAPUS ROW CUSTOM FIELD (AJAX)
        // ==========================================
        $('body').on('click', '.btn-delete-master-field', function(e) {
            e.stopPropagation();
            var btn = $(this);
            var id = btn.data('id');
            var rowItem = btn.closest('.item-field-source');

            swal({
                title: "Apakah Anda yakin?",
                text: "Menghapus kolom master kustom ini akan menghilangkannya dari pilihan template!",
                icon: "warning",
                buttons: {
                    cancel: { text: "Batal", visible: true, className: "btn btn-focus" },
                    confirm: { text: "Ya, Hapus!", className: "btn btn-danger" }
                },
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: "{{ route('tenant.custom-fields.destroy', ':id') }}".replace(':id', id),
                        method: "DELETE",
                        data: { _token: "{{ csrf_token() }}", },
                        success: function(response) {
                            if(response.success) {
                                $('#col-' + id).remove();
                                renumberColumns();

                                var customCard = $('.card-entity-group[data-entity="custom"]');
                                var currentCount = parseInt(customCard.find('.field-count-badge').text()) || 0;
                                customCard.find('.field-count-badge').text(Math.max(0, currentCount - 1));

                                rowItem.remove();

                                $.notify({ icon: 'fas fa-check-circle', title: 'Terhapus', message: 'Kolom kustom berhasil dihapus.' }, { type: 'success', placement: { from: "bottom", align: "right" } });
                            }
                        },
                        error: function(xhr) {
                            swal({ title: "Gagal", text: "Gagal menghapus kolom data.", icon: "error" });
                        }
                    });
                }
            });
        });

        // Validasi Akhir sebelum Submit Form
        $('#formTemplate').submit(function(e) {
            if ($('#sortableColumns li').length === 0) {
                e.preventDefault();
                swal({ title: "Gagal Menyimpan", text: "Susunan kolom Excel Anda masih kosong.", icon: "error" });
            }
        });
    });
</script>
@endpush