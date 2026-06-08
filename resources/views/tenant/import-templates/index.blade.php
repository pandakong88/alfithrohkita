@extends('layouts.tenant')

@section('content')
<div class="page-inner" style="padding-top: 15px !important;">
    {{-- HEADER --}}
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row mb-3">
        <div>
            <h3 class="fw-bold mb-1">Template Survey</h3>
            <h6 class="op-7 mb-0">Manajemen format kustom untuk import data Excel.</h6>
        </div>
        <div class="ms-md-auto py-2 py-md-0">
            <a href="{{ route('tenant.import-templates.create') }}" class="btn btn-primary btn-round btn-sm shadow-sm text-white">
                <i class="fas fa-plus-circle me-2"></i> Buat Template
            </a>
        </div>
    </div>

    @if(session('success'))
        <div id="success-trigger" data-message="{{ session('success') }}"></div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="card card-round border-0 shadow-none mt-n1">
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="templateTable" class="display table table-hover mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 45%">Nama Template</th>
                                    <th style="width: 25%">Tanggal Dibuat</th>
                                    <th class="text-center" style="width: 15%">Jumlah Kolom</th>
                                    <th class="text-end pe-3" style="width: 15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($templates as $template)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3" style="width: 32px; height: 32px;">
                                                    <span class="avatar-title rounded-circle border border-white bg-primary text-white" style="font-size: 11px;">
                                                        <i class="fas fa-file-excel"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-1">
                                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 13px;">{{ $template->nama_template }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted small fw-bold">
                                                <i class="far fa-calendar-alt me-1 text-muted"></i> {{ $template->created_at->format('d/m/Y') }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" 
                                                    class="badge badge-primary rounded-pill border-0 py-1.5 px-3" 
                                                    style="cursor: pointer; background-color: #e1f0ff !important; color: #1572e8 !important; font-weight: bold;"
                                                    data-bs-toggle="popover" 
                                                    data-bs-trigger="hover focus"
                                                    data-bs-placement="top"
                                                    data-bs-html="true"
                                                    title="Struktur Kolom Excel" 
                                                    data-bs-content="
                                                        <div class='d-flex flex-column gap-1 text-start'>
                                                            @foreach($template->fields as $field)
                                                                <div>
                                                                    <span class='badge bg-light text-dark border small'>
                                                                        {{ $field->pivot->order + 1 }}. {{ $field->label }} 
                                                                        @if($field->is_required) <span class='text-danger'>*</span> @endif
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    ">
                                                {{ count($template->fields ?? []) }} Kolom <i class="fas fa-info-circle ms-1" style="font-size: 10px;"></i>
                                            </button>
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="d-flex justify-content-end align-items-center gap-1">
                                                
                                                <div class="dropdown d-inline-block">
                                                    <button type="button" 
                                                            class="btn btn-icon btn-round btn-success btn-xs" 
                                                            data-bs-toggle="dropdown" 
                                                            aria-expanded="false"
                                                            title="Unduh Excel">
                                                        <i class="fa fa-download" style="font-size: 11px;"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="font-size: 13px; min-width: 240px; margin-top: 5px;">
                                                        <li>
                                                            <a class="dropdown-item py-2" href="{{ route('tenant.import-templates.download', [$template->id, 'with_data' => 'false']) }}">
                                                                <i class="fas fa-file-excel text-success me-2" style="width: 16px;"></i> 
                                                                <strong>Download Template Kosong</strong>
                                                                <small class="text-muted d-block mt-0.5">Untuk tambah santri baru massal</small>
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item py-2 btn-trigger-download-modal" 
                                                               href="javascript:void(0)"
                                                               data-template-id="{{ $template->id }}"
                                                               data-template-name="{{ $template->nama_template }}">
                                                                <i class="fas fa-database text-primary me-2" style="width: 16px;"></i> 
                                                                <strong>Download + Data Santri</strong>
                                                                <small class="text-muted d-block mt-0.5">Untuk edit / update massal data lama</small>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                         
                                                <a href="{{ route('tenant.import-templates.show', $template->id) }}" 
                                                   class="btn btn-icon btn-round btn-info btn-xs" 
                                                   data-bs-toggle="tooltip" title="Lihat Detail">
                                                    <i class="fa fa-eye" style="font-size: 11px;"></i>
                                                </a>

                                                <form action="{{ route('tenant.import-templates.duplicate', $template->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-icon btn-round btn-secondary btn-xs text-white" 
                                                            style="background-color: #6861ce !important; border-color: #6861ce !important;"
                                                            data-bs-toggle="tooltip" title="Duplikat Template">
                                                        <i class="fa fa-copy" style="font-size: 11px;"></i>
                                                    </button>
                                                </form>
                                         
                                                <a href="{{ route('tenant.import-templates.edit', $template->id) }}" 
                                                   class="btn btn-icon btn-round btn-warning btn-xs text-white" 
                                                   style="background-color: #ff9800 !important; border-color: #ff9800 !important;"
                                                   data-bs-toggle="tooltip" title="Edit Struktur Template">
                                                    <i class="fa fa-edit" style="font-size: 11px;"></i>
                                                </a>
                                         
                                                <form action="{{ route('tenant.import-templates.destroy', $template->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-icon btn-round btn-danger btn-xs btn-delete" data-bs-toggle="tooltip" title="Hapus Template">
                                                        <i class="fa fa-times" style="font-size: 11px;"></i>
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
</div>

<style>
    .card-round { border-radius: 12px !important; }
    .mt-n1 { margin-top: -10px !important; }
    .gap-1 { gap: 0.25rem !important; }
    
    #templateTable thead th {
        background-color: #f8fafc !important;
        color: #475569 !important;
        border-bottom: 2px solid #cbd5e1 !important;
        font-weight: 600 !important;
        text-transform: uppercase;
        font-size: 10px !important;
        letter-spacing: 0.5px;
        padding: 12px 16px !important;
    }
    #templateTable tbody td {
        padding: 14px 16px !important;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9 !important;
    }
    
    /* Mencegah dropdown menu terpotong/terpotong oleh container table-responsive */
    @media (min-width: 768px) {
        .table-responsive {
            overflow: visible !important;
        }
    }
</style>

{{-- MODAL DOWNLOAD FILTER --}}
<div class="modal fade" id="downloadFilterModal" tabindex="-1" aria-labelledby="downloadFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
            <div class="modal-header border-0 text-white py-3 px-4" style="border-top-left-radius: 15px; border-top-right-radius: 15px; background: linear-gradient(135deg, #1d7af3 0%, #1572e8 100%);">
                <h5 class="modal-title fw-bold" id="downloadFilterModalLabel">
                    <i class="fas fa-file-excel me-2"></i> Pengaturan Unduhan Data
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="downloadFilterForm" method="GET" action="">
                <input type="hidden" name="with_data" value="true">
                <div class="modal-body p-4" style="font-size: 13px;">
                    <p class="text-muted mb-3">Sesuaikan opsi filter dan nama file di bawah ini. Kosongkan filter jika ingin mengunduh seluruh data santri.</p>
                    
                    {{-- Nama Berkas --}}
                    <div class="form-group mb-3 px-0">
                        <label for="custom_filename" class="form-label fw-bold text-dark mb-1">Nama File Hasil Unduhan</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-success"><i class="fas fa-file-alt"></i></span>
                            <input type="text" class="form-control border-start-0" id="custom_filename" name="filename" placeholder="Contoh: Sensus_Santri_Komplek_A" style="font-size: 13px;">
                            <span class="input-group-text bg-light border-start-0 text-muted">.xlsx</span>
                        </div>
                        <small class="text-muted d-block mt-1">Nama file akan dibersihkan dari karakter ilegal.</small>
                    </div>

                    <div class="row">
                        {{-- Komplek --}}
                        <div class="col-md-6 mb-3">
                            <label for="filter_komplek" class="form-label fw-bold text-dark mb-1">Komplek</label>
                            <select class="form-select form-control" id="filter_komplek" name="komplek_id" style="font-size: 13px;">
                                <option value="">-- Semua Komplek --</option>
                                @foreach($kompleks as $komplek)
                                    <option value="{{ $komplek->id }}">{{ $komplek->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Kamar --}}
                        <div class="col-md-6 mb-3">
                            <label for="filter_kamar" class="form-label fw-bold text-dark mb-1">Kamar</label>
                            <select class="form-select form-control" id="filter_kamar" name="kamar_id" style="font-size: 13px;">
                                <option value="">-- Semua Kamar --</option>
                                @foreach($kamars as $kamar)
                                    <option value="{{ $kamar->id }}" data-komplek-id="{{ $kamar->komplek_id }}">{{ $kamar->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        {{-- Kelas --}}
                        <div class="col-md-6 mb-3">
                            <label for="filter_kelas" class="form-label fw-bold text-dark mb-1">Kelas</label>
                            <select class="form-select form-control" id="filter_kelas" name="kelas_id" style="font-size: 13px;">
                                <option value="">-- Semua Kelas --</option>
                                @foreach($kelas as $kls)
                                    <option value="{{ $kls->id }}">{{ $kls->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Jenis Kelamin --}}
                        <div class="col-md-6 mb-3">
                            <label for="filter_jk" class="form-label fw-bold text-dark mb-1">Jenis Kelamin</label>
                            <select class="form-select form-control" id="filter_jk" name="jenis_kelamin" style="font-size: 13px;">
                                <option value="">-- Semua Gender --</option>
                                <option value="L">Laki-laki (L)</option>
                                <option value="P">Perempuan (P)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="form-group mb-0 px-0">
                        <label for="filter_status" class="form-label fw-bold text-dark mb-1">Status Keaktifan</label>
                        <select class="form-select form-control" id="filter_status" name="status" style="font-size: 13px;">
                            <option value="">-- Semua Status --</option>
                            <option value="active">Active</option>
                            <option value="nonaktif">Nonaktif</option>
                            <option value="lulus">Lulus</option>
                            <option value="keluar">Keluar</option>
                            <option value="izin">Izin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light border btn-round btn-sm" data-bs-dismiss="modal" style="font-size: 12px; padding: 6px 16px;">Batal</button>
                    <button type="submit" class="btn btn-success btn-round btn-sm" style="font-size: 12px; padding: 6px 16px;">
                        <i class="fas fa-download me-1"></i> Download Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Inisialisasi DataTables (Sudah diperbaiki dari duplikasi baris)
        var table = $('#templateTable').DataTable({
            "order": [], 
            "pageLength": 10,
            "language": {
                "search": "",
                "searchPlaceholder": "Cari template...",
                "lengthMenu": "_MENU_",
                "info": "Menampilkan _START_ - _END_ dari _TOTAL_ template",
                "paginate": {
                    "next": '<i class="fa fa-chevron-right"></i>',
                    "previous": '<i class="fa fa-chevron-left"></i>'
                }
            },
            "dom": '<"d-flex flex-wrap justify-content-between align-items-center mb-3"lf>rt<"d-flex flex-wrap justify-content-between align-items-center mt-3"ip>'
        });

        // Event Delegation untuk tombol Delete (SweetAlert)
        $('body').on('click', '.btn-delete', function(e) {
            e.preventDefault();
            
            var form = $(this).closest("form");
            
            swal({
                title: "Hapus Template?",
                text: "Template yang dihapus tidak bisa dikembalikan.",
                icon: "warning",
                buttons: ["Batal", "Ya, Hapus"], 
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) { 
                    form.submit(); 
                }
            });
        });

        // Fix dropdown menu clipping in table-responsive (especially for mobile/smaller tables)
        $('#templateTable').on('show.bs.dropdown', '.dropdown', function() {
            $(this).closest('.table-responsive').css('overflow', 'visible');
        }).on('hide.bs.dropdown', '.dropdown', function() {
            $(this).closest('.table-responsive').css('overflow', 'auto');
        });

        // Re-init Tooltip dan Popover setelah ganti page datatable atau draw ulang
        table.on('draw', function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
            
            // Tambahkan inisialisasi popover ini
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl)
            });
        });

        // Jalankan inisialisasi popover pertama kali saat halaman di-load
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl)
        });

        // Notify Alert Success
        let msg = $('#success-trigger').data('message');
        if(msg) {
            $.notify({
                icon: 'fas fa-check-circle',
                title: 'Berhasil',
                message: msg,
            },{
                type: 'primary',
                placement: { from: "bottom", align: "right" },
                time: 1000, delay: 3000,
            });
        }

        // Tampilkan Modal Download dengan Filter
        $('.btn-trigger-download-modal').on('click', function(e) {
            e.preventDefault();
            var templateId = $(this).data('template-id');
            var templateName = $(this).data('template-name');
            
            // Set action URL pada form
            var urlPattern = "{{ route('tenant.import-templates.download', ':id') }}";
            $('#downloadFilterForm').attr('action', urlPattern.replace(':id', templateId));
            
            // Set default nama berkas
            var cleanTemplateName = templateName.replace(/\s+/g, '_');
            $('#custom_filename').val(cleanTemplateName + '_dengan_data');
            
            // Reset filters
            $('#filter_komplek').val('');
            $('#filter_kamar').val('');
            $('#filter_kelas').val('');
            $('#filter_jk').val('');
            $('#filter_status').val('');
            
            // Show all rooms initially
            $('#filter_kamar option').show();
            
            // Tampilkan modal
            $('#downloadFilterModal').modal('show');
        });

        // Filter Kamar secara dinamis berdasarkan Komplek yang dipilih
        $('#filter_komplek').on('change', function() {
            var komplekId = $(this).val();
            var $kamarSelect = $('#filter_kamar');
            
            // Reset pilihan kamar
            $kamarSelect.val('');
            
            if (komplekId === '') {
                // Tampilkan semua kamar jika komplek tidak dipilih
                $kamarSelect.find('option').show();
            } else {
                // Sembunyikan kamar yang tidak sesuai komplek, tampilkan yang sesuai
                $kamarSelect.find('option').each(function() {
                    var optionKomplekId = $(this).attr('data-komplek-id');
                    if (optionKomplekId === undefined || optionKomplekId === '' || optionKomplekId === komplekId) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
        });
    });
</script>
@endpush