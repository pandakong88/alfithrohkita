<?php

namespace App\Http\Controllers\Api;

use App\Domains\Absensi\Actions\CreateAbsensiAction;
use App\Domains\Absensi\DTO\AbsensiData; // Pastikan namespace ini sesuai dengan file DTO Anda
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AbsensiController extends Controller
{
    /**
     * Get list absensi berdasarkan filter
     */
    public function index(Request $request)
    {
        // PERBAIKAN: Nama relasi adalah 'santri' (dari Model Absensi)
        // PERBAIKAN: Kolom di tabel santris adalah 'nama_lengkap', bukan 'nama'
        $absensi = Absensi::with(['santri:id,nama_lengkap,nis', 'sesi'])
            ->when($request->tanggal, fn($q) => $q->where('tanggal', $request->tanggal))
            ->when($request->sesi_id, fn($q) => $q->where('sesi_id', $request->sesi_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $absensi
        ]);
    }

    /**
     * Store atau Update Absensi (Single atau Batch)
     */
    public function store(Request $request, CreateAbsensiAction $action)
    {
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'sesi_id' => 'required|exists:absensi_sesi,id',
            
            // PERBAIKAN: exists merujuk ke tabel 'santris' (plural)
            'data_absensi'             => 'nullable|array',
            'data_absensi.*.santri_id' => 'required|exists:santris,id',
            'data_absensi.*.status'    => 'required|in:hadir,sakit,izin,alfa,terlambat',
            
            // PERBAIKAN: exists merujuk ke tabel 'santris' (plural)
            'santri_id' => 'nullable|required_without:data_absensi|exists:santris,id',
            'status'    => 'nullable|required_without:data_absensi|in:hadir,sakit,izin,alfa,terlambat',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // 2. Mapping ke DTO
        if ($request->has('data_absensi')) {
            // Skenario REKAP MANUAL (Array)
            $dtos = collect($request->data_absensi)->map(function ($item) use ($request) {
                return new AbsensiData(
                    santri_id: $item['santri_id'],
                    sesi_id: $request->sesi_id,
                    tanggal: $request->tanggal,
                    status: $item['status'],
                    metode: $request->metode ?? 'manual',
                    input_by: auth()->id(),
                    keterangan: $item['keterangan'] ?? null
                );
            })->toArray();
            
            $result = $action->execute($dtos);
        } else {
            // Skenario SCAN QR (Single)
            $dto = AbsensiData::fromRequest($request);
            $result = $action->execute($dto);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data absensi berhasil disimpan',
            'data'    => $result
        ]);
    }
}