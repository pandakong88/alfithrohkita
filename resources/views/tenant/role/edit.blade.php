<h2>Edit Role</h2>

@if ($errors->any())
    <div style="color:red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('tenant.role.update', $role) }}">
    @csrf
    @method('PUT')

    <div>
        <label>Nama Role</label><br>
        <input type="text"
               name="name"
               value="{{ old('name', $role->name) }}"
               required>
    </div>

    <br>

    <h4>Pilih Permissions</h4>

    @foreach($permissions as $permission)
        <label>
            <input type="checkbox"
                   name="permissions[]"
                   value="{{ $permission->name }}"
                   {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
            {{ $permission->name }}
        </label><br>
    @endforeach

    <br>
    <button type="submit">Update</button>
</form>

<br>
<a href="{{ route('tenant.role.index') }}">Kembali</a>
