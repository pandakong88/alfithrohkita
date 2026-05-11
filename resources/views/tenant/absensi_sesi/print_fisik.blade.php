<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Absen Fisik - {{ $sesi->nama_sesi }}</title>
    <style>
        /* Base Styling */
        body { 
            background: white; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            font-size: 9pt; 
            margin: 0;
            padding: 0;
        }
        .container-print { padding: 20px; }
        
        /* Header */
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 0; text-transform: uppercase; font-size: 16pt; }
        .header p { margin: 5px 0 0; font-size: 11pt; color: #555; }

        /* Table Styling */
        .table-absen { 
            border-collapse: collapse; 
            width: 100%; 
            table-layout: fixed; /* Menjaga ukuran kolom tetap konsisten */
        }
        .table-absen th, .table-absen td { 
            border: 1px solid black !important; 
            padding: 5px 2px; 
            text-align: center; 
            word-wrap: break-word;
        }
        .table-absen th { background-color: #f2f2f2 !important; font-weight: bold; }
        
        /* Kolom Nama & No */
        .col-no { width: 30px; }
        .col-nama { width: 180px; text-align: left !important; padding-left: 8px !important; }

        /* Hari Libur & Kolom Tanggal */
        .bg-libur { background-color: #ebedef !important; }
        .text-red { color: #d9534f !important; font-weight: bold; }
        
        /* Tanda Tangan */
        .ttd-section { margin-top: 30px; width: 100%; }
        .ttd-box { float: right; width: 250px; text-align: center; }

        /* Control Button (Tidak ikut ter-print) */
        .no-print { 
            background: #f8f9fa; 
            padding: 15px; 
            border-bottom: 1px solid #ddd; 
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            border: none;
        }
        .btn-print { background: #1572e8; color: white; }
        .btn-back { background: #6c757d; color: white; }

        @media print {
            .no-print { display: none !important; }
            @page { 
                size: landscape; 
                margin: 0.5cm; 
            }
            body { margin: 0; }
            .table-absen th { -webkit-print-color-adjust: exact; }
            .bg-libur { background-color: #ebedef !important; -webkit-print-color-adjust: exact; }
            .text-red { color: red !important; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn btn-print">Cetak Sekarang (Landscape)</button>
        <a href="{{ route('tenant.absensi-sesi.index') }}" class="btn btn-danger">Kembali</a>

        <div style="margin-left: auto; color: #666; font-size: 0.9em;">
            *Gunakan kertas A4/F4 posisi Landscape
        </div>
    </div>

    <div class="container-print">
        <div class="header">
            <h2>Daftar Hadir Santri - {{ $sesi->nama_sesi }}</h2>
            <p>
                Target: <strong>{{ ucfirst($sesi->target_tipe) }} 
                @if($sesi->target_tipe == 'kelas') ({{ $sesi->kelas->nama ?? 'N/A' }})
                @elseif($sesi->target_tipe == 'kamar') ({{ $sesi->kamar->nama ?? 'N/A' }})
                @elseif($sesi->target_tipe == 'komplek') ({{ $sesi->komplek->nama ?? 'N/A' }})
                @endif</strong>
                | Bulan: <strong>{{ $namaBulan }} {{ $tahun }}</strong>
            </p>
        </div>

        <table class="table-absen">
            <thead>
                <tr>
                    <th class="col-no" rowspan="2">No</th>
                    <th class="col-nama" rowspan="2">Nama Lengkap</th>
                    <th colspan="{{ ($tglSelesai - $tglMulai) + 1 }}">Tanggal</th>
                </tr>
                <tr>
                    @for($i = $tglMulai; $i <= $tglSelesai; $i++)
                        @php
                            $timestamp = strtotime("$tahun-$bulan-$i");
                            $dayName = date('D', $timestamp); // Nama hari (Sun, Mon, dst)
                            $dayIndex = date('N', $timestamp); // Angka hari (1=Senin, 7=Minggu)
                            $isLibur = in_array($dayIndex, $hariLibur);
                        @endphp
                        <th class="{{ $isLibur ? 'bg-libur text-red' : '' }}">
                            {{ $i }}<br>
                            <small style="font-size: 7pt;">{{ $dayName }}</small>
                        </th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @forelse($santris as $index => $s)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="col-nama"><strong>{{ $s->nama_lengkap }}</strong></td>
                    @for($i = $tglMulai; $i <= $tglSelesai; $i++)
                        @php
                            $dayIndex = date('N', strtotime("$tahun-$bulan-$i"));
                            $isLibur = in_array($dayIndex, $hariLibur);
                        @endphp
                        <td class="{{ $isLibur ? 'bg-libur' : '' }}"></td>
                    @endfor
                </tr>
                @empty
                <tr>
                    <td colspan="{{ ($tglSelesai - $tglMulai) + 3 }}">Data santri tidak ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="ttd-section">
            <div class="ttd-box">
                <p>{{ date('d') }} {{ $namaBulan }} {{ $tahun }}</p>
                <p>Pengurus,</p>
                <br><br><br>
                <p><strong>( ____________________ )</strong></p>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>

</body>
</html>