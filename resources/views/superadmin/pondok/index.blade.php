<h2>Daftar Pondok</h2>

<a href="{{ route('superadmin.pondok.create') }}">+ Tambah Pondok</a>

@if(session('success'))
    <p style="color:green;">{{ session('success') }}</p>
@endif

<table border="1" cellpadding="8">
    <tr>
        <th>Nama</th>
        <th>Slug</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>

    @foreach($pondoks as $pondok)
        <tr>
            <td>{{ $pondok->name }}</td>
            <td>{{ $pondok->slug }}</td>
            <td>
                {{ $pondok->is_active ? 'Aktif' : 'Nonaktif' }}
            </td>
            <td>

                <!-- Edit -->
                <a href="{{ route('superadmin.pondok.edit', $pondok) }}">
                    Edit
                </a>

                |

                <!-- Toggle Status -->
                <form action="{{ route('superadmin.pondok.toggle', $pondok) }}"
                      method="POST"
                      style="display:inline;">
                    @csrf
                    @method('PATCH')
                    <button type="submit">
                        {{ $pondok->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </form>

                |

                <!-- Delete -->
                <form action="{{ route('superadmin.pondok.destroy', $pondok) }}"
                      method="POST"
                      style="display:inline;"
                      onsubmit="return confirm('Yakin hapus pondok ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit">
                        Hapus
                    </button>
                </form>

            </td>
        </tr>
    @endforeach
</table>
