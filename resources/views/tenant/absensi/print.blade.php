<!DOCTYPE html>
<html>
<head>
    <title>Print_Rekap_{{ $sesiAktif->nama }}_{{ $bulan }}_{{ $tahun }}</title>
    <style>
        @media print {
            @page { size: landscape; margin: 10mm; }
            body { -webkit-print-color-adjust: exact; }
        }
        body { font-family: 'Arial', sans-serif; font-size: 10px; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 3px 1px; text-align: center; }
        .name-col { width: 160px; text-align: left; padding-left: 5px; white-space: nowrap; overflow: hidden; }
        .tgl-col { width: auto; }
        .rekap-col { width: 80px; font-weight: bold; background: #f0f0f0; }
        
        /* Box Status Minimalis */
        .box { display: inline-block; width: 15px; height: 15px; line-height: 15px; border-radius: 3px; font-weight: bold; color: white; font-size: 9px; }
        .st-hadir { background: #10b981 !important; }
        .st-sakit { background: #f59e0b !important; }
        .st-izin  { background: #3b82f6 !important; }
        .st-alfa  { background: #ef4444 !important; }
        .st-terlambat { background: #6366f1 !important; }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h2 style="margin:0">REKAP PRESENSI SANTRI</h2>
        <h3 style="margin:5px 0">{{ strtoupper($sesiAktif->nama) }}</h3>
        <p>Periose: {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="name-col">Nama Santri</th>
                @for($d = 1; $d <= $daysInMonth; $d++)
                    <th class="tgl-col">{{ $d }}</th>
                @endfor
                <th class="rekap-col">H/S/I/A/T</th>
            </tr>
        </thead>
        <tbody>
            @foreach($santris as $santri)
                @php
                    $h = $santri->absensis->where('status', 'hadir')->count();
                    $s = $santri->absensis->where('status', 'sakit')->count();
                    $i = $santri->absensis->where('status', 'izin')->count();
                    $a = $santri->absensis->where('status', 'alfa')->count();
                    $t = $santri->absensis->where('status', 'terlambat')->count();
                @endphp
                <tr>
                    <td class="name-col">{{ $santri->nama_lengkap }}</td>
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $tgl = $tahun . '-' . sprintf('%02d', $bulan) . '-' . sprintf('%02d', $d);
                            $abs = $santri->absensis->firstWhere('tanggal', $tgl);
                        @endphp
                        <td class="tgl-col">
                            @if($abs)
                                <span class="box st-{{ $abs->status }}">{{ strtoupper(substr($abs->status, 0, 1)) }}</span>
                            @else
                                <span style="color: #ccc">-</span>
                            @endif
                        </td>
                    @endfor
                    <td class="rekap-col">{{$h}}/{{$s}}/{{$i}}/{{$a}}/{{$t}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>