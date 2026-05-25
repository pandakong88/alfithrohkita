<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\AbsensiSesi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

// Import Actions & DTO
use App\Domains\Absensi\Actions\CreateAbsensiAction;
use App\Domains\Absensi\DTO\AbsensiDTO;

// Import Resources
use App\Http\Resources\AbsensiSantriResource;
use App\Http\Resources\AbsensiSesiResource;

class AbsensiController extends Controller
{
    /**
     * 1. Ambil semua sesi untuk dashboard mobile
     */
    public function getSesi(Request $request)
    {
        $sesis = AbsensiSesi::where('pondok_id', auth()->user()->pondok_id)
            ->where('is_active', true)
            ->get();

        return AbsensiSesiResource::collection($sesis);
    }

    /**
     * 2. Ambil daftar santri berdasarkan sesi (untuk Mode Manual)
     */
    public function getSantriBySesi(Request $request, $sesi_id)
    {
        $sesi = AbsensiSesi::findOrFail($sesi_id);
        $tanggal = $request->get('tanggal', date('Y-m-d'));

        // Query dasar santri di pondok ini
        $query = Santri::where('pondok_id', auth()->user()->pondok_id)->active();

        // Filter berdasarkan target tipe sesi (Kelas, Kamar, atau Plotting)
        if ($sesi->target_tipe === 'plotting') {
            $santris = $sesi->santris(); 
        } else {
            if ($sesi->target_tipe === 'kelas') $query->where('kelas_id', $sesi->target_id);
            if ($sesi->target_tipe === 'kamar') $query->where('kamar_id', $sesi->target_id);
            $santris = $query;
        }

        $data = $santris->with(['absensis' => function($q) use ($tanggal, $sesi_id) {
            $q->where('tanggal', $tanggal)->where('sesi_id', $sesi_id);
        }])->orderBy('nama_lengkap', 'asc')->get();

        return AbsensiSantriResource::collection($data);
    }

    /**
     * 3. Scan QR - Ambil data 1 santri spesifik
     */
    public function scanSantri(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required', // Bisa NIS atau ID
            'sesi_id' => 'required|exists:absensi_sesi,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Data tidak valid', 'errors' => $validator->errors()], 422);
        }

        $santri = Santri::where('pondok_id', auth()->user()->pondok_id)
            ->where(function($q) use ($request) {
                $q->where('nis', $request->identifier)->orWhere('id', $request->identifier);
            })
            ->first();

        if (!$santri) {
            return response()->json(['message' => 'Santri tidak ditemukan'], 404);
        }

        return new AbsensiSantriResource($santri);
    }

    /**
     * 4. Simpan Absensi (Manual atau via QR)
     * Menggunakan CreateAbsensiAction & DTO
     */
    public function store(Request $request, CreateAbsensiAction $action)
    {
        $validator = Validator::make($request->all(), [
            'santri_id' => 'required|exists:santris,id',
            'sesi_id'   => 'required|exists:absensi_sesi,id',
            // Samakan dengan Flutter: alfa (tanpa h)
            'status'     => 'required|in:hadir,sakit,izin,alfa,terlambat', 
            'keterangan' => 'nullable|string',
            'metode'     => 'required|in:manual,qr_code',
            // Tambahkan validasi tanggal agar audit berfungsi
            'tanggal'    => 'required|date_format:Y-m-d', 
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }
    
        // 1. Transform request ke DTO tunggal
        $dto = AbsensiDTO::fromRequest($request);
        
        // 2. Eksekusi Action dengan membungkus DTO ke dalam ARRAY
        // Karena Action Anda minta array (mungkin untuk mendukung mass attendance)
        try {
            $absensi = $action->execute([$dto]); // <--- Tambahkan kurung siku di sini
            
            return response()->json([
                'success' => true,
                'message' => 'Absensi berhasil disimpan',
                'data' => $absensi
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan absensi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }
    
        try {
            // Cari data absensi yang sesuai
            $query = \App\Models\Absensi::where([
                'pondok_id' => $user->pondok_id,
                'santri_id' => $request->santri_id,
                'sesi_id'   => $request->sesi_id,
                'tanggal'   => $request->tanggal,
            ]);
    
            // Karena kamu pakai Soft Delete, gunakan forceDelete agar record benar-benar hilang
            // sehingga status santri di sistem kembali menjadi default (Hadir)
            $query->forceDelete();
    
            return response()->json([
                'success' => true, 
                'message' => 'Status dikembalikan ke Hadir'
            ]);
    
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }
}