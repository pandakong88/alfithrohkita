@extends('layouts.tenant')

@section('content')
<div class="page-inner">

    <div class="page-header">
        <h3 class="fw-bold mb-3">Import Snapshot Santri</h3>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="{{ route('tenant.santri.snapshot.preview') }}" 
                  method="POST" 
                  enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Tanggal Snapshot</label>
                    <input type="date" 
                           name="snapshot_date" 
                           class="form-control" 
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Upload File Excel</label>
                    <input type="file" 
                           name="file" 
                           class="form-control" 
                           required>
                </div>

                <button type="submit" class="btn btn-primary">
                    Upload & Preview
                </button>

            </form>

            <hr>

            <h6>Format Excel:</h6>
            <ul>
                <li>Kolom A: NIS</li>
                <li>Kolom B: Status</li>
                <li>Kolom C: Kelas (optional)</li>
                <li>Kolom D: Catatan (optional)</li>
            </ul>

        </div>
    </div>

</div>
@endsection