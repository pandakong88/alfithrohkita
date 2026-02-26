@extends('layouts.tenant')

@section('content')
<div class="page-inner">

    <div class="page-header d-flex justify-content-between">
        <h3 class="fw-bold mb-3">Buku Pedoman Santri</h3>
        <a href="{{ route('tenant.santri.handbook.create') }}" 
           class="btn btn-primary">
            Tambah Versi
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="handbookTable" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Versi</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($handbooks as $item)
                        <tr>
                            <td>{{ $item->version }}</td>
                            <td>{{ $item->release_date->format('d M Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $item->status === 'published' ? 'success' : 'secondary' }}">
                                    {{ strtoupper($item->status) }}
                                </span>
                            </td>
                            <td>{{ $item->description }}</td>
                            <td>
                                <a href="{{ route('tenant.santri.handbook.edit', $item->id) }}"
                                   class="btn btn-sm btn-warning">Edit</a>

                                <form action="{{ route('tenant.santri.handbook.destroy', $item->id) }}"
                                      method="POST"
                                      style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection