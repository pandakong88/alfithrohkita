@extends('layouts.tenant')

@section('content')
<div class="page-inner" style="background: #f8fafc; min-height: 100vh;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-round border-0 shadow-sm">
                <div class="card-header bg-primary text-white p-4" style="border-radius: 15px 15px 0 0;">
                    <h3 class="fw-bold mb-0">Buat Perizinan Baru</h3>
                    <p class="text-white-50 mb-0">Lengkapi data untuk mencetak surat izin santri.</p>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('tenant.perizinan.store') }}">
                        @csrf

                        <div class="row">
                            {{-- TEMPLATE --}}
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Jenis Perizinan (Template)</label>
                                <select id="templateSelect" name="template_perizinan_id" class="form-control" required>
                                    <option value="" data-variables="[]">-- Pilih Jenis Izin --</option>
                                    @foreach($templates as $t)
                                        <option value="{{ $t->id }}" data-variables="{{ json_encode($t->required_variables) }}">
                                            {{ $t->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- SANTRI --}}
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Nama Santri</label>
                                <select id="santriSelect" name="santri_id" class="form-control select2" required>
                                    <option value="">-- Cari Santri --</option>
                                    @foreach($santris as $s)
                                        <option value="{{ $s->id }}">
                                            {{ $s->nama_lengkap }} ({{ $s->nis }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr>

                        {{-- DYNAMIC VARIABLES CONTAINER --}}
                        <div id="dynamicContainer" class="p-3 mb-4 rounded" style="background: #f0f4f8; display: none;">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="fas fa-edit me-2"></i> Data Tambahan Surat:
                            </h6>
                            <div id="dynamicInputs" class="row">
                                {{-- JS akan merender input di sini --}}
                            </div>
                            <small class="text-muted italic">*Data otomatis ditarik dari profil santri, silakan ubah jika perlu.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Tanggal Keluar</label>
                                <input type="datetime-local" name="tanggal_keluar" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Batas Kembali</label>
                                <input type="datetime-local" name="batas_kembali" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="fw-bold">Nomor Surat (Opsional)</label>
                            <input type="text" name="nomor_manual" class="form-control" placeholder="Isi jika ada nomor surat fisik manual">
                        </div>

                        <div class="mb-3">
                            <label class="fw-bold">Keperluan</label>
                            <textarea name="keperluan" class="form-control" rows="2" placeholder="Alasan santri keluar pondok..."></textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('tenant.perizinan.index') }}" class="btn btn-light px-4">Batal</a>
                            <button type="submit" class="btn btn-primary btn-round px-5 fw-bold shadow">
                                <i class="fas fa-save me-2"></i> Simpan & Generate Surat
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let currentSantriData = null;

    // Fungsi Utama: Render Input & Isi Data
    function refreshDynamicInputs() {
        const selectedTemplate = $('#templateSelect').find(':selected');
        const variables = selectedTemplate.data('variables');
        const santriId = $('#santriSelect').val();
        
        const container = $('#dynamicContainer');
        const inputDiv = $('#dynamicInputs');

        // Jika tidak ada template dipilih, sembunyikan
        if (!variables || variables.length === 0) {
            container.fadeOut();
            inputDiv.empty();
            return;
        }

        // Jika santri dipilih tapi data belum ada atau berbeda, ambil via Ajax
        if (santriId) {
            $.get(`{{ url('/dashboard/perizinan/santri-data') }}/${santriId}`, function(response) {
                currentSantriData = response;
                renderElements(variables, currentSantriData);
            });
        } else {
            renderElements(variables, null);
        }
    }

    function renderElements(variables, data) {
        const inputDiv = $('#dynamicInputs');
        inputDiv.empty();
        $('#dynamicContainer').fadeIn();

        variables.forEach(key => {
            // Hindari render ulang field utama jika ada di variables database
            const coreFields = ['tanggal_keluar', 'batas_kembali', 'keperluan'];
            if (coreFields.includes(key)) return;

            let label = key.replace(/\./g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            
            // Logic deep access (misal: 'wali.nama')
            let val = '';
            if (data) {
                val = key.split('.').reduce((obj, i) => (obj ? obj[i] : ''), data) || '';
            }

            let html = `
                <div class="col-md-6 mb-3 animate__animated animate__fadeIn">
                    <label class="small fw-bold text-muted">${label}</label>
                    <input type="text" name="variables[${key}]" value="${val}" class="form-control form-control-sm">
                </div>
            `;
            inputDiv.append(html);
        });
    }

    // Trigger saat Template atau Santri berubah
    $('#templateSelect').on('change', refreshDynamicInputs);
    $('#santriSelect').on('change', refreshDynamicInputs);
});
</script>
@endpush