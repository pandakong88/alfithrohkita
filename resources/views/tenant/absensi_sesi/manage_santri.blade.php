@extends('layouts.tenant')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Kelola Peserta: {{ $sesi->nama_sesi }}</h4>
    </div>

    <form action="{{ route('tenant.absensi-sesi.update-santri', $sesi->id) }}" method="POST">
        @csrf
        <div class="row">
            <!-- Sidebar Filter -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title"><i class="fa fa-filter"></i> Filter</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Cari Nama</label>
                            <input type="text" id="searchNama" class="form-control" placeholder="Ketik nama...">
                        </div>
                        <div class="form-group">
                            <label>Kelas</label>
                            <select id="filterKelas" class="form-control filter-input">
                                <option value="">Semua Kelas</option>
                                @foreach($kelass as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Komplek</label>
                            <select id="filterKomplek" class="form-control filter-input">
                                <option value="">Semua Komplek</option>
                                @foreach($kompleks as $kp)
                                    <option value="{{ $kp->id }}">{{ $kp->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Kamar</label>
                            <select id="filterKamar" class="form-control filter-input">
                                <option value="">Semua Kamar</option>
                                @foreach($kamars as $km)
                                    <option value="{{ $km->id }}" data-komplek="{{ $km->komplek_id }}">{{ $km->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <hr>
                        <button type="button" class="btn btn-outline-primary btn-block" onclick="selectAllVisible()">
                            <i class="fa fa-check-double"></i> Pilih Semua Terfilter
                        </button>
                    </div>
                </div>
            </div>

            <!-- Daftar Santri -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h4 class="card-title">Daftar Santri</h4>
                        <span class="badge badge-info ml-2" id="countSelected">{{ count($selectedSantri) }} Terpilih</span>
                        <button type="submit" class="btn btn-primary ml-auto">Simpan Perubahan</button>
                    </div>
                    <div class="card-body" style="max-height: 70vh; overflow-y: auto;">
                        <div class="row" id="santriContainer">
                            @foreach($santris as $s)
                            <div class="col-md-6 col-lg-4 mb-3 santri-item" 
                                 data-nama="{{ strtolower($s->nama_lengkap ?? '') }}" {{-- Gunakan nama_lengkap --}}
                                 data-kelas="{{ $s->kelas_id }}"
                                 data-komplek="{{ $s->kamar->komplek_id ?? '' }}"
                                 data-kamar="{{ $s->kamar_id }}">
                                
                                <div class="p-2 border rounded d-flex align-items-center item-box shadow-sm">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="santri_ids[]" value="{{ $s->id }}" 
                                               class="custom-control-input chk-santri" id="santri-{{ $s->id }}"
                                               {{ in_array($s->id, $selectedSantri) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="santri-{{ $s->id }}"></label>
                                    </div>
                                    <div class="ml-2 overflow-hidden" onclick="document.getElementById('santri-{{ $s->id }}').click()" style="cursor:pointer">
                                        {{-- Pastikan ini nama_lengkap atau kolom yang benar di tabel santri --}}
                                        <div class="text-truncate font-weight-bold">{{ $s->nama_lengkap }}</div>
                                        <small class="text-muted d-block">{{ $s->kelas->nama ?? 'Tanpa Kelas' }}</small>
                                        <small class="text-primary">{{ $s->kamar->nama ?? 'Tanpa Kamar' }}</small>
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
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Filter Kamar berdasarkan Komplek
        $('#filterKomplek').on('change', function() {
            let komplekId = $(this).val();
            $('#filterKamar option').each(function() {
                let itemKomplek = $(this).data('komplek');
                if (komplekId == "" || itemKomplek == komplekId || $(this).val() == "") {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            $('#filterKamar').val("");
            applyFilter();
        });

        // Event saat input filter berubah
        $('.filter-input, #searchNama').on('keyup change', function() {
            applyFilter();
        });

        // Hitung terpilih saat checkbox di klik
        $(document).on('change', '.chk-santri', function() {
            updateCount();
        });
    });

    function applyFilter() {
        let nama = $('#searchNama').val().toLowerCase();
        let kelas = $('#filterKelas').val();
        let komplek = $('#filterKomplek').val();
        let kamar = $('#filterKamar').val();

        $('.santri-item').each(function() {
            let itemNama = $(this).data('nama');
            let itemKelas = $(this).data('kelas');
            let itemKomplek = $(this).data('komplek');
            let itemKamar = $(this).data('kamar');

            let matchNama = itemNama.includes(nama);
            let matchKelas = kelas == "" || itemKelas == kelas;
            let matchKomplek = komplek == "" || itemKomplek == komplek;
            let matchKamar = kamar == "" || itemKamar == kamar;

            if (matchNama && matchKelas && matchKomplek && matchKamar) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    function selectAllVisible() {
        $('.santri-item:visible').each(function() {
            $(this).find('.chk-santri').prop('checked', true);
        });
        updateCount();
    }

    function updateCount() {
        let count = $('.chk-santri:checked').length;
        $('#countSelected').text(count + ' Terpilih');
    }
</script>

<style>
    .item-box:hover { background-color: #f0f7ff; border-color: #007bff !important; }
    .custom-checkbox { transform: scale(1.2); }
</style>
@endpush