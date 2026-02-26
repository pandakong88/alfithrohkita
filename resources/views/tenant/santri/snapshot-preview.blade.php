@extends('layouts.tenant')

@section('content')
<div class="page-inner">

    <div class="page-header">
        <h3 class="fw-bold mb-3">Preview Snapshot Santri</h3>
    </div>

    <div class="card">
        <div class="card-body">

            <div class="mb-3">
                <strong>Tanggal Snapshot:</strong> 
                {{ $batch->snapshot_date->format('d M Y') }}
            </div>

            <div class="mb-3">
                <strong>Status Batch:</strong>
                <span class="badge bg-{{ $batch->status === 'committed' ? 'success' : 'warning' }}">
                    {{ strtoupper($batch->status) }}
                </span>
            </div>

            <div class="mb-3">
                <strong>Total:</strong> {{ $batch->total_rows }} |
                <strong>Valid:</strong> {{ $batch->valid_rows }} |
                <strong>Invalid:</strong> {{ $batch->invalid_rows }}
            </div>

            @if($batch->status === 'committed')
                <div class="alert alert-success">
                    Snapshot sudah di-commit pada 
                    {{ \Carbon\Carbon::parse($batch->committed_at)->format('d M Y H:i') }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Row</th>
                            <th>NIS</th>
                            <th>Status</th>
                            <th>Kelas</th>
                            <th>Catatan</th>
                            <th>Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($batch->rows as $row)
                            <tr class="{{ !$row->is_valid ? 'table-danger' : '' }}">
                                <td>{{ $row->row_number }}</td>
                                <td>{{ $row->payload['nis'] ?? '-' }}</td>
                                <td>{{ $row->payload['status'] ?? '-' }}</td>
                                <td>{{ $row->payload['kelas'] ?? '-' }}</td>
                                <td>{{ $row->payload['catatan'] ?? '-' }}</td>
                                <td>
                                    @if($row->errors)
                                        {{ implode(', ', $row->errors) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Tombol Commit --}}
            @if($batch->invalid_rows == 0 && 
                $batch->valid_rows > 0 && 
                $batch->status !== 'committed')

                <form action="{{ route('tenant.santri.snapshot.commit', $batch->id) }}" 
                      method="POST" 
                      class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        Commit Snapshot
                    </button>
                </form>

            @endif

            <a href="{{ route('tenant.santri.snapshot.import') }}" 
               class="btn btn-secondary mt-3">
                Kembali
            </a>

        </div>
    </div>

</div>
@endsection