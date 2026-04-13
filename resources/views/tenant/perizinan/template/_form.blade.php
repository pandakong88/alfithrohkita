<div class="card card-round border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="row">
            <div class="col-md-7 mb-3">
                <label class="fw-bold mb-2">Nama Template</label>
                <input type="text" name="nama" class="form-control" value="{{ old('nama', $template->nama ?? '') }}" placeholder="Misal: Surat Izin Pulang" required>
            </div>
            <div class="col-md-5 mb-3">
                <label class="fw-bold mb-2">Layout Cetak</label>
                <select name="layout_print" id="layout_print" class="form-select border-primary">
                    <option value="1">1 Per Halaman (Full F4/A4)</option>
                    <option value="2">2 Per Halaman (A5 Vertikal)</option>
                    <option value="4">4 Per Halaman (A6 Kotak)</option>
                </select>
            </div>
        </div>

        {{-- TOOLBAR KOMPONEN --}}
        <div class="bg-light p-3 rounded-lg mb-3 border">
            <label class="fw-bold small text-uppercase text-muted d-block mb-2">Sisipkan Komponen Cepat:</label>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-sm btn-dark" onclick="insertKop()">
                    <i class="fas fa-id-card me-1"></i> + Kop Surat
                </button>
                <button type="button" class="btn btn-sm btn-dark" onclick="insertTandaTangan(2)">
                    <i class="fas fa-signature me-1"></i> + 2 Tanda Tangan
                </button>
                <button type="button" class="btn btn-sm btn-dark" onclick="insertTandaTangan(3)">
                    <i class="fas fa-users me-1"></i> + 3 Tanda Tangan
                </button>
                <button type="button" class="btn btn-sm btn-info text-white" onclick="insertGaris()">
                    <i class="fas fa-minus me-1"></i> + Garis Pemisah
                </button>
            </div>
        </div>

        <div class="mb-3">
            <label class="fw-bold mb-2 text-primary">Kanvas Desain Surat</label>
            <textarea name="format_surat" id="suratEditor" class="form-control">
                {{ old('format_surat', $template->format_surat ?? '') }}
            </textarea>
        </div>

        <div class="form-check p-0">
            <input type="checkbox" name="is_active" id="isActive" value="1" {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}>
            <label class="fw-bold ms-2" for="isActive">Aktifkan Template</label>
        </div>
    </div>
</div>