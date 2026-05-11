<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SantriDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nis' => $this->nis,
            'nama_lengkap' => $this->nama_lengkap,
            'jenis_kelamin' => $this->jenis_kelamin,
            'tempat_lahir' => $this->tempat_lahir,
            'tanggal_lahir' => optional($this->tanggal_lahir)->format('d-m-Y'),
            'alamat' => $this->alamat,
            'no_hp' => $this->no_hp,
            'status' => $this->status,
            'tanggal_masuk' => $this->tanggal_masuk,

            'kelas' => $this->whenLoaded('kelas', fn() => [
                'id' => $this->kelas->id,
                'nama' => $this->kelas->nama,
            ]),

            'kamar' => $this->whenLoaded('kamar', fn() => [
                'id' => $this->kamar->id,
                'nama' => $this->kamar->nama,
                'komplek' => $this->kamar->kompleks?->nama,
            ]),

            'wali' => $this->whenLoaded('wali', fn() => [
                'id' => $this->wali->id,
                'nama' => $this->wali->nama,
                'no_hp' => $this->wali->no_hp,
                'pekerjaan' => $this->wali->pekerjaan,
                'alamat' => $this->wali->alamat,
            ]),
        ];
    }
}