<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WaliResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'nik' => $this->nik,
            'no_hp' => $this->no_hp,
            'alamat' => $this->alamat,
            'pekerjaan' => $this->pekerjaan,

            'santri_count' => $this->santris_count ?? $this->santris->count(),

            'santris' => SantriSimpleResource::collection(
                $this->whenLoaded('santris')
            ),
        ];
    }
}