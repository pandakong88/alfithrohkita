<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KategoriPelanggaran;
use App\Domains\Pelanggaran\DTO\KategoriPelanggaranDTO;
use App\Domains\Pelanggaran\Actions\CreateKategoriPelanggaranAction;
use App\Domains\Pelanggaran\Actions\UpdateKategoriPelanggaranAction;
use App\Domains\Pelanggaran\Actions\DeleteKategoriPelanggaranAction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class KategoriPelanggaranController extends Controller
{
    /**
     * Menampilkan daftar semua master kategori pelanggaran per pondok (Tenant).
     * Endpoint ini sangat berguna untuk dropdown pilihan pelanggaran di Flutter.
     */
    public function index(): JsonResponse
    {
        $pondokId = auth()->user()->pondok_id;

        $kategori = KategoriPelanggaran::where('pondok_id', $pondokId)
            ->orderBy('tingkat', 'asc')
            ->orderBy('nama_pelanggaran', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar kategori pelanggaran berhasil diambil.',
            'data'    => $kategori
        ], 200);
    }

    /**
     * Menyimpan kategori pelanggaran baru melalui API.
     */
    public function store(Request $request, CreateKategoriPelanggaranAction $action): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_pelanggaran' => 'required|string|max:255',
            'poin'             => 'required|integer|min:1',
            'tingkat'          => 'required|in:ringan,sedang,berat',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Map ke DTO & Eksekusi Action
        $dto = KategoriPelanggaranDTO::fromRequest($request);
        $result = $action->execute($dto);

        return response()->json([
            'success' => true,
            'message' => 'Kategori pelanggaran berhasil ditambahkan.',
            'data'    => $result
        ], 201);
    }

    /**
     * Menampilkan detail satu kategori pelanggaran tertentu.
     */
    public function show(int $id): JsonResponse
    {
        $pondokId = auth()->user()->pondok_id;

        $kategori = KategoriPelanggaran::where('pondok_id', $pondokId)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail kategori pelanggaran berhasil ditemukan.',
            'data'    => $kategori
        ], 200);
    }

    /**
     * Memperbarui kategori pelanggaran melalui API.
     */
    public function update(Request $request, int $id, UpdateKategoriPelanggaranAction $action): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_pelanggaran' => 'required|string|max:255',
            'poin'             => 'required|integer|min:1',
            'tingkat'          => 'required|in:ringan,sedang,berat',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Map ke DTO membawa ID parameter & Eksekusi Action
        $dto = KategoriPelanggaranDTO::fromRequest($request, $id);
        $result = $action->execute($dto);

        return response()->json([
            'success' => true,
            'message' => 'Kategori pelanggaran berhasil diperbarui.',
            'data'    => $result
        ], 200);
    }

    /**
     * Menghapus kategori pelanggaran (Soft Delete).
     */
    public function destroy(int $id, DeleteKategoriPelanggaranAction $action): JsonResponse
    {
        $action->execute($id);

        return response()->json([
            'success' => true,
            'message' => 'Kategori pelanggaran berhasil dihapus.'
        ], 200);
    }
}