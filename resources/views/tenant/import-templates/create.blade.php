@extends('layouts.tenant')

@section('title', 'Buat Template Survey')

@section('content')
<div class="container">
    <div class="page-inner py-5">
        <div class="max-w-5xl mx-auto">
            
            {{-- HEADER --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h2 class="text-dark fw-bold mb-1">Konfigurasi Template Import</h2>
                    <p class="text-muted mb-0">Rancang urutan kolom Excel sesuai kebutuhan survei Anda.</p>
                </div>
                <div class="avatar avatar-lg">
                    <span class="avatar-title rounded-circle border border-white bg-primary shadow-sm">
                        <i class="fas fa-drafting-compass"></i>
                    </span>
                </div>
            </div>

            <form method="POST" action="{{ route('tenant.import-templates.store') }}" id="templateForm">
                @csrf

                <div class="card card-round border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        
                        {{-- NAMA TEMPLATE --}}
                        <div class="form-group p-0 mb-5">
                            <label class="form-label fw-bold text-dark mb-2">Nama Template</label>
                            <input type="text" name="nama_template" class="form-control form-control-lg border-2 rounded-xl" 
                                   placeholder="Contoh: Survey Kedisiplinan Wali Murid 2026" required>
                            <small class="text-muted mt-2 d-block">Nama ini akan muncul di daftar pilihan saat proses import.</small>
                        </div>

                        <div class="row g-4">
                            {{-- KIRI: AVAILABLE FIELDS --}}
                            <div class="col-md-5">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h5 class="fw-bold text-dark mb-0">Daftar Field Tersedia</h5>
                                    <span class="badge badge-primary badge-pill" id="countAvailable">{{ count($fields) }}</span>
                                </div>
                                
                                <div class="input-group mb-3 shadow-sm rounded-pill overflow-hidden border">
                                    <span class="input-group-text bg-white border-0"><i class="fa fa-search text-muted"></i></span>
                                    <input type="text" id="searchField" class="form-control border-0 ps-0" placeholder="Cari field...">
                                </div>

                                <div id="fieldList" class="field-container p-2 bg-light-soft rounded-xl border" style="height: 450px; overflow-y: auto;">
                                    @foreach($fields as $field)
                                    <div class="field-item card mb-2 border-0 shadow-sm cursor-pointer hover-push" 
                                         data-id="{{ $field->id }}" 
                                         data-key="{{ $field->field_key }}" 
                                         data-label="{{ $field->label }}">
                                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-xs me-2">
                                                    <span class="avatar-title rounded-circle bg-primary-light text-primary">
                                                        <i class="fas fa-plus small"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark small">{{ $field->label }}</div>
                                                    <div class="text-muted" style="font-size: 10px;">{{ strtoupper($field->entity) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- KANAN: PREVIEW --}}
                            <div class="col-md-7">
                                <div class="d-flex align-items-center mb-3">
                                    <h5 class="fw-bold text-dark mb-0">Preview Urutan Kolom Excel</h5>
                                    <i class="fas fa-info-circle ms-2 text-muted" data-bs-toggle="tooltip" title="Geser (drag) baris untuk merubah urutan kolom"></i>
                                </div>

                                <div class="preview-zone border-2 border-dashed rounded-xl p-4 bg-white d-flex flex-column align-items-center" style="min-height: 450px; border-style: dashed !important; border-color: #d1d5db !important;">
                                    <div id="dragHint" class="text-center my-auto">
                                        <div class="mb-3 opacity-25">
                                            <i class="fas fa-th-list fa-4x"></i>
                                        </div>
                                        <p class="text-muted">Klik field di sebelah kiri untuk<br>mulai menyusun struktur tabel.</p>
                                    </div>

                                    <div id="sortableColumns" class="w-100">
                                        {{-- Sortable items --}}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 border-top pt-4 d-flex justify-content-between align-items-center">
                            <a href="{{ route('tenant.import-templates.index') }}" class="btn btn-link text-muted">Batal</a>
                            <button type="submit" class="btn btn-primary btn-round btn-lg px-5 shadow">
                                <span class="fw-bold">Simpan Template</span>
                            </button>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .bg-light-soft { background-color: #f8f9fc; }
    .rounded-xl { border-radius: 1.25rem !important; }
    .cursor-pointer { cursor: pointer; }
    .field-item { transition: all 0.2s; }
    .field-item:hover { background-color: #f0f7ff !important; transform: translateX(5px); }
    .bg-primary-light { background-color: #eef2ff; }
    
    .sortable-ghost { opacity: 0.4; background: #e0f2fe !important; border: 2px dashed #1572e8 !important; }
    
    .column-tag {
        background: #fff;
        border: 1px solid #ebedf2;
        border-left: 5px solid #1572e8;
        padding: 12px 15px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        cursor: grab;
    }
    .column-tag:active { cursor: grabbing; }
    .handle { color: #d1d5db; margin-right: 15px; }
    .btn-remove { color: #d1d5db; cursor: pointer; transition: 0.2s; }
    .btn-remove:hover { color: #f25961; }
</style>
@endsection

@push('scripts')
{{-- Load SortableJS --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fieldItems = document.querySelectorAll('.field-item');
        const sortableContainer = document.getElementById('sortableColumns');
        const dragHint = document.getElementById('dragHint');
        const templateForm = document.getElementById('templateForm');
    
        // 1. Initialize SortableJS
        new Sortable(sortableContainer, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            handle: '.column-tag',
        });
    
        // 2. Logic Klik Kiri -> Tambah ke Kanan
        fieldItems.forEach(item => {
            item.addEventListener('click', function() {
                const id = this.dataset.id;
                const key = this.dataset.key;
                const label = this.dataset.label;
    
                if (document.getElementById(`col-${id}`)) {
                    // Jika diklik lagi, hapus (toggle)
                    removeColumn(id);
                    return;
                }
    
                dragHint.classList.add('d-none');
    
                const colTag = document.createElement('div');
                colTag.className = 'column-tag shadow-sm';
                colTag.id = `col-${id}`;
                colTag.innerHTML = `
                    <i class="fas fa-grip-vertical handle"></i>
                    <div class="flex-grow-1">
                        <span class="fw-bold text-dark small">${label}</span>
                        <span class="text-muted ms-2" style="font-size:10px">[${key}]</span>
                    </div>
                    <input type="hidden" name="fields[]" value="${id}">
                    <i class="fas fa-times btn-remove" onclick="removeColumn('${id}')"></i>
                `;
                
                sortableContainer.appendChild(colTag);
                
                // Visual feedback
                this.classList.add('bg-primary-light', 'border-primary');
                this.querySelector('i').className = 'fas fa-check text-primary';
            });
        });
    
        // 3. Search Logic
        document.getElementById('searchField').addEventListener('input', function() {
            let keyword = this.value.toLowerCase();
            document.querySelectorAll('.field-item').forEach(item => {
                const text = item.innerText.toLowerCase();
                item.style.display = text.includes(keyword) ? 'block' : 'none';
            });
        });

        // 4. Prevent Double Submit
        templateForm.addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Menyimpan...';
        });
    });
    
    function removeColumn(id) {
        const col = document.getElementById(`col-${id}`);
        const sourceItem = document.querySelector(`.field-item[data-id="${id}"]`);
        
        if(col) col.remove();
        
        if (document.getElementById('sortableColumns').children.length === 0) {
            document.getElementById('dragHint').classList.remove('d-none');
        }
    
        if(sourceItem) {
            sourceItem.classList.remove('bg-primary-light', 'border-primary');
            sourceItem.querySelector('i').className = 'fas fa-plus small';
        }
    }
</script>
@endpush