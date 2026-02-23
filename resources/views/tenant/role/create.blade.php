<h2>Buat Role Baru</h2>

@if ($errors->any())
    <div style="color:red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('tenant.role.store') }}">
    @csrf

    <div>
        <label>Nama Role</label><br>
        <input type="text" name="name" value="{{ old('name') }}" required>
    </div>

    <br>

    <h4>Pilih Permissions</h4>

    @foreach($permissions as $permission)
        <label>
            <input type="checkbox"
                   name="permissions[]"
                   value="{{ $permission->name }}"
                   {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}>
            {{ $permission->name }}
        </label><br>
    @endforeach

    <br>
    <button type="submit">Simpan</button>
</form>

<br>
<a href="{{ route('tenant.role.index') }}">Kembali</a>
