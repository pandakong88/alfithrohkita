<?php 

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AbsensiSantriResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Cari data absen untuk tanggal yang diminta (default hari ini)
        $tanggal = $request->tanggal ?? date('Y-m-d');
        $sesiId = $request->sesi_id;

        $absenHariIni = $this->absensis
            ->where('tanggal', $tanggal)
            ->where('sesi_id', $sesiId)
            ->first();

        return [
            'id' => $this->id,
            'nama' => $this->nama_lengkap,
            'nis' => $this->nis,
            'foto' => $this->foto_url, // Asumsi ada accessor foto_url
            'kelas' => $this->whenLoaded('kelas', fn() => $this->kelas->nama),
            'kamar' => $this->whenLoaded('kamar', fn() => $this->kamar->nama),
            'absensi' => [
                'status' => $absenHariIni ? $absenHariIni->status : 'hadir',
                'keterangan' => $absenHariIni ? $absenHariIni->keterangan : null,
                'metode' => $absenHariIni ? $absenHariIni->metode : null,
                'waktu_input' => $absenHariIni ? $absenHariIni->created_at->format('H:i') : null,
            ]
        ];
    }
}

