<?php

namespace App\Http\Controllers\Tenant\Absensi;

use App\Domains\Absensi\Actions\CreateAbsensiSesiAction;
use App\Domains\Absensi\DTO\AbsensiSesiDTO;
use App\Http\Controllers\Controller;
use App\Models\AbsensiSesi;
use App\Models\Kamar; // Tambahkan ini
use App\Models\Kelas; // Tambahkan ini
use App\Models\Komplek;
use Illuminate\Http\Request;

class AbsensiSesiController extends Controller
{
    /**
     * Tampilkan Daftar Sesi
     */
    public function index()
    {
        // Trait BelongsToTenant otomatis memfilter per pondok
        // Gunakan eager loading (with) agar di view tidak terjadi N+1 query saat manggil nama kelas/kamar
        $sesis = AbsensiSesi::with(['kelas', 'kamar','komplek'])->latest()->get();

        // Ambil data untuk dropdown di modal tambah/edit
        $kelass = Kelas::orderBy('nama', 'asc')->get();
        $kamars = Kamar::orderBy('nama', 'asc')->get();
        $kompleks = Komplek::orderBy('nama', 'asc')->get();

        return view('tenant.absensi_sesi.index', compact('sesis', 'kelass', 'kamars', 'kompleks'));
    }

    /**
     * Store & Update
     */
    public function store(Request $request, CreateAbsensiSesiAction $action)
    {
        // dd($request->all());
        $request->validate([
            'nama_sesi'   => 'required|string|max:255',
            // Ubah bagian ini
            'target_tipe' => 'required|in:global,kelas,kamar,komplek,plotting',
            'target_id'   => 'nullable|integer', // Bisa null kalau tipenya global/plotting
            'jam_mulai'   => 'required',
            'jam_selesai' => 'required',
            'id'          => 'nullable|exists:absensi_sesi,id'
        ]);

        try {
            // 1. Transform request ke DTO
            $dto = AbsensiSesiDTO::fromRequest($request);

            // 2. Eksekusi Action
            $action->execute($dto, $request->id);

            $message = $request->id ? 'Sesi berhasil diperbarui' : 'Sesi berhasil ditambahkan';
            
            return redirect()
                ->route('tenant.absensi-sesi.index')
                ->with('success', $message);

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Sesi (Soft Delete)
     */
    public function destroy($id)
    {
        try {
            $sesi = AbsensiSesi::findOrFail($id);
            $sesi->delete();

            return redirect()
                ->route('tenant.absensi-sesi.index')
                ->with('success', 'Sesi berhasil dihapus');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    /**
     * TODO: Method untuk Manage Santri (Khusus tipe Plotting)
     * Ini nanti kita buat halamannya sendiri buat nyeklis si ABCD
     */
    public function manageSantri($id)
    {
        $sesi = AbsensiSesi::with('santris')->findOrFail($id);
        
        $kelass = \App\Models\Kelas::orderBy('nama', 'asc')->get();
        $kompleks = \App\Models\Komplek::orderBy('nama', 'asc')->get();
        $kamars = \App\Models\Kamar::orderBy('nama', 'asc')->get();
        
        // Gunakan scopeActive() bawaan model kamu agar sinkron ('active')
        $santris = \App\Models\Santri::active() 
                    ->orderBy('nama_lengkap', 'asc')
                    ->get();
        
        $selectedSantri = $sesi->santris->pluck('id')->toArray();
    
        return view('tenant.absensi_sesi.manage_santri', compact(
            'sesi', 'santris', 'selectedSantri', 'kelass', 'kompleks', 'kamars'
        ));
    }
    
    public function updateSantri(Request $request, $id)
    {
        $sesi = AbsensiSesi::findOrFail($id);
        
        // Siapkan data santri_id dengan pondok_id untuk tabel pivot
        $syncData = [];
        $santriIds = $request->input('santri_ids', []);
        
        foreach ($santriIds as $santriId) {
            $syncData[$santriId] = ['pondok_id' => auth()->user()->pondok_id];
        }
    
        // Sync akan menghapus yang tidak dicentang dan menambah yang baru dicentang
        $sesi->santris()->sync($syncData);
    
        return redirect()->route('tenant.absensi-sesi.index')
            ->with('success', 'Daftar peserta plotting berhasil diperbarui');
    }


    public function printAbsenFisik($id, Request $request)
    {
        // 1. Cari data Sesi
        $sesi = AbsensiSesi::findOrFail($id);
        
        // 2. Ambil Parameter dari URL (dikirim dari halaman manage-print)
        $mode = $request->get('mode', 'full'); 
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        
        // Pastikan hariLibur selalu berbentuk array, default Jumat (5) jika kosong
        $hariLibur = $request->get('libur');
        if (!$hariLibur) {
            $hariLibur = [5]; 
        }

        // 3. Logic Ambil Data Santri berdasarkan Target Tipe
        // Kita gunakan Santri::active() agar santri yang sudah boyong tidak muncul
        $querySantri = \App\Models\Santri::active();

        if ($sesi->target_tipe === 'plotting') {
            // Jika tipenya plotting, ambil dari relasi pivot
            $santris = $sesi->santris()->orderBy('nama_lengkap', 'asc')->get();
        } elseif ($sesi->target_tipe === 'kelas') {
            // Jika tipenya kelas, filter berdasarkan kelas_id
            $santris = $querySantri->where('kelas_id', $sesi->target_id)
                                ->orderBy('nama_lengkap', 'asc')->get();
        } elseif ($sesi->target_tipe === 'kamar') {
            // Jika tipenya kamar, filter berdasarkan kamar_id
            $santris = $querySantri->where('kamar_id', $sesi->target_id)
                                ->orderBy('nama_lengkap', 'asc')->get();
        } elseif ($sesi->target_tipe === 'komplek') {
            // Jika tipenya komplek, cari santri yang kamarnya milik komplek tersebut
            $santris = $querySantri->whereHas('kamar', function($q) use ($sesi) {
                                    $q->where('komplek_id', $sesi->target_id);
                                })->orderBy('nama_lengkap', 'asc')->get();
        } else {
            // Jika global/tidak ada tipe, ambil semua santri aktif
            $santris = $querySantri->orderBy('nama_lengkap', 'asc')->get();
        }

        // 4. Hitung Rentang Tanggal
        $totalHari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
        
        if ($mode == 'awal') {
            $tglMulai = 1;
            $tglSelesai = 15;
        } elseif ($mode == 'akhir') {
            $tglMulai = 16;
            $tglSelesai = $totalHari;
        } else {
            $tglMulai = 1;
            $tglSelesai = $totalHari;
        }

        // 5. Format Nama Bulan (Indonesia)
        $namaBulan = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F');

        // 6. Kirim ke View
        return view('tenant.absensi_sesi.print_fisik', compact(
            'sesi', 
            'santris', 
            'tglMulai', 
            'tglSelesai', 
            'namaBulan', 
            'tahun', 
            'bulan', 
            'hariLibur',
            'mode'
        ));
    }

    // 1. Method untuk halaman setting (yang pake layout tenant)
    public function managePrint($id)
    {
        $sesi = AbsensiSesi::findOrFail($id);
        return view('tenant.absensi_sesi.manage_print', compact('sesi'));
    }

}