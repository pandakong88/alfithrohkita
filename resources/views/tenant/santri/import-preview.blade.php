<!DOCTYPE html>
<html>
<head>
    <title>Preview Import Santri</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">

    <h2>Preview Import Santri</h2>

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>File:</strong> {{ $batch->filename }}</p>
            <p><strong>Total Rows:</strong> {{ $batch->total_rows }}</p>
            <p class="text-success">
                <strong>Valid:</strong> {{ $batch->valid_rows }}
            </p>
            <p class="text-danger">
                <strong>Invalid:</strong> {{ $batch->invalid_rows }}
            </p>
        </div>
    </div>

    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th>Row</th>
                <th>NIS</th>
                <th>Nama</th>
                <th>Gender</th>
                <th>Wali</th>
                <th>Status</th>
                <th>Error</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr class="{{ $row->is_valid ? '' : 'table-danger' }}">
                    <td>{{ $row->row_number }}</td>
                    <td>{{ $row->payload['nis'] ?? '-' }}</td>
                    <td>{{ $row->payload['nama_lengkap'] ?? '-' }}</td>
                    <td>{{ $row->payload['jenis_kelamin'] ?? '-' }}</td>
                    <td>{{ $row->payload['nama_wali'] ?? '-' }}</td>
                    <td>
                        @if($row->is_valid)
                            <span class="badge bg-success">Valid</span>
                        @else
                            <span class="badge bg-danger">Invalid</span>
                        @endif
                    </td>
                    <td>
                        @if($row->errors)
                            <ul class="mb-0">
                                @foreach($row->errors as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $rows->links() }}

    @if($batch->invalid_rows == 0 && $batch->valid_rows > 0)
        <form method="POST"
              action="{{ route('tenant.santri.import.commit', $batch->id) }}">
            @csrf
            <button class="btn btn-success">
                Konfirmasi Import
            </button>
        </form>
    @else
        <div class="alert alert-warning">
            Tidak bisa commit karena masih ada data invalid.
        </div>
    @endif

</div>

</body>
</html>