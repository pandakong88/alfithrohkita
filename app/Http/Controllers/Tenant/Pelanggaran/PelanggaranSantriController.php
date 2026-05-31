<?php

namespace App\Http\Controllers\Tenant\Pelanggaran;

use App\Http\Controllers\Controller;
use App\Domains\Pelanggaran\Actions\CreatePelanggaranAction;
use App\Domains\Pelanggaran\Actions\UpdatePelanggaranAction;
use App\Domains\Pelanggaran\DTO\PelanggaranSantriDTO;
use App\Models\PelanggaranSantri;
use App\Models\KategoriPelanggaran;
use App\Models\Santri;
use Illuminate\Http\Request;

class PelanggaranSantriController extends Controller
{
    public function __construct(
        protected CreatePelanggaranAction $createPelanggaranAction,
        protected UpdatePelanggaranAction $updatePelanggaranAction
    ) {}

    /**
     * Halaman Utama & Riwayat Pelanggaran
     */
    public function index(Request $request)
    {
        $pondokId = auth()->user()->pondok_id;
    
        // 1. QUERY UTAMA: Riwayat Pelanggaran (Tabel)
        $pelanggarans = PelanggaranSantri::with(['santri', 'kategoriPelanggaran', 'pencatat'])
            ->where('pondok_id', $pondokId)
            ->when($request->search, function ($query, $search) {
                $query->whereHas('santri', function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%");
                })->orWhere('judul_pelanggaran', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);
    
        // 2. WIDGET 1: Top 3 Kategori Pelanggaran Paling Sering Terjadi (Bulan Ini)
        $topPelanggarans = PelanggaranSantri::where('pelanggaran_santris.pondok_id', $pondokId)
            ->join('kategori_pelanggarans', 'pelanggaran_santris.kategori_id', '=', 'kategori_pelanggarans.id')
            ->whereMonth('pelanggaran_santris.tanggal', now()->month)
            ->whereYear('pelanggaran_santris.tanggal', now()->year)
            ->whereNull('kategori_pelanggarans.deleted_at') // Memastikan kategori yang belum dihapus
            ->select(
                'kategori_pelanggarans.nama_pelanggaran as nama_kategori', 
                'kategori_pelanggarans.tingkat', // Opsional: jika ingin menampilkan tingkatnya (ringan/sedang/berat) di Blade
                \DB::raw('count(*) as total_cases')
            )
            ->groupBy('pelanggaran_santris.kategori_id', 'kategori_pelanggarans.nama_pelanggaran', 'kategori_pelanggarans.tingkat')
            ->orderByDesc('total_cases')
            ->take(3)
            ->get();

        // Hitung total kasus bulan ini untuk kalkulasi persentase progress bar di Blade
        $totalKasusBulanIni = $topPelanggarans->sum('total_cases') ?: 1;
    
        // 3. WIDGET 2: Top 3 Santri Indisipliner Teratas (Tanpa Filter Status Active)
        // Menggunakan subquery COALESCE agar menghitung semua akumulasi poin santri di pondok ini
        $topSantris = Santri::where('pondok_id', $pondokId)
            ->select('santris.*')
            ->selectRaw('COALESCE((
                SELECT SUM(poin) 
                FROM pelanggaran_santris 
                WHERE pelanggaran_santris.santri_id = santris.id 
                AND pelanggaran_santris.deleted_at IS NULL
            ), 0) as total_poin')
            ->having('total_poin', '>', 0)
            ->orderByDesc('total_poin')
            ->take(3)
            ->get();
    
        // 4. DATA MASTER: Ambil SEMUA santri di pondok ini tanpa batasan status
        $kategoris = KategoriPelanggaran::where('pondok_id', $pondokId)->get();
        $santris = Santri::where('pondok_id', $pondokId)->get();
    
        return view('tenant.pelanggaran.index', compact(
            'pelanggarans', 
            'topPelanggarans', 
            'totalKasusBulanIni',
            'topSantris', 
            'kategoris', 
            'santris'
        ));
    }

    /**
     * Handle Input Pelanggaran Massal / Tunggal
     */
    public function store(Request $request)
    {
        $request->validate([
            'santri_ids'        => 'required|array',
            'santri_ids.*'      => 'required|integer',
            'judul_pelanggaran' => 'required|string|max:255',
            'poin'              => 'required|integer|min:0',
            'kategori_id'       => 'nullable|integer',
            'tanggal'           => 'required|date',
            'catatan_detail'    => 'nullable|string',
            'foto_bukti'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'kategori_sumber'   => 'nullable|string'
        ]);

        $pathFoto = null;
        if ($request->hasFile('foto_bukti')) {
            $pathFoto = $request->file('foto_bukti')->store('pelanggaran/bukti', 'public');
        }

        // Generate Array of DTOs via static method
        $dtos = PelanggaranSantriDTO::fromRequestCollection($request, $pathFoto);

        // Eksekusi Action Create
        $this->createPelanggaranAction->execute($dtos);

        return redirect()->route('tenant.pelanggaran.index')
            ->with('success', count($dtos) . ' data pelanggaran santri berhasil dicatat.');
    }

    /**
     * Handle Edit/Update Spesifik Pelanggaran (Per Individu)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'judul_pelanggaran' => 'required|string|max:255',
            'poin'              => 'required|integer|min:0',
            'catatan_detail'    => 'nullable|string',
        ]);

        // Eksekusi Action Update
        $this->updatePelanggaranAction->execute((int) $id, $request->only([
            'judul_pelanggaran', 'poin', 'catatan_detail'
        ]));

        return redirect()->route('tenant.pelanggaran.index')
            ->with('success', 'Data pelanggaran berhasil diperbarui.');
    }

    /**
     * Hapus Data Catatan Pelanggaran (Mendukung Soft Deletes)
     */
    public function destroy($id)
    {
        $pondokId = auth()->user()->pondok_id;
        
        // Cari data aktif
        $pelanggaran = PelanggaranSantri::where('pondok_id', $pondokId)->findOrFail($id);
        
        $pelanggaran->delete(); // Mengisi kolom deleted_at

        return redirect()->route('tenant.pelanggaran.index')
            ->with('success', 'Catatan pelanggaran berhasil dipindahkan ke tempat sampah.');
    }
}