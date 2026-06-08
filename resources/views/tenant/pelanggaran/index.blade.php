@extends('layouts.tenant')

@section('content')
<div class="container-fluid py-4" style="background-color: #f8f9fa; min-height: 100vh;">
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="bi bi-shield-exclamation text-danger"></i> Pusat Kedisiplinan & Pelanggaran Santri
            </h4>
            <p class="text-muted small mb-0">Panel khusus Kyai & Admin untuk mencatat, mengevaluasi, dan melacak akumulasi poin pelanggaran santri.</p>
        </div>
        @can('manage_pelanggaran')
        <button type="button" class="btn btn-danger shadow-sm fw-bold d-flex align-items-center gap-2 px-4 py-2 rounded-3" data-bs-toggle="modal" data-bs-target="#modalTambahPelanggaran">
            <i class="bi bi-plus-circle-fill"></i> Catat Pelanggaran Baru
        </button>
        @endcan
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-6 col-md-12">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-header bg-white border-0 pt-3 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-2 bg-warning-subtle rounded-3 text-warning d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-graph-up-arrow fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Tren Kasus Terbanyak</h6>
                            <small class="text-muted">Kasus dominan yang terekam bulan ini</small>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-3">
                    @forelse($topPelanggarans as $tp)
                        @php
                            $persentase = $totalKasusBulanIni > 0 ? ($tp->total_cases / $totalKasusBulanIni) * 100 : 0;
                        @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="fw-semibold text-secondary">{{ \Str::limit($tp->nama_kategori, 45) }}</span>
                                <span class="badge bg-dark rounded-pill fw-bold">{{ $tp->total_cases }} Kasus</span>
                            </div>
                            <div class="progress rounded-pill" style="height: 8px; background-color: #e9ecef;">
                                <div class="progress-bar bg-warning rounded-pill" role="progressbar" style="width: {{ $persentase }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            <i class="bi bi-patch-check text-success d-block mb-1 fs-3"></i>
                            Belum ada rekapitulasi data tren kasus bulan ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-12">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-header bg-white border-0 pt-3 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-2 bg-danger-subtle rounded-3 text-danger d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-exclamation-octagon fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Perhatian Poin Tertinggi</h6>
                            <small class="text-muted">Santri yang membutuhkan pembinaan intensif</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0 pt-2">
                    <div class="list-group list-group-flush">
                        @forelse($topSantris as $ts)
                            @php
                                $totalPoin = $ts->total_poin ?? 0;
                                $badgeStyle = $totalPoin >= 50 ? 'bg-danger text-white' : ($totalPoin >= 25 ? 'bg-warning text-dark' : 'bg-secondary text-white');
                            @endphp
                            <div class="list-group-item d-flex justify-content-between align-items-center py-2.5 px-4 bg-transparent border-0 border-bottom">
                                <div>
                                    <span class="fw-bold text-dark d-block mb-0" style="font-size: 0.9rem;">{{ $ts->nama_lengkap }}</span>
                                    <small class="text-muted">NIS: {{ $ts->nis ?? '-' }}</small>
                                </div>
                                <span class="badge {{ $badgeStyle }} fw-bold px-3 py-2 rounded-3 shadow-sm" style="font-size: 0.8rem;">
                                    {{ $totalPoin }} Poin
                                </span>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted small">
                                <i class="bi bi-heart-fill text-success d-block mb-1 fs-3"></i>
                                Alhamdulillah, poin seluruh santri bersih terawat.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="tablePelanggaranUtama" class="table table-hover align-middle mb-0 w-100">
                    <thead class="bg-light text-dark border-bottom fw-bold text-uppercase small">
                        <tr>
                            <th class="ps-3 py-3">Tanggal</th>
                            <th class="py-3">Identitas Santri</th>
                            <th class="py-3" style="width: 25%;">Detail Pelanggaran</th>
                            <th class="py-3 text-center">Poin</th>
                            <th class="py-3">Petugas</th>
                            <th class="py-3 text-center">Bukti</th>
                            @can('manage_pelanggaran')
                            <th class="pe-3 py-3 text-end">Aksi</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pelanggarans as $item)
                            <tr class="border-bottom-light">
                                <td class="ps-3">
                                    <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</div>
                                    <small class="text-muted" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $item->santri->nama_lengkap ?? 'N/A' }}</div>
                                    <span class="badge bg-light text-secondary border rounded-pill mt-1" style="font-size: 0.72rem;">NIS: {{ $item->santri->nis ?? '-' }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark mb-1" style="font-size: 0.88rem;">{{ $item->judul_pelanggaran }}</div>
                                    @if($item->kategoriPelanggaran)
                                        <span class="badge bg-danger-subtle text-danger rounded-pill" style="font-size: 0.7rem;">
                                            {{ $item->kategoriPelanggaran->nama_pelanggaran }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger text-white fw-bold px-2.5 py-1.5 rounded-2">
                                        +{{ $item->poin }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-secondary" style="font-size: 0.85rem;">{{ $item->pencatat->name ?? 'Admin' }}</div>
                                    <small class="text-muted" style="font-size: 0.75rem;">via {{ $item->kategori_sumber ?? 'Manual' }}</small>
                                </td>
                                <td class="text-center">
                                    @if($item->foto_bukti)
                                        <img src="{{ asset('storage/' . $item->foto_bukti) }}" 
                                             class="img-thumbnail rounded-3 img-preview-trigger" 
                                             style="width: 45px; height: 45px; object-fit: cover; cursor: pointer;" 
                                             data-src="{{ asset('storage/' . $item->foto_bukti) }}">
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                @can('manage_pelanggaran')
                                <td class="pe-3 text-end">
                                    <div class="d-inline-flex gap-2">
                                        <button type="button" class="btn btn-primary btn-sm rounded-3 px-2.5 py-1.5 d-flex align-items-center gap-1 btn-edit-pelanggaran" 
                                                data-id="{{ $item->id }}"
                                                data-judul="{{ $item->judul_pelanggaran }}"
                                                data-poin="{{ $item->poin }}"
                                                data-catatan="{{ $item->catatan_detail }}">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </button>
                                        
                                        <form action="{{ route('tenant.pelanggaran.destroy', $item->id) }}" method="POST" class="m-0 d-inline form-hapus-pelanggaran">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-sm rounded-3 px-2.5 py-1.5 d-flex align-items-center gap-1 btn-trigger-delete">
                                                <i class="bi bi-trash3"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                @endcan
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahPelanggaran" data-bs-backdrop="static" tabindex="-1" aria-labelledby="modalTambahPelanggaranLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-dark text-white border-0 py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                    <div>
                        <h5 class="modal-title fw-bold fs-5 mb-0">Form Catat Pelanggaran Santri</h5>
                        <small style="font-size: 0.75rem; color: #adb5bd;">Pilih beberapa santri sekaligus dan lengkapi data</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('tenant.pelanggaran.store') }}" method="POST" enctype="multipart/form-data" class="mb-0">
                @csrf
                <div class="modal-body p-4 bg-white" style="max-height: 65vh; overflow-y: auto;">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark d-flex justify-content-between align-items-center mb-2">
                            <span><i class="bi bi-people-fill text-primary me-1"></i> Pilih Santri Terlibat <span class="text-danger">*</span></span>
                            <span class="badge bg-primary text-white fw-bold id-terpilih-count" style="font-size: 0.75rem;">0 Terpilih</span>
                        </label>

                        <div class="input-group mb-2 shadow-sm">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" id="cariSantriInput" class="form-control bg-light border-start-0 ps-0" placeholder="Ketik nama atau NIS santri untuk mencari...">
                        </div>

                        <div class="border rounded-3 p-2 bg-light" style="max-height: 180px; overflow-y: auto;" id="listSantriContainer">
                            @foreach($santris as $santri)
                                <div class="form-check p-2 rounded item-santri-box border-bottom border-white" data-search="{{ strtolower($santri->nama_lengkap) }} {{ $santri->nis }}">
                                    <input class="form-check-input ms-1 me-2 cb-santri" type="checkbox" name="santri_ids[]" value="{{ $santri->id }}" id="chk-{{ $santri->id }}" style="width: 17px; height: 17px; cursor: pointer;">
                                    <label class="form-check-label w-100 text-dark" for="chk-{{ $santri->id }}" style="cursor: pointer; user-select: none;">
                                        <strong style="font-size: 0.88rem;">{{ $santri->nama_lengkap }}</strong>
                                        <span class="text-muted small ms-1">(NIS: {{ $santri->nis ?? '-' }})</span>
                                        @if($santri->kamar_id)
                                            <span class="badge bg-secondary-subtle text-dark border ms-1" style="font-size: 0.65rem;">Kamar {{ $santri->kamar_id }}</span>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="p-3 bg-light border rounded-3 mb-4 d-none" id="boxReviewSantri">
                        <small class="fw-bold text-secondary d-block mb-2"><i class="bi bi-check2-square text-success"></i> Keterangan Santri Terpilih:</small>
                        <div id="containerReviewBadges" class="d-flex flex-wrap gap-1.5"></div>
                    </div>

                    <div style="border-top: 2px dashed #e9ecef;" class="my-4"></div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small mb-1">Kategori Aturan Acuan</label>
                            <select class="form-select bg-light border" id="select-kategori" name="kategori_id">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategoris as $kat)
                                    @php
                                        $namaKat = $kat->nama_kategori ?? $kat->nama_pelanggaran;
                                        $poinKat = $kat->bobot_poin ?? $kat->poin ?? 0;
                                    @endphp
                                    <option value="{{ $kat->id }}" data-poin="{{ $poinKat }}" data-nama="{{ $namaKat }}">
                                        {{ $namaKat }} (+{{ $poinKat }} Poin)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small mb-1">Tanggal Melanggar <span class="text-danger">*</span></label>
                            <input type="date" class="form-control bg-light border" name="tanggal" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark small mb-1">Judul / Bentuk Nyata Kasus <span class="text-danger">*</span></label>
                            <input type="text" class="form-control border" id="judul_pelanggaran" name="judul_pelanggaran" placeholder="Contoh: Ketahuan merokok di area kamar mandi" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark small mb-1">Bobot Input Poin <span class="text-danger">*</span></label>
                            <input type="number" class="form-control fw-bold text-danger bg-danger-subtle bg-opacity-25 border fs-5" id="poin_pelanggaran" name="poin" min="0" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold text-dark small mb-1">Unggah Lampiran Bukti Foto</label>
                            <input type="file" class="form-control border" name="foto_bukti" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark small mb-1">Catatan Kronologi Kejadian</label>
                            <textarea class="form-control border" name="catatan_detail" rows="3" placeholder="Tulis rincian kronologi singkat..."></textarea>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4">
                    <button type="button" class="btn btn-secondary px-4 fw-bold rounded-3" data-bs-dismiss="modal">Batalkan</button>
                    <button type="submit" class="btn btn-danger px-4 shadow-sm fw-bold rounded-3">Simpan Rekaman</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditPelanggaran" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-primary text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold fs-5 mb-0"><i class="bi bi-pencil-square"></i> Koreksi Data Rekam Kasus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-edit-pelanggaran" method="POST" class="mb-0">
                @csrf
                @method('PUT')
                <div class="modal-body p-4 bg-white">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Judul / Bentuk Pelanggaran</label>
                        <input type="text" class="form-control" id="edit-judul" name="judul_pelanggaran" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Koreksi Nilai Poin</label>
                        <input type="number" class="form-control fw-bold text-primary bg-primary-subtle bg-opacity-25" id="edit-poin" name="poin" min="0" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold text-dark small">Alasan Koreksi</label>
                        <textarea class="form-control" id="edit-catatan" name="catatan_detail" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4">
                    <button type="button" class="btn btn-secondary px-4 fw-bold rounded-3" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold rounded-3">Update Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPreviewGambar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 bg-transparent">
            <div class="modal-header border-0 p-0 justify-content-end mb-2">
                <button type="button" class="btn-close btn-close-white bg-dark p-2 rounded-circle" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center">
                <img src="" id="img-target-popup" class="img-fluid rounded-3 shadow-lg border border-2 border-white" style="max-height: 80vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        .border-bottom-light { border-bottom: 1px solid #f1f3f5 !important; }
        .bg-warning-subtle { background-color: #fff3cd !important; }
        .bg-danger-subtle { background-color: #f8d7da !important; }
        .bg-primary-subtle { background-color: #cfe2ff !important; }
        
        .dataTables_wrapper .dataTables_filter input {
            background-color: #ffffff;
            border: 1px solid #ced4da;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.88rem;
        }
        table.dataTable th {
            font-weight: 700 !important;
            background-color: #f8f9fa !important;
            color: #343a40 !important;
        }
        .item-santri-box:hover {
            background-color: #e9ecef !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.3/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.0.3/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    $(document).ready(function() {
        
        // SWEETALERT NOTIFIKASI SUKSES
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                confirmButtonColor: '#3085d6',
                timer: 3000
            });
        @endif

        // INITIALIZE DATATABLES
        $('#tablePelanggaranUtama').DataTable({
            "language": {
                "search": "Cari data:",
                "lengthMenu": "Tampilkan _MENU_ baris",
                "zeroRecords": "Data tidak ditemukan.",
                "info": "Halaman _PAGE_ dari _PAGES_",
                "paginate": { "next": "Maju", "previous": "Mundur" }
            },
            "pageLength": 10,
            "order": [[0, "desc"]]
        });

        // LIVE FILTER PENCARIAN SANTRI
        $('#cariSantriInput').on('input', function() {
            let value = $(this).val().toLowerCase();
            $('.item-santri-box').each(function() {
                let text = $(this).data('search');
                if (text.includes(value)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // DETEKSI CENTANG & UPDATE BADGE REVIEW
        $(document).on('change', '.cb-santri', function() {
            let checkedItems = $('.cb-santri:checked');
            let count = checkedItems.length;
            
            $('.id-terpilih-count').text(count + ' Terpilih');
            let reviewBox = $('#boxReviewSantri');
            let badgeContainer = $('#containerReviewBadges');
            badgeContainer.empty();

            if (count > 0) {
                reviewBox.removeClass('d-none');
                checkedItems.each(function() {
                    let labelText = $(this).siblings('label').find('strong').text();
                    badgeContainer.append(`
                        <span class="badge bg-dark text-white px-2.5 py-1.5 rounded-2 d-inline-flex align-items-center gap-1" style="font-size:0.8rem;">
                            <i class="bi bi-person-fill text-danger"></i> ${labelText}
                        </span>
                    `);
                });
            } else {
                reviewBox.addClass('d-none');
            }
        });

        // AUTOFILL KATEGORI ATURAN
        $('#select-kategori').on('change', function() {
            let option = $(this).find(':selected');
            let nama = option.data('nama');
            let poin = option.data('poin');

            if (nama) {
                $('#judul_pelanggaran').val(nama);
                $('#poin_pelanggaran').val(poin);
            } else {
                $('#judul_pelanggaran').val('');
                $('#poin_pelanggaran').val('');
            }
        });

        // MODAL PREVIEW FOTO
        $(document).on('click', '.img-preview-trigger', function() {
            let src = $(this).data('src');
            $('#img-target-popup').attr('src', src);
            $('#modalPreviewGambar').modal('show');
        });

        // ACTION MODAL EDIT
        $(document).on('click', '.btn-edit-pelanggaran', function() {
            let id = $(this).data('id');
            let judul = $(this).data('judul');
            let poin = $(this).data('poin');
            let catatan = $(this).data('catatan');

            let url = "{{ route('tenant.pelanggaran.update', ':id') }}".replace(':id', id);
            $('#form-edit-pelanggaran').attr('action', url);

            $('#edit-judul').val(judul);
            $('#edit-poin').val(poin);
            $('#edit-catatan').val(catatan);

            $('#modalEditPelanggaran').modal('show');
        });

        // SWEETALERT KONFIRMASI HAPUS
        $(document).on('click', '.btn-trigger-delete', function(e) {
            e.preventDefault();
            let form = $(this).closest('.form-hapus-pelanggaran');

            Swal.fire({
                title: 'Apakah Anda Yakin?',
                text: "Data catatan pelanggaran santri ini akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // RESET FORM SAAT CLOSED
        $('#modalTambahPelanggaran').on('hidden.bs.modal', function () {
            $('.cb-santri').prop('checked', false).trigger('change');
            $('#cariSantriInput').val('').trigger('input');
            $('#judul_pelanggaran').val('');
            $('#poin_pelanggaran').val('');
            $('#select-kategori').val('');
        });
    });
    </script>
@endpush