<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SantriListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nis' => $this->nis,
            'nama' => $this->nama_lengkap,
            'alamat' => $this->alamat,
            'kelas' => $this->kelas?->nama,
            'kamar' => $this->kamar?->nama,
            'komplek' => $this->kamar?->kompleks?->nama,
            'jenis_kelamin' => $this->jenis_kelamin,
            'status' => $this->status,
        ];
    }
}