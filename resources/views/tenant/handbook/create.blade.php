@extends('layouts.tenant')

@section('content')
<div class="page-inner">

    <div class="page-header">
        <h3 class="fw-bold mb-3">Tambah Buku Pedoman</h3>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="{{ route('tenant.santri.handbook.store') }}" 
                  method="POST" 
                  enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Versi</label>
                    <input type="text" name="version" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal Rilis</label>
                    <input type="date" name="release_date" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control" required>
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Upload PDF</label>
                    <input type="file" name="file" class="form-control" required>
                </div>

                <button class="btn btn-primary">Simpan</button>
                <a href="{{ route('tenant.santri.handbook.index') }}" 
                   class="btn btn-secondary">Kembali</a>

            </form>

        </div>
    </div>

</div>
@endsection