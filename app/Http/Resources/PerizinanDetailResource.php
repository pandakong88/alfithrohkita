<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerizinanDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode_surat' => $this->kode_surat,

            'santri' => [
                'nama' => $this->santri->nama_lengkap,
                'jenis_kelamin' => $this->santri->jenis_kelamin,
                'kelas' => $this->santri->kelas->nama ?? '-',
                'kamar' => $this->santri->kamar->nama ?? '-',
                'kompleks' => $this->santri->kamar->kompleks->nama ?? '-',
            ],

            'template' => $this->template->nama ?? '-',

            'tanggal_keluar' => $this->tanggal_keluar,
            'batas_kembali' => $this->batas_kembali,
            'tanggal_kembali' => $this->tanggal_kembali,

            'status' => $this->status,
            'keperluan' => $this->keperluan,

            // 🔥 isi dari template
            'variables' => $this->variables ?? [],
        ];
    }
}