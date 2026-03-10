<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSantriRequest;
use App\Http\Requests\UpdateSantriRequest;
use App\Http\Resources\SantriResource;
use App\Models\Santri;
use App\Domains\Santri\DTO\CreateSantriData;
use App\Domains\Santri\DTO\UpdateSantriData;
use App\Domains\Santri\Actions\CreateSantriAction;
use App\Domains\Santri\Actions\UpdateSantriAction;

class SantriController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $query = Santri::with('wali')->where('pondok_id', auth()->user()->pondok_id);

        // 1. Filter Search (Nama/NIS) - Logika Hybrid yang tadi
        $searchValue = is_array($request->search) ? ($request->search['value'] ?? null) : $request->search;
        if ($searchValue) {
            $query->where(function($q) use ($searchValue) {
                $q->where('nama_lengkap', 'like', "%{$searchValue}%") // Sesuaikan dengan nama_lengkap
                ->orWhere('nis', 'like', "%{$searchValue}%");
            });
        }

        // 2. Filter Jenis Kelamin (L/P)
        $query->when($request->filled('jk'), function ($q) use ($request) {
            return $q->where('jenis_kelamin', $request->jk);
        });

        // 3. Filter Status (active/nonaktif)
        $query->when($request->filled('status'), function ($q) use ($request) {
            return $q->where('status', $request->status);
        });

        // 4. Filter Tahun Masuk (Berdasarkan created_at jika tanggal_masuk null)
        $query->when($request->filled('tahun'), function ($q) use ($request) {
            return $q->whereYear('created_at', $request->tahun);
        });

        $perPage = $request->get('length', $request->get('per_page', 10));
        $santri = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => SantriResource::collection($santri),
            'meta' => [
                'current_page' => $santri->currentPage(),
                'last_page' => $santri->lastPage(),
                'total' => $santri->total(),
            ],
            'draw' => intval($request->draw),
        ]);
    }

    public function store(StoreSantriRequest $request)
    {
        $dto = CreateSantriData::fromArray($request->validated());

        $santri = app(CreateSantriAction::class)->execute($dto);

        return response()->json([
            'success' => true,
            'message' => 'Santri berhasil ditambahkan',
            'data' => new SantriResource($santri)
        ], 201);
    }

    public function show(Santri $santri)
    {
        $this->authorizeSantri($santri);
    
        // Tambahkan baris ini untuk menarik data wali
        $santri->load('wali'); 
    
        return response()->json([
            'success' => true,
            'data' => new SantriResource($santri)
        ]);
    }

    public function update(UpdateSantriRequest $request, Santri $santri)
    {
        $this->authorizeSantri($santri);

        $dto = UpdateSantriData::fromArray($request->validated());

        $santri = app(UpdateSantriAction::class)->execute($santri, $dto);

        return response()->json([
            'success' => true,
            'message' => 'Santri berhasil diupdate',
            'data' => new SantriResource($santri)
        ]);
    }

    public function destroy(Santri $santri)
    {
        $this->authorizeSantri($santri);

        $santri->delete();

        return response()->json([
            'success' => true,
            'message' => 'Santri berhasil dihapus'
        ]);
    }

    private function authorizeSantri(Santri $santri): void
    {
        if ($santri->pondok_id !== auth()->user()->pondok_id) {
            abort(403, 'Akses ditolak.');
        }
    }
}