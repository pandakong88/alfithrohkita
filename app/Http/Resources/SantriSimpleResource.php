<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SantriSimpleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama_lengkap,
            'nis' => $this->nis,
            'jenis_kelamin' => $this->jenis_kelamin,
            'status' => $this->status,
        ];
    }
}