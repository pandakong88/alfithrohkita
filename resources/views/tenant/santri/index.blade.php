<!DOCTYPE html>
<html>
<head>
    <title>Data Santri</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">

    <h2>Data Santri</h2>

    <a href="{{ route('tenant.santri.create') }}" class="btn btn-primary mb-3">
        Tambah Santri
    </a>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>NIS</th>
                <th>Nama</th>
                <th>Wali</th>
                <th>Status</th>
                <th width="180">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($santris as $santri)
                <tr>
                    <td>{{ $santri->nis }}</td>
                    <td>{{ $santri->nama_lengkap }}</td>
                    <td>{{ $santri->wali->nama ?? '-' }}</td>
                    <td>{{ $santri->status }}</td>
                    <td>
                        <a href="{{ route('tenant.santri.edit', $santri) }}"
                           class="btn btn-sm btn-warning">
                           Edit
                        </a>

                        <form action="{{ route('tenant.santri.destroy', $santri) }}"
                              method="POST"
                              style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $santris->links() }}

</div>

</body>
</html>
