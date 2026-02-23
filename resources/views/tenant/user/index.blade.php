<h2>Daftar User</h2>

<a href="{{ route('tenant.user.create') }}">+ Tambah User</a>

@if(session('success'))
    <p style="color:green;">{{ session('success') }}</p>
@endif

<table border="1" cellpadding="8">
    <tr>
        <th>Nama</th>
        <th>Email</th>
        <th>Role</th>
        <th>Status</th>
        <th>Aksi</th>

    </tr>

    @foreach($users as $user)
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>
                {{ $user->roles->pluck('name')->implode(', ') }}
            </td>
            
            <td>{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</td>
            <td>

                <a href="{{ route('tenant.user.edit', $user) }}">Edit</a>
            
                |
            
                <form action="{{ route('tenant.user.toggle', $user) }}"
                      method="POST"
                      style="display:inline;">
                    @csrf
                    @method('PATCH')
                    <button type="submit">
                        {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </form>
            
                |
            
                <form action="{{ route('tenant.user.destroy', $user) }}"
                      method="POST"
                      style="display:inline;"
                      onsubmit="return confirm('Yakin hapus user?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Hapus</button>
                </form>
            
            </td>
            
        </tr>
    @endforeach
</table>
