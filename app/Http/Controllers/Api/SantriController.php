<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Santri;
use App\Models\Kelas;
use App\Models\Komplek;
use App\Http\Resources\SantriListResource;
use App\Http\Resources\SantriDetailResource;

class SantriController extends Controller
{
    public function index(Request $request)
    {
        $query = Santri::query();
    
        // 1. SEARCH
        $query->when($request->search, function ($q) use ($request) {
            $q->where(function ($sub) use ($request) {
                $sub->where('nama_lengkap', 'like', '%' . $request->search . '%')
                    ->orWhere('nis', 'like', '%' . $request->search . '%');
            });
        });
    
        // 2. FILTER DASAR (ID Based)
        $query->when($request->status, fn($q) => $q->where('status', $request->status));
        $query->when($request->kelas_id, fn($q) => $q->where('kelas_id', $request->kelas_id));
        $query->when($request->kamar_id, fn($q) => $q->where('kamar_id', $request->kamar_id));
    
        // 3. FILTER KOMPLEK (Deep Filtering lewat relasi Kamar)
        $query->when($request->komplek_id, function ($q) use ($request) {
            $q->whereHas('kamar', function ($k) use ($request) {
                $k->where('komplek_id', $request->komplek_id);
            });
        });
    
        // 4. DINAMIS SORTING
        switch ($request->sort) {
            case 'nama_asc':
                $query->orderBy('nama_lengkap', 'asc');
                break;
            case 'nama_desc':
                $query->orderBy('nama_lengkap', 'desc');
                break;
            default:
                $query->latest();
                break;
        }
    
        // RELATION & SELECT (Eager Loading untuk performa)
        $santri = $query->with([
                'kelas:id,nama',
                'kamar:id,nama,komplek_id',
                'kamar.kompleks:id,nama' 
            ])
            ->select('id', 'nama_lengkap', 'nis', 'kelas_id', 'kamar_id', 'jenis_kelamin', 'status')
            ->paginate(10);
    
        return SantriListResource::collection($santri);
    }

    public function show($id)
    {
        $santri = Santri::with([
            'wali:id,nama,no_hp,pekerjaan,alamat',
            'kelas:id,nama',
            'kamar:id,nama,komplek_id',
            'kamar.kompleks:id,nama'
        ])->findOrFail($id);

        return new SantriDetailResource($santri);
    }

    /**
     * Expert Feature: Mengambil data master untuk pilihan filter di Mobile
     */
    public function getFilterData()
    {
        try {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'kelas' => Kelas::select('id', 'nama')->orderBy('nama', 'asc')->get(),
                    'kompleks' => Komplek::select('id', 'nama')->orderBy('nama', 'asc')->get(),
                    // Kamu bisa tambah master data lain di sini nanti
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}