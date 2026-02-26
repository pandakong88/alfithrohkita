@extends('layouts.tenant')

@section('content')
<div class="page-inner">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Riwayat Import Santri</h3>
            <p class="text-muted small mb-0">Kelola dan pantau status batch import data santri.</p>
        </div>
        <a href="{{ route('tenant.santri.import') }}" class="btn btn-primary btn-round">
            <i class="fa fa-plus me-1"></i> Import Baru
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="importTable" class="table table-head-bg-primary table-hover table-sm text-nowrap">
                    <thead>
                        <tr>
                            <th style="width: 50px">No</th>
                            <th>Nama File</th>
                            <th class="text-center">Total</th>
                            <th class="text-center text-success">Valid</th>
                            <th class="text-center text-danger">Invalid</th>
                            <th>Status</th>
                            <th>Oleh</th>
                            <th>Tanggal</th>
                            <th style="width: 100px" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.875rem;">
                        @foreach($batches as $index => $batch)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width: 150px;" title="{{ $batch->filename }}">
                                    {{ $batch->filename }}
                                </span>
                            </td>
                            <td class="text-center fw-bold">{{ $batch->total_rows }}</td>
                            <td class="text-center text-success">{{ $batch->valid_rows }}</td>
                            <td class="text-center text-danger">{{ $batch->invalid_rows }}</td>
                            <td>
                                @if($batch->status === 'committed')
                                    <span class="badge badge-success" style="font-size: 10px;">SUCCESS</span>
                                @else
                                    <span class="badge badge-warning" style="font-size: 10px;">{{ strtoupper($batch->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <small class="d-block"><strong>Up:</strong> {{ optional($batch->uploader)->name }}</small>
                                @if($batch->committer)
                                <small class="d-block text-muted"><strong>Acc:</strong> {{ $batch->committer->name }}</small>
                                @endif
                            </td>
                            <td>
                                <small>{{ $batch->created_at->format('d/m/y') }}</small><br>
                                <small class="text-muted">{{ $batch->created_at->format('H:i') }}</small>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('tenant.santri.import.preview.show', $batch->id) }}"
                                   class="btn btn-icon btn-link btn-info btn-sm" title="Lihat Detail">
                                    <i class="fa fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $('#importTable').DataTable({
            pageLength: 10,
            ordering: true,
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari riwayat...",
            }
        });
    });
</script>
@endpush