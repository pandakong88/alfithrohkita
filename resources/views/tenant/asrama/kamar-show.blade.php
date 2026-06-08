@extends('layouts.tenant')

@section('title', 'Detail Kamar - ' . $kamar->nama)

@section('content')
{{-- HEADER / BREADCRUMB --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Kamar: {{ $kamar->nama }}</h3>
        <p class="text-muted small mb-0">
            <a href="{{ route('tenant.kamar.index') }}" class="text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Asrama
            </a>
            <span class="mx-2">/</span>
            Komplek {{ $kamar->kompleks->nama }}
        </p>
    </div>
    @can('manage_asrama')
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-round" data-bs-toggle="modal" data-bs-target="#modalAddLemari">
            <i class="fas fa-box me-1"></i> Tambah Lemari / Loker
        </button>
        <button type="button" class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#modalAddOccupant">
            <i class="fas fa-user-plus me-1"></i> Tambah Penghuni
        </button>
    </div>
    @endcan
</div>

@if(session('success'))
    <div id="kamar-success-message" data-message="{{ session('success') }}"></div>
@endif
@if(session('error'))
    <div id="kamar-error-message" data-message="{{ session('error') }}"></div>
@endif

{{-- CARD SUMMARY & TABS --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="fw-bold mb-0 text-dark">Informasi Kamar</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush" style="font-size: 0.9rem;">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                        <span class="text-muted"><i class="fas fa-building me-2"></i>Komplek Gedung</span>
                        <span class="fw-bold text-dark">{{ $kamar->kompleks->nama }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                        <span class="text-muted"><i class="fas fa-door-open me-2"></i>Nama Kamar</span>
                        <span class="fw-bold text-dark">{{ $kamar->nama }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                        <span class="text-muted"><i class="fas fa-users me-2"></i>Hunian</span>
                        <span class="fw-bold text-dark">{{ $kamar->santris->count() }} / {{ $kamar->kapasitas }} Santri</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                        <span class="text-muted"><i class="fas fa-percentage me-2"></i>Tingkat Keterisian</span>
                        @php
                            $rate = $kamar->kapasitas > 0 ? round(($kamar->santris->count() / $kamar->kapasitas) * 100) : 0;
                        @endphp
                        <span class="badge {{ $rate >= 90 ? 'bg-danger' : ($rate >= 60 ? 'bg-warning' : 'bg-success') }} px-2.5 rounded-pill">
                            {{ $rate }}% Penuh
                        </span>
                    </li>
                </ul>

                <div class="mt-4">
                    <div class="progress" style="height: 8px; border-radius: 4px;">
                        <div class="progress-bar {{ $rate >= 90 ? 'bg-danger' : ($rate >= 60 ? 'bg-warning' : 'bg-success') }}" role="progressbar" style="width: {{ $rate }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0 py-2">
                <ul class="nav nav-pills nav-primary nav-pills-no-bd" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active fw-bold" id="pills-penghuni-tab" data-bs-toggle="pill" href="#pills-penghuni" role="tab" aria-controls="pills-penghuni" aria-selected="true">
                            <i class="fas fa-user-graduate me-2"></i>Daftar Penghuni ({{ $kamar->santris->count() }})
                        </a>
                    </li>
                    <li class="nav-link-separator" style="width: 1px; background-color: #e2e8f0; margin: 8px 15px;"></li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold" id="pills-lemari-tab" data-bs-toggle="pill" href="#pills-lemari" role="tab" aria-controls="pills-lemari" aria-selected="false">
                            <i class="fas fa-box-open me-2"></i>Inventaris Lemari & Loker
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body pt-1">
                <div class="tab-content mt-2-5" id="pills-tabContent">
                    {{-- TAB PENGHUNI --}}
                    <div class="tab-pane fade show active" id="pills-penghuni" role="tabpanel" aria-labelledby="pills-penghuni-tab">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" style="font-size: 0.85rem;">
                                <thead class="table-light text-muted font-xs text-uppercase">
                                    <tr>
                                        <th class="ps-3">Nama Lengkap</th>
                                        <th>NIS</th>
                                        <th>Jenis Kelamin</th>
                                        @can('manage_asrama')
                                        <th class="text-center pe-3">Aksi</th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($kamar->santris as $santri)
                                        <tr>
                                            <td class="ps-3 fw-bold text-dark">{{ $santri->nama_lengkap }}</td>
                                            <td><code>{{ $santri->nis }}</code></td>
                                            <td>{{ $santri->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                            @can('manage_asrama')
                                            <td class="text-center pe-3">
                                                <form action="{{ route('tenant.kamar.occupant.remove', [$kamar, $santri]) }}" method="POST" class="d-inline remove-occupant-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-icon btn-link btn-xs text-danger btn-remove-occupant" title="Keluarkan dari Kamar">
                                                        <i class="fas fa-user-times"></i> Keluarkan
                                                    </button>
                                                </form>
                                            </td>
                                            @endcan
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-5">
                                                <i class="fas fa-users fa-2x mb-3 text-muted d-block opacity-50"></i>
                                                Belum ada santri di kamar ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TAB LEMARI --}}
                    <div class="tab-pane fade" id="pills-lemari" role="tabpanel" aria-labelledby="pills-lemari-tab">
                        @forelse($kamar->lemaris as $lemari)
                            <div class="card border border-light shadow-none mb-4">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-3">
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">
                                            <i class="fas fa-box me-2 text-primary"></i>{{ $lemari->nama }}
                                            <span class="badge bg-secondary ms-2" style="font-size: 10px;">
                                                {{ ucfirst(str_replace('_', ' ', $lemari->tipe)) }}
                                            </span>
                                        </h6>
                                    </div>
                                    @can('manage_asrama')
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-icon btn-link btn-xs text-warning p-0" 
                                                onclick="editLemari({{ $lemari->id }}, '{{ addslashes($lemari->nama) }}', '{{ $lemari->tipe }}', {{ $lemari->jumlah_slot }})" 
                                                title="Edit Lemari">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('tenant.lemari.destroy', $lemari) }}" method="POST" class="d-inline delete-lemari-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-icon btn-link btn-xs text-danger p-0 btn-delete-lemari" title="Hapus Lemari">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                    @endcan
                                </div>
                                <div class="card-body p-3">
                                    {{-- SLOT VISUAL GRID --}}
                                    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 g-2">
                                        @foreach($lemari->slots as $slot)
                                            @php
                                                $boxBg = 'bg-success-light border-success text-success';
                                                $icon = 'fa-check-circle';
                                                if($slot->status === 'dipakai') {
                                                    $boxBg = 'bg-primary-light border-primary text-primary';
                                                    $icon = 'fa-user-check';
                                                } elseif($slot->status === 'rusak') {
                                                    $boxBg = 'bg-danger-light border-danger text-danger';
                                                    $icon = 'fa-times-circle';
                                                } elseif($slot->status === 'barang') {
                                                    $boxBg = 'bg-warning-light border-warning text-warning';
                                                    $icon = 'fa-box-open';
                                                }
                                            @endphp
                                            <div class="col">
                                                <div class="card h-100 border text-center p-2.5 slot-card {{ $boxBg }}" 
                                                     style="@can('manage_asrama') cursor: pointer; @endcan border-radius: 8px; transition: transform 0.15s ease;"
                                                     @can('manage_asrama')
                                                     onclick="openSlotModal({{ $slot->id }}, {{ $slot->nomor_slot }}, '{{ $slot->status }}', '{{ $slot->santri_id }}', '{{ addslashes($slot->keterangan) }}')"
                                                     @endcan>
                                                    <div class="fw-bold small">Slot #{{ $slot->nomor_slot }}</div>
                                                    <div class="my-1.5"><i class="fas {{ $icon }} fa-lg"></i></div>
                                                    <div class="text-xs text-truncate text-dark fw-semibold" style="max-width: 100%;" title="{{ $slot->santri->nama_lengkap ?? '' }}">
                                                        {{ $slot->santri->nama_lengkap ?? ucfirst($slot->status) }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-box fa-2x mb-3 text-muted d-block opacity-50"></i>
                                Belum ada lemari/rak inventaris di kamar ini.
                                <br>
                                @can('manage_asrama')
                                <button class="btn btn-primary btn-sm btn-round mt-3" data-bs-toggle="modal" data-bs-target="#modalAddLemari">
                                    Tambah Lemari Pertama <i class="fas fa-plus ms-1"></i>
                                </button>
                                @endcan
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODALS --}}

{{-- Modal Tambah Penghuni --}}
<div class="modal fade" id="modalAddOccupant" tabindex="-1" aria-labelledby="modalAddOccupantLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('tenant.kamar.occupant.add', $kamar) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalAddOccupantLabel"><i class="fas fa-user-plus me-2"></i>Daftarkan Penghuni</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_occupant_santri" class="form-label fw-bold">Pilih Santri <span class="text-danger">*</span></label>
                        <select class="form-select select2-modal" id="add_occupant_santri" name="santri_id" required style="width: 100%;">
                            <option value="">-- Cari Nama/NIS Santri --</option>
                            @foreach($availableSantris as $santri)
                                <option value="{{ $santri->id }}">{{ $santri->nama_lengkap }} ({{ $santri->nis }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hanya menampilkan santri aktif yang belum mendapatkan kamar.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Daftarkan</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Tambah Lemari --}}
<div class="modal fade" id="modalAddLemari" tabindex="-1" aria-labelledby="modalAddLemariLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('tenant.lemari.store') }}" method="POST">
            @csrf
            <input type="hidden" name="kamar_id" value="{{ $kamar->id }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalAddLemariLabel"><i class="fas fa-box me-2"></i>Tambah Lemari/Loker</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_lemari_nama" class="form-label fw-bold">Nama Lemari/Loker <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add_lemari_nama" name="nama" placeholder="Contoh: Lemari Kayu, Loker Besi A, dll." required>
                    </div>
                    <div class="mb-3">
                        <label for="add_lemari_tipe" class="form-label fw-bold">Tipe Lemari <span class="text-danger">*</span></label>
                        <select class="form-select" id="add_lemari_tipe" name="tipe" required>
                            <option value="lemari">Lemari Pakaian</option>
                            <option value="rak_buku">Rak Buku</option>
                            <option value="rak_barang">Rak Barang</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="add_lemari_jumlah_slot" class="form-label fw-bold">Jumlah Laci/Slot <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="add_lemari_jumlah_slot" name="jumlah_slot" min="1" max="100" placeholder="Contoh: 4" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Lemari --}}
<div class="modal fade" id="modalEditLemari" tabindex="-1" aria-labelledby="modalEditLemariLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formEditLemari" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalEditLemariLabel"><i class="fas fa-edit me-2"></i>Edit Lemari</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_lemari_nama" class="form-label fw-bold">Nama Lemari/Loker <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_lemari_nama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_lemari_tipe" class="form-label fw-bold">Tipe Lemari <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_lemari_tipe" name="tipe" required>
                            <option value="lemari">Lemari Pakaian</option>
                            <option value="rak_buku">Rak Buku</option>
                            <option value="rak_barang">Rak Barang</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_lemari_jumlah_slot" class="form-label fw-bold">Jumlah Laci/Slot <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="edit_lemari_jumlah_slot" name="jumlah_slot" min="1" max="100" required>
                        <small class="text-muted text-xs">Jika jumlah slot dikurangi, sistem akan memvalidasi apakah slot tersebut sedang kosong atau tidak.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Perbarui</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Pengaturan Slot (Loker) --}}
<div class="modal fade" id="modalSlotConfig" tabindex="-1" aria-labelledby="modalSlotConfigLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formSlotConfig" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalSlotConfigLabel">Konfigurasi Laci/Slot #<span id="label_nomor_slot"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="slot_status" class="form-label fw-bold">Status Slot <span class="text-danger">*</span></label>
                        <select class="form-select" id="slot_status" name="status" required onchange="onSlotStatusChange()">
                            <option value="kosong">Kosong (Tersedia)</option>
                            <option value="dipakai">Dipakai (Oleh Santri)</option>
                            <option value="rusak">Rusak / Tidak Bisa Digunakan</option>
                            <option value="barang">Untuk Barang Umum</option>
                        </select>
                    </div>
                    <div class="mb-3" id="wrapper_santri_id" style="display: none;">
                        <label for="slot_santri_id" class="form-label fw-bold">Terapkan ke Penghuni Kamar</label>
                        <select class="form-select" id="slot_santri_id" name="santri_id">
                            <option value="">-- Pilih Penghuni Kamar --</option>
                            @foreach($kamar->santris as $resident)
                                <option value="{{ $resident->id }}">{{ $resident->nama_lengkap }} ({{ $resident->nis }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hanya dapat dialokasikan kepada santri yang terdaftar aktif menghuni Kamar ini.</small>
                    </div>
                    <div class="mb-3">
                        <label for="slot_keterangan" class="form-label fw-bold">Keterangan Tambahan</label>
                        <textarea class="form-control" id="slot_keterangan" name="keterangan" rows="3" placeholder="Contoh: Kunci hilang, diisi buku, dll."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .font-xs { font-size: 0.7rem; letter-spacing: 0.05em; }
    
    /* Light colored boxes */
    .bg-success-light { background-color: #f0fdf4; border-color: #bbf7d0 !important; }
    .bg-primary-light { background-color: #eff6ff; border-color: #bfdbfe !important; }
    .bg-danger-light { background-color: #fef2f2; border-color: #fecaca !important; }
    .bg-warning-light { background-color: #fffbeb; border-color: #fde68a !important; }
    
    .slot-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
</style>
@endsection

@push('scripts')
<script>
    // Initialize Modal triggers & variables
    function editLemari(id, nama, tipe, jumlahSlot) {
        var actionUrl = "{{ route('tenant.lemari.update', ':id') }}".replace(':id', id);
        $('#formEditLemari').attr('action', actionUrl);
        $('#edit_lemari_nama').val(nama);
        $('#edit_lemari_tipe').val(tipe);
        $('#edit_lemari_jumlah_slot').val(jumlahSlot);
        var modal = new bootstrap.Modal(document.getElementById('modalEditLemari'));
        modal.show();
    }

    function openSlotModal(id, nomorSlot, status, santriId, keterangan) {
        var actionUrl = "{{ route('tenant.lemari-slot.update', ':id') }}".replace(':id', id);
        $('#formSlotConfig').attr('action', actionUrl);
        $('#label_nomor_slot').text(nomorSlot);
        $('#slot_status').val(status);
        $('#slot_santri_id').val(santriId || '');
        $('#slot_keterangan').val(keterangan || '');
        
        onSlotStatusChange();
        
        var modal = new bootstrap.Modal(document.getElementById('modalSlotConfig'));
        modal.show();
    }

    function onSlotStatusChange() {
        var status = $('#slot_status').val();
        if(status === 'dipakai') {
            $('#wrapper_santri_id').slideDown();
        } else {
            $('#wrapper_santri_id').slideUp();
            // Clear santri_id selection when not dipakai
            $('#slot_santri_id').val('');
        }
    }

    $(document).ready(function() {
        // Success Notifications
        var successMsg = $('#kamar-success-message').data('message');
        if(successMsg) {
            swal({
                title: 'Berhasil!',
                text: successMsg,
                icon: 'success',
                timer: 2000,
                buttons: false
            });
            $.notify({
                icon: 'fa fa-check',
                message: successMsg
            },{
                type: 'success',
                placement: { from: 'bottom', align: 'right' },
                delay: 2000,
                timer: 500
            });
        }

        // Error Notifications
        var errorMsg = $('#kamar-error-message').data('message');
        if(errorMsg) {
            swal({
                title: 'Gagal!',
                text: errorMsg,
                icon: 'error',
                buttons: {
                    confirm: {
                        className: 'btn btn-danger'
                    }
                }
            });
        }

        // SweetAlert Confirm for removal of occupant
        $('.btn-remove-occupant').on('click', function(e) {
            e.preventDefault();
            var form = $(this).closest('form');
            swal({
                title: 'Keluarkan santri dari kamar?',
                text: 'Data santri akan dipindahkan kembali ke daftar tanpa kamar. Riwayat mutasi kamar akan tetap tersimpan!',
                icon: 'warning',
                buttons: {
                    cancel: {
                        text: 'Batal',
                        visible: true,
                        className: 'btn btn-secondary'
                    },
                    confirm: {
                        text: 'Ya, Keluarkan',
                        visible: true,
                        className: 'btn btn-danger'
                    }
                },
                dangerMode: true,
            }).then(function(confirm) {
                if (confirm) {
                    form.submit();
                }
            });
        });

        // SweetAlert Confirm for deleting lemari
        $('.btn-delete-lemari').on('click', function(e) {
            e.preventDefault();
            var form = $(this).closest('form');
            swal({
                title: 'Hapus lemari ini?',
                text: 'Hapus lemari beserta seluruh data laci/slot di dalamnya! Data laci yang terisi akan dihapus!',
                icon: 'warning',
                buttons: {
                    cancel: {
                        text: 'Batal',
                        visible: true,
                        className: 'btn btn-secondary'
                    },
                    confirm: {
                        text: 'Ya, Hapus',
                        visible: true,
                        className: 'btn btn-danger'
                    }
                },
                dangerMode: true,
            }).then(function(confirm) {
                if (confirm) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
