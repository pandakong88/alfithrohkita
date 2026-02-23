<!DOCTYPE html>
<html>
<head>
    <title>Tambah Santri</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Tambah Santri</h2>

    @if($errors->any())
        <div class="alert alert-danger">
            {{ implode('', $errors->all(':message')) }}
        </div>
    @endif

    <form action="{{ route('tenant.santri.store') }}" method="POST">
        @csrf

        <h5>Wali (Pilih Salah Satu)</h5>

        <!-- 🔹 Existing Wali -->
        <div class="mb-3">
            <label>Pilih Wali Existing</label>
            <select name="wali_id" class="form-control">
                <option value="">-- Pilih Wali --</option>
                @foreach($walis as $wali)
                    <option value="{{ $wali->id }}">
                        {{ $wali->nama }} - {{ $wali->no_hp }}
                    </option>
                @endforeach
            </select>
        </div>

        <hr>

        <!-- 🔹 Wali Baru -->
        <h6>Atau Buat Wali Baru</h6>

        <div class="mb-3">
            <label>Nama Wali</label>
            <input type="text" name="wali_nama" class="form-control">
        </div>

        <div class="mb-3">
            <label>No HP Wali</label>
            <input type="text" name="wali_no_hp" class="form-control">
        </div>

        <div class="mb-3">
            <label>Alamat Wali</label>
            <input type="text" name="wali_alamat" class="form-control">
        </div>

        <hr>

        <h5>Data Santri</h5>

        <div class="mb-3">
            <label>NIS</label>
            <input type="text" name="nis" class="form-control">
        </div>

        <div class="mb-3">
            <label>Nama Lengkap</label>
            <input type="text" name="nama_lengkap" class="form-control">
        </div>

        <div class="mb-3">
            <label>Jenis Kelamin</label>
            <select name="jenis_kelamin" class="form-control">
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
            </select>
        </div>

        <button class="btn btn-success">Simpan</button>
        <a href="{{ route('tenant.santri.index') }}" class="btn btn-secondary">Kembali</a>

    </form>
</div>

</body>
</html>