<h2>Recycle Bin User</h2>

<a href="{{ route('tenant.user.index') }}">Kembali</a>

<table border="1" cellpadding="8">
    <tr>
        <th>Nama</th>
        <th>Email</th>
        <th>Aksi</th>
    </tr>

    @foreach($users as $user)
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>
                <form method="POST" action="{{ route('tenant.user.restore', $user->id) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit">Restore</button>
                </form>
            </td>
        </tr>
    @endforeach
</table>
