<h2>Edit Pondok</h2>

@if ($errors->any())
    <div style="color:red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('superadmin.pondok.update', $pondok) }}">
    @csrf
    @method('PUT')

    <input type="text"
           name="name"
           value="{{ old('name', $pondok->name) }}"
           required>
    <br><br>

    <input type="text"
           name="address"
           value="{{ old('address', $pondok->address) }}">
    <br><br>

    <input type="text"
           name="phone"
           value="{{ old('phone', $pondok->phone) }}">
    <br><br>

    <button type="submit">Update</button>
</form>

<br>
<a href="{{ route('superadmin.pondok.index') }}">Kembali</a>
