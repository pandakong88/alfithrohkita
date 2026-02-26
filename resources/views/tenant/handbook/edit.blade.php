@extends('layouts.tenant')

@section('content')
<div class="page-inner">

    <div class="page-header">
        <h3 class="fw-bold mb-3">Edit Buku Pedoman</h3>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="{{ route('tenant.santri.handbook.update', $handbook->id) }}" 
                  method="POST" 
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Versi</label>
                    <input type="text" name="version" 
                           value="{{ $handbook->version }}" 
                           class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal Rilis</label>
                    <input type="date" name="release_date" 
                           value="{{ $handbook->release_date->format('Y-m-d') }}" 
                           class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control" required>
                        <option value="draft" 
                            {{ $handbook->status == 'draft' ? 'selected' : '' }}>
                            Draft
                        </option>
                        <option value="published" 
                            {{ $handbook->status == 'published' ? 'selected' : '' }}>
                            Published
                        </option>
                        <option value="archived" 
                            {{ $handbook->status == 'archived' ? 'selected' : '' }}>
                            Archived
                        </option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="description" class="form-control">
                        {{ $handbook->description }}
                    </textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ganti File PDF (optional)</label>
                    <input type="file" name="file" class="form-control">
                </div>

                <button class="btn btn-primary">Update</button>
                <a href="{{ route('tenant.santri.handbook.index') }}" 
                   class="btn btn-secondary">Kembali</a>

            </form>

        </div>
    </div>

</div>
@endsection