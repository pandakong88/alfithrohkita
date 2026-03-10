<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SantriResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nis' => $this->nis,
            'nama_lengkap' => $this->nama_lengkap, // Gunakan nama_lengkap sesuai DB
            'jenis_kelamin' => $this->jenis_kelamin,
            'tempat_lahir' => $this->tempat_lahir,
            'tanggal_lahir' => $this->tanggal_lahir ? $this->tanggal_lahir->format('d-m-Y') : null,
            'alamat' => $this->alamat,
            'no_hp' => $this->no_hp,
            'status' => $this->status,
            'tanggal_masuk' => $this->tanggal_masuk,

            // Tambahkan ini agar data wali muncul di Flutter
            'wali' => $this->whenLoaded('wali', function() {
                return [
                    'id' => $this->wali->id,
                    'nama' => $this->wali->nama,
                    'no_hp' => $this->wali->no_hp,
                    'pekerjaan' => $this->wali->pekerjaan,
                    'alamat' => $this->wali->alamat,
                ];
            }),
        ];
    }
}
