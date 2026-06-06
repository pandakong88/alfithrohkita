<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\Wali;
use App\Models\Perizinan;
use App\Models\PelanggaranSantri;
use App\Models\Absensi;

class DashboardController extends Controller
{
    public function index()
    {
        $pondokId = auth()->user()->pondok_id;

        // Statistics (automatically scoped by tenant via BelongsToTenant trait)
        $totalSantri = Santri::count();
        $totalWali = Wali::count();
        
        // Active leave/permissions (status is 'aktif' or 'terlambat')
        $aktifIzin = Perizinan::whereIn('status', ['aktif', 'terlambat'])->count();
        
        // Total violations recorded (explicitly scope by pondok_id since it doesn't use the trait)
        $totalPelanggaran = PelanggaranSantri::where('pondok_id', $pondokId)->count();

        // Gender Distribution
        $santriLaki = Santri::where('jenis_kelamin', 'L')->count();
        $santriPerempuan = Santri::where('jenis_kelamin', 'P')->count();

        // Today's attendance stats
        $todayDate = date('Y-m-d');
        $hadirToday = Absensi::where('tanggal', $todayDate)->where('status', 'hadir')->count();
        $izinToday = Absensi::where('tanggal', $todayDate)->where('status', 'izin')->count();
        $sakitToday = Absensi::where('tanggal', $todayDate)->where('status', 'sakit')->count();
        $alfaToday = Absensi::where('tanggal', $todayDate)->where('status', 'alfa')->count();
        $terlambatToday = Absensi::where('tanggal', $todayDate)->where('status', 'terlambat')->count();

        // Recent perizinan (5 latest)
        $recentPerizinans = Perizinan::with(['santri', 'template'])
            ->latest()
            ->take(5)
            ->get();

        // Recent pelanggaran (5 latest)
        $recentPelanggarans = PelanggaranSantri::with(['santri', 'kategoriPelanggaran'])
            ->where('pondok_id', $pondokId)
            ->latest()
            ->take(5)
            ->get();

        // Tren Pelanggaran (last 7 days counts)
        $violationTrend = [];
        $violationLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dayName = date('D', strtotime($date));
            $dayNameInd = match($dayName) {
                'Mon' => 'Senin',
                'Tue' => 'Selasa',
                'Wed' => 'Rabu',
                'Thu' => 'Kamis',
                'Fri' => 'Jumat',
                'Sat' => 'Sabtu',
                'Sun' => 'Minggu',
                default => $dayName
            };
            $violationLabels[] = $dayNameInd;
            $violationTrend[] = PelanggaranSantri::where('pondok_id', $pondokId)->where('tanggal', $date)->count();
        }

        return view('tenant.dashboard', compact(
            'totalSantri',
            'totalWali',
            'aktifIzin',
            'totalPelanggaran',
            'santriLaki',
            'santriPerempuan',
            'hadirToday',
            'izinToday',
            'sakitToday',
            'alfaToday',
            'terlambatToday',
            'recentPerizinans',
            'recentPelanggarans',
            'violationTrend',
            'violationLabels'
        ));
    }
}
