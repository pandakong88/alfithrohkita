<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PelanggaranSantri;
use App\Domains\Pelanggaran\DTO\PelanggaranSantriDTO;
use App\Domains\Pelanggaran\Actions\CreatePelanggaranAction;
use App\Domains\Pelanggaran\Actions\UpdatePelanggaranAction; // Import di atas
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PelanggaranSantriController extends Controller
{
    /**
     * Mencatat pelanggaran baru (Mendukung Tunggal maupun Berkelompok)
     */
    public function store(Request $request, CreatePelanggaranAction $action): JsonResponse
    {
        // Validasi input dari Flutter / Web
        $validator = Validator::make($request->all(), [
            'santri_ids'        => 'required|array',
            'santri_ids.*'      => 'required|integer|exists:santris,id',
            'kategori_id'       => 'nullable|integer|exists:kategori_pelanggarans,id',
            'judul_pelanggaran' => 'required|string|max:255',
            'poin'              => 'required|integer|min:0',
            'tanggal'           => 'nullable|date',
            'catatan_detail'    => 'nullable|string',
            'foto_bukti'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Maksimal 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 422);
        }

        // 1. Handle upload foto jika ada (Hanya upload 1 kali meski santrinya banyak)
        $pathFoto = null;
        if ($request->hasFile('foto_bukti')) {
            $pathFoto = $request->file('foto_bukti')->store('bukti-pelanggaran', 'public');
        }

        // 2. Transform ke kumpulan DTO
        $dtos = PelanggaranSantriDTO::fromRequestCollection($request, $pathFoto);

        // 3. Eksekusi Action massal
        $action->execute($dtos);

        return response()->json([
            'success' => true,
            'message' => 'Pelanggaran santri berhasil dicatat.',
            'total_santri' => count($dtos)
        ], 201);
    }


    public function update(Request $request, int $id, UpdatePelanggaranAction $action): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'judul_pelanggaran' => 'required|string|max:255',
            'poin'              => 'required|integer|min:0',
            'catatan_detail'    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $result = $action->execute($id, $request->only(['judul_pelanggaran', 'poin', 'catatan_detail']));

        return response()->json([
            'success' => true,
            'message' => 'Catatan pelanggaran berhasil diperbarui.',
            'data'    => $result
        ], 200);
    }
    /**
     * Menghapus catatan pelanggaran santri (Soft Delete)
     * Otomatis mengurangi poin kumulatif berjalan santri tersebut.
     */
    public function destroy(int $id): JsonResponse
    {
        $pondokId = auth()->user()->pondok_id;

        $pelanggaran = PelanggaranSantri::where('pondok_id', $pondokId)
            ->findOrFail($id);

        $pelanggaran->delete();

        return response()->json([
            'success' => true,
            'message' => 'Catatan pelanggaran berhasil dihapus.'
        ], 200);
    }
}