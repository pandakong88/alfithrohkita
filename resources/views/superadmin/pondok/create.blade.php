<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Create Pondok</title>
</head>
<body>
    <h2>Buat Pondok Baru</h2>

@if ($errors->any())
    <div style="color:red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('superadmin.pondok.store') }}">
    @csrf

    <h4>Data Pondok</h4>
    <input type="text" name="name" placeholder="Nama Pondok" required><br><br>
    <input type="text" name="address" placeholder="Alamat"><br><br>
    <input type="text" name="phone" placeholder="No HP"><br><br>

    <h4>Admin Pondok</h4>
    <input type="text" name="admin_name" placeholder="Nama Admin" required><br><br>
    <input type="email" name="admin_email" placeholder="Email Admin" required><br><br>
    <input type="password" name="admin_password" placeholder="Password" required><br><br>

    <button type="submit">Simpan</button>
</form>

</body>
</html>