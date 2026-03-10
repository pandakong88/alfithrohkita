<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wali;
use Illuminate\Http\Request;
use App\Http\Resources\WaliResource;

class WaliApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Wali::where('pondok_id', auth()->user()->pondok_id)
                     ->withCount('santris');

        // 1. Filter Search (Nama/No HP)
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                  ->orWhere('no_hp', 'like', "%$search%");
            });
        }

        // 2. Filter Berdasarkan Jenis Kelamin Santri
        if ($request->filled('jk_santri')) {
            $query->whereHas('santris', function ($q) use ($request) {
                $q->where('jenis_kelamin', $request->jk_santri);
            });
        }

        $perPage = $request->get('length', 10);
        $walis = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => WaliResource::collection($walis->items()),
            'meta' => [
                'total' => $walis->total(),
                'current_page' => $walis->currentPage(),
                'last_page' => $walis->lastPage(),
            ]
        ]);
    }

    public function show($id)
    {
        $wali = Wali::where('pondok_id', auth()->user()->pondok_id)
            ->with([
                'santris:id,wali_id,nis,nama_lengkap,jenis_kelamin,status'
            ])
            ->withCount('santris')
            ->findOrFail($id);
    
        return response()->json([
            'success' => true,
            'data' => new WaliResource($wali)
        ]);
    }
}