<?php 

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class AbsensiSesiResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama_sesi,
            'jam_mulai' => $this->jam_mulai,
            'jam_selesai' => $this->jam_selesai,
            'target_tipe' => $this->target_tipe, // kelas/kamar/plotting
        ];
    }
}