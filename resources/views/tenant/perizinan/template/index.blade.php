@extends('layouts.tenant')

@section('title', 'Template Perizinan')

@section('content')
<div class="container-fluid" style="background: #f4f7f6; min-height: 90vh;">
    <div class="page-inner py-5">
        
        {{-- HEADER --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h2 class="fw-extrabold text-dark mb-1">
                    <i class="fas fa-file-contract text-primary me-2"></i> Template Perizinan
                </h2>
                <p class="text-muted fw-medium">Kelola format surat izin santri dengan mapping variabel otomatis.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('tenant.template-perizinan.upload') }}" class="btn btn-primary btn-round px-4 shadow">
                    <i class="fas fa-plus-circle me-2"></i> Tambah Template
                </a>
            </div>
        </div>

        {{-- TABLE CARD --}}
        <div class="card card-round border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="templateTable" class="table table-hover align-middle">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th style="width: 45%;">Template & Konteks</th>
                                <th class="text-center">Ukuran</th>
                                <th class="text-center">Data Terhubung</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $template)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-start">
                                        <div class="avatar avatar-sm me-3 mt-1">
                                            <span class="avatar-title rounded bg-soft-primary text-primary fw-bold">
                                                {{ substr($template->nama, 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="d-flex align-items-center flex-wrap">
                                                <h6 class="fw-bold mb-0 text-dark me-2">{{ $template->nama }}</h6>
                                                @if($template->is_default)
                                                    <span class="badge badge-primary badge-xs rounded-pill text-white">
                                                        <i class="fas fa-star me-1" style="font-size: 8px;"></i> Utama
                                                    </span>
                                                @endif
                                            </div>
                                            <small class="text-muted d-block mt-1 lh-sm">
                                                {{ Str::limit($template->deskripsi, 85) ?: 'Tidak ada catatan operasional.' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @php
                                        $layouts = [
                                            1 => ['label' => 'A4', 'class' => 'badge-info', 'desc' => '1 Lembar'],
                                            2 => ['label' => 'A5', 'class' => 'badge-success', 'desc' => '1/2 Lembar'],
                                            4 => ['label' => 'A6', 'class' => 'badge-warning', 'desc' => '1/4 Lembar']
                                        ];
                                        $conf = $layouts[$template->layout_print] ?? ['label' => '?', 'class' => 'badge-secondary', 'desc' => 'Custom'];
                                    @endphp
                                    <span class="badge {{ $conf['class'] }} rounded-pill px-3 text-white">{{ $conf['label'] }}</span>
                                    <br><small class="text-muted mt-1" style="font-size: 10px;">{{ $conf['desc'] }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center">
                                        <span class="fw-bold text-primary">{{ count($template->required_variables ?? []) }}</span>
                                        <small class="text-muted" style="font-size: 10px;">Variabel</small>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center p-0">
                                        <input class="form-check-input status-toggle" 
                                               type="checkbox" 
                                               role="switch" 
                                               style="cursor: pointer; width: 40px; height: 20px; margin-left: 0;"
                                               data-id="{{ $template->id }}"
                                               {{ $template->is_active ? 'checked' : '' }}>
                                    </div>
                                    <small id="status-label-{{ $template->id }}" class="text-muted mt-1 d-block" style="font-size: 10px;">
                                        {{ $template->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </small>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <button type="button" class="btn btn-icon btn-link btn-info" 
                                                onclick="openPreview('{{ asset('storage/'.$template->file_pdf) }}', '{{ $template->nama }}', {{ json_encode($template->required_variables) }}, {{ $template->is_default ? 'true' : 'false' }}, '{{ route('tenant.template-perizinan.edit', $template->id) }}')"
                                                title="Detail Pratinjau">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        <a href="{{ route('tenant.template-perizinan.edit', $template->id) }}" 
                                           class="btn btn-icon btn-link btn-primary" title="Ubah">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <form action="{{ route('tenant.template-perizinan.destroy', $template->id) }}" method="POST" id="del-{{ $template->id }}" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="button" onclick="confirmDelete({{ $template->id }})" 
                                                    class="btn btn-icon btn-link btn-danger" title="Hapus">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PREVIEW PREMIUM --}}
{{-- MODAL PREVIEW --}}
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header bg-primary-gradient py-3 px-4 border-0">
                <div class="d-flex align-items-center">
                    <div class="p-2 bg-white bg-opacity-25 rounded-circle me-3">
                        <i class="fas fa-file-pdf text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title text-white fw-bold mb-0" id="modalTemplateName">Detail Template</h5>
                        <small class="text-white text-opacity-75" style="font-size: 11px;">Pratinjau Dokumen & Variabel Mapping</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0">
                <div class="row g-0">
                    <div class="col-lg-8" style="background: #e9ecef;">
                        <div class="p-3 d-flex flex-column h-100">
                            <div class="shadow-lg rounded-3 overflow-hidden bg-white" style="height: 650px;">
                                <iframe id="modalPdfFrame" src="" width="100%" height="100%" style="border: none;"></iframe>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 bg-white">
                        <div class="p-4 h-100 d-flex flex-column">
                            <div class="mb-4">
                                <label class="text-uppercase text-muted fw-bold mb-2" style="font-size: 10px; letter-spacing: 1.5px;">Status Konfigurasi</label>
                                <div id="modalStatusBadge" class="mb-2"></div>
                            </div>
                            <div class="mb-3">
                                <label class="text-uppercase text-muted fw-bold mb-3 d-block" style="font-size: 10px; letter-spacing: 1.5px;">
                                    <i class="fas fa-database me-2"></i> Variabel Terhubung
                                </label>
                                <div id="modalVariableList" class="d-flex flex-column gap-2" style="max-height: 400px; overflow-y: auto; padding-right: 5px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-label-secondary btn-round px-4 fw-bold" data-bs-dismiss="modal">Tutup</button>
                
                <button type="button" onclick="printPdf()" class="btn btn-info btn-round px-4 fw-bold text-white">
                    <i class="fas fa-print me-2"></i>Cetak Template
                </button>
            
                <a href="#" id="modalEditBtn" class="btn btn-primary btn-round px-4 fw-bold">
                    <i class="fas fa-edit me-2"></i>Edit Struktur
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .fw-extrabold { font-weight: 800; }
    .bg-soft-primary { background-color: rgba(21, 114, 232, 0.1); border: 1px solid rgba(21, 114, 232, 0.2); }
    .bg-primary-gradient { background: linear-gradient(135deg, #1572e8 0%, #0d59bd 100%) !important; }
    .bg-soft-info { background-color: rgba(72, 171, 247, 0.1) !important; }
    .card-round { border-radius: 15px !important; }
    .table thead th { border-top: none !important; border-bottom-width: 1px !important; padding: 15px 10px !important; }
    .badge-xs { padding: 0.25em 0.6em; font-size: 9px; }
    .form-check-input:checked { background-color: #28a745; border-color: #28a745; }
    .btn-label-secondary { background: #ebedef; color: #5a5c5e; border: none; }
    .btn-label-secondary:hover { background: #e2e5e8; }
    #modalVariableList::-webkit-scrollbar { width: 4px; }
    #modalVariableList::-webkit-scrollbar-thumb { background: #e9ecef; border-radius: 10px; }
</style>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  // Inisialisasi Modal Bootstrap 5 secara global
  let previewModal;

    $(document).ready(function() {
        // Inisialisasi instance modal agar bisa dipanggil lewat JS murni
        previewModal = new bootstrap.Modal(document.getElementById('previewModal'));

        $('#templateTable').DataTable({
            "pageLength": 10,
            "language": {
                "search": "Cari:",
                "lengthMenu": "_MENU_",
                "info": "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                "paginate": { "previous": "<", "next": ">" }
            },
            "columnDefs": [{ "orderable": false, "targets": [3, 4] }]
        });
    });

    function openPreview(pdfUrl, name, variables, isDefault, editUrl) {
        // Update Konten
        document.getElementById('modalTemplateName').innerText = name;
        document.getElementById('modalPdfFrame').src = pdfUrl + "#toolbar=0&navpanes=0";
        document.getElementById('modalEditBtn').href = editUrl;
        // Gunakan parameter #toolbar=1 agar fitur bawaan PDF browser muncul jika diperlukan
        document.getElementById('modalPdfFrame').src = pdfUrl + "#toolbar=1";

        // Status Badge
        const statusContainer = document.getElementById('modalStatusBadge');
        statusContainer.innerHTML = isDefault 
            ? '<span class="badge bg-primary px-3 rounded-pill text-white"><i class="fas fa-star me-1"></i> Template Utama</span>'
            : '<span class="badge border px-3 rounded-pill text-muted">Template Opsional</span>';

        // Variables List
        const varContainer = document.getElementById('modalVariableList');
        varContainer.innerHTML = '';

        if (variables && variables.length > 0) {
            variables.forEach(v => {
                const item = document.createElement('div');
                item.className = 'd-flex align-items-center p-2 rounded-3 border bg-light bg-opacity-50';
                item.style.fontSize = '12px';
                item.innerHTML = `
                    <div class="bg-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                        <i class="fas fa-code text-white" style="font-size: 10px;"></i>
                    </div>
                    <div class="fw-bold text-dark text-uppercase">${v}</div>
                `;
                varContainer.appendChild(item);
            });
        } else {
            varContainer.innerHTML = '<p class="text-muted small italic p-3 text-center border rounded-3">Tidak ada variabel mapping.</p>';
        }

        // TAMPILKAN MODAL (Pakai instance BS5)
        previewModal.show();
    }
    
    function printPdf() {
        const iframe = document.getElementById('modalPdfFrame');
        
        if (!iframe.src || iframe.src.includes('about:blank')) {
            Swal.fire('Waduh', 'File PDF belum siap atau tidak ditemukan.', 'error');
            return;
        }

        try {
            // Memberi fokus ke iframe lalu memicu dialog print browser
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        } catch (e) {
            console.error(e);
            // Fallback jika lintas domain (CORS) bermasalah, buka di tab baru
            window.open(iframe.src, '_blank');
        }
    }
    // AJAX TOGGLE STATUS
    $(document).on('change', '.status-toggle', function() {
        const id = $(this).data('id');
        const isActive = $(this).prop('checked') ? 1 : 0;
        const label = $(`#status-label-${id}`);

        $.ajax({
            url: "{{ route('tenant.template-perizinan.update-status') }}",
            method: "POST",
            data: { _token: "{{ csrf_token() }}", id: id, is_active: isActive },
            success: function(response) {
                if (response.success) {
                    label.text(isActive ? 'Aktif' : 'Nonaktif');
                    $.notify({ icon: 'fas fa-check-circle', title: 'Sukses', message: 'Status diperbarui.' }, { type: 'success', time: 500 });
                }
            },
            error: function() {
                $(this).prop('checked', !isActive);
                Swal.fire('Error', 'Gagal mengubah status.', 'error');
            }
        });
    });

    // CONFIRM DELETE
    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus Template?',
            text: "Data ini akan hilang selamanya.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) { $(`#del-${id}`).submit(); }
        })
    }
</script>
@endpush