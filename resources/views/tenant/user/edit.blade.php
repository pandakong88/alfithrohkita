<h2>Edit User</h2>

@if ($errors->any())
    <div style="color:red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('tenant.user.update', $user) }}">
    @csrf
    @method('PUT')

    <input type="text"
           name="name"
           value="{{ old('name', $user->name) }}"
           required><br><br>

    <input type="email"
           name="email"
           value="{{ old('email', $user->email) }}"
           required><br><br>

           <select name="role_id" required>
            @foreach($roles as $role)
                <option value="{{ $role->id }}"
                    {{ $user->roles->first()?->id == $role->id ? 'selected' : '' }}>
                    {{ $role->name }}
                </option>
            @endforeach
        </select>
        
        <br><br>

        password baru
    <input type="password"
           name="password"
           placeholder="Kosongkan jika tidak diubah"><br><br>

    <button type="submit">Update</button>
</form>
