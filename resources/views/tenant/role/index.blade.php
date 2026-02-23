<h2>Manajemen Role</h2>

<a href="{{ route('tenant.role.create') }}">+ Tambah Role</a>

@if(session('success'))
    <p style="color:green;">{{ session('success') }}</p>
@endif

<table border="1" cellpadding="8">
    <tr>
        <th>Nama Role</th>
        <th>Permissions</th>
        <th>Aksi</th>
    </tr>

    @foreach($roles as $role)
        <tr>
            <td>{{ $role->name }}</td>
            <td>
                @foreach($role->permissions as $permission)
                    <span>{{ $permission->name }}</span>,
                @endforeach
            </td>
            <td>

                <a href="{{ route('tenant.role.edit', $role) }}">Edit</a>

                |

                <form action="{{ route('tenant.role.destroy', $role) }}"
                      method="POST"
                      style="display:inline;"
                      onsubmit="return confirm('Yakin hapus role ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Hapus</button>
                </form>

            </td>
        </tr>
    @endforeach
</table>
