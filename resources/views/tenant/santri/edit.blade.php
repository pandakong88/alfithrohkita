<!DOCTYPE html>
<html>
<head>
    <title>Edit Santri</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Edit Santri</h2>

    <form action="{{ route('tenant.santri.update', $santri) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Wali</label>
            <select name="wali_id" class="form-control">
                @foreach($walis as $wali)
                    <option value="{{ $wali->id }}"
                        {{ $wali->id == $santri->wali_id ? 'selected' : '' }}>
                        {{ $wali->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>NIS</label>
            <input type="text"
                   name="nis"
                   value="{{ $santri->nis }}"
                   class="form-control">
        </div>

        <div class="mb-3">
            <label>Nama Lengkap</label>
            <input type="text"
                   name="nama_lengkap"
                   value="{{ $santri->nama_lengkap }}"
                   class="form-control">
        </div>

        <div class="mb-3">
            <label>Jenis Kelamin</label>
            <select name="jenis_kelamin" class="form-control">
                <option value="L" {{ $santri->jenis_kelamin == 'L' ? 'selected' : '' }}>
                    Laki-laki
                </option>
                <option value="P" {{ $santri->jenis_kelamin == 'P' ? 'selected' : '' }}>
                    Perempuan
                </option>
            </select>
        </div>

        <button class="btn btn-success">Update</button>
        <a href="{{ route('tenant.santri.index') }}" class="btn btn-secondary">
            Kembali
        </a>
    </form>
</div>

</body>
</html>
