@extends('layouts.tenant')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Setting Cetak Absen</h4>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <form action="{{ route('tenant.absensi-sesi.print-fisik', $sesi->id) }}" method="GET" target="_blank">
                    <div class="card-header">
                        <div class="card-title">Parameter Absensi: {{ $sesi->nama_sesi }}</div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Pilih Bulan</label>
                                    <select name="bulan" class="form-control">
                                        @for($m=1; $m<=12; $m++)
                                            <option value="{{ sprintf('%02d', $m) }}" {{ date('m') == $m ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tahun</label>
                                    <input type="number" name="tahun" class="form-control" value="{{ date('Y') }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Format Halaman</label>
                            <select name="mode" class="form-control">
                                <option value="full">Satu Bulan Penuh (Tanggal 1 - Selesai)</option>
                                <option value="awal">Setengah Bulan Awal (Tanggal 1 - 15)</option>
                                <option value="akhir">Setengah Bulan Akhir (Tanggal 16 - Selesai)</option>
                            </select>
                            <small class="form-text text-muted">Gunakan opsi setengah bulan jika jumlah santri terlalu banyak agar kolom tidak terlalu sempit.</small>
                        </div>

                        <div class="form-group">
                            <label>Tandai Hari Libur (Warna Abu-abu)</label>
                            <div class="d-flex flex-wrap">
                                @php $hari = [1=>'Sen', 2=>'Sel', 3=>'Rab', 4=>'Kam', 5=>'Jum', 6=>'Sab', 7=>'Ahd']; @endphp
                                @foreach($hari as $v => $n)
                                <div class="custom-control custom-checkbox mr-3">
                                    <input type="checkbox" name="libur[]" value="{{ $v }}" class="custom-control-input" id="h{{ $v }}" {{ $v == 5 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="h{{ $v }}">{{ $n }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="card-action">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-print"></i> Buka Halaman Cetak
                        </button>
                        <a href="{{ route('tenant.absensi-sesi.index') }}" class="btn btn-danger">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-body">
                    <h5>Tips Cetak:</h5>
                    <ul class="pl-3">
                        <li>Gunakan kertas <b>F4 atau A4</b>.</li>
                        <li>Pastikan orientasi print adalah <b>Landscape</b>.</li>
                        <li>Set <b>Margin</b> ke 'Minimum' atau 'None' di pengaturan browser saat print.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection