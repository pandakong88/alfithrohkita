@extends('layouts.tenant')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Edit Template Perizinan</h4>
        <ul class="breadcrumbs">
            <li class="nav-home"><a href="#"><i class="flaticon-home"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a>Template</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a>Edit</a></li>
        </ul>
    </div>

    <form method="POST" action="{{ route('tenant.template-perizinan.update', $template->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row">
            {{-- KOLOM KIRI: PREVIEW (EXISTING / NEW) --}}
            <div class="col-lg-7">
                <div class="card card-round">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="card-title">
                            <i class="fas fa-file-pdf me-2 text-danger"></i>
                            <span id="preview-title">Pratinjau File Saat Ini</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary btn-round" onclick="document.getElementById('file_pdf_input').click()">
                            <i class="fas fa-sync-alt me-1"></i> Ganti File PDF
                        </button>
                    </div>
                    <div class="card-body bg-light p-0">
                        <div id="pdf-container" style="background: #525659; padding: 20px; min-height: 700px;">
                            {{-- Tampilkan file lama sebagai default --}}
                            <iframe id="pdf-preview" 
                                    src="{{ asset('storage/'.$template->file_pdf) }}#toolbar=0" 
                                    width="100%" 
                                    height="750px" 
                                    class="shadow-lg border-0">
                            </iframe>
                        </div>
                        {{-- Hidden File Input --}}
                        <input type="file" name="file_pdf" id="file_pdf_input" class="d-none" accept="application/pdf" onchange="handleFileChange(this)">
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: FORM CONFIG --}}
            <div class="col-lg-5">
                <div class="card card-round">
                    <div class="card-header">
                        <div class="card-title">Konfigurasi Template</div>
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
                                <label class="custom-control-label fw-bold mb-0" for="is_default" style="cursor:pointer; color: #1572e8;">
                                    <i class="fas fa-star me-1"></i> Jadikan Template Utama
                                </label>
                            </div>
                        </div>

                        {{-- LAYOUT --}}
                        <div class="form-group p-0 mb-4">
                            <label class="fw-bold mb-2">Layout Cetak</label>
                            <div class="row g-2">
                                @foreach([1 => 'A4', 2 => 'A5', 4 => 'A6'] as $val => $text)
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="layout_print" id="l{{$val}}" value="{{$val}}" {{ $template->layout_print == $val ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary w-100" for="l{{$val}}">{{ $text }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- VARIABLES --}}
                        <label class="fw-bold mb-2">Variabel Mapping</label>
                        <div class="variable-grid bg-light p-3 rounded-3" style="max-height: 250px; overflow-y: auto;">
                            <div class="row g-2">
                                @foreach($variables as $var)
                                <div class="col-6">
                                    <div class="custom-control custom-checkbox bg-white border rounded p-2 h-100">
                                        <input type="checkbox" class="custom-control-input" name="variables[]" value="{{ $var->key }}" id="v-{{ $loop->index }}" 
                                            {{ in_array($var->key, $template->required_variables ?? []) ? 'checked' : '' }}>
                                        <label class="custom-control-label ms-1" for="v-{{ $loop->index }}" style="cursor: pointer;">
                                            <span class="d-block fw-bold text-primary" style="font-size: 11px;">{{ $var->label }}</span>
                                            <small class="text-muted" style="font-size: 9px;">{{ $var->key }}</small>
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer pb-4 px-4">
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

{{-- SCRIPT AUTO SWAP PREVIEW --}}
<script>
function handleFileChange(input) {
    const file = input.files[0];
    const previewFrame = document.getElementById('pdf-preview');
    const title = document.getElementById('preview-title');

    if (file && file.type === "application/pdf") {
        // Buat URL sementara untuk file baru
        const fileURL = URL.createObjectURL(file);
        
        // Ganti src iframe secara instan
        previewFrame.src = fileURL + "#toolbar=0";
        
        // Beri tanda visual bahwa ini file baru (belum disimpan)
        title.innerHTML = "Pratinjau File Baru (Belum Disimpan)";
        title.parentElement.classList.add('text-success');
        
        // Notifikasi simpel
        $.notify({
            icon: 'fas fa-file-import',
            title: 'File Terpilih',
            message: 'Pratinjau telah diperbarui ke file baru.',
        },{
            type: 'info',
            placement: { from: "top", align: "right" },
            time: 1000,
        });
    }
}
</script>

<style>
    .btn-check:checked + label { background-color: #1572e8 !important; color: white !important; }
    .variable-grid::-webkit-scrollbar { width: 5px; }
    .variable-grid::-webkit-scrollbar-thumb { background: #dcdde1; border-radius: 10px; }
</style>
@endsection