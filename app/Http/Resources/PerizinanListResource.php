<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerizinanListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode_surat' => $this->kode_surat,
            'santri' => [
                'nama' => $this->santri->nama_lengkap,
                'nis' => $this->santri->nis,
            ],
            'template_nama' => $this->template->nama ?? 'Umum', 
            
            // Format Raw (Data Asli untuk Logic)
            'tanggal_keluar_raw' => $this->tanggal_keluar?->toDateTimeString(),
            'batas_kembali_raw'  => $this->batas_kembali?->toDateTimeString(),
            'tanggal_kembali_raw'=> $this->tanggal_kembali?->toDateTimeString(),
            
            // Format Cantik (Untuk UI)
            'tanggal_keluar'  => $this->tanggal_keluar?->format('d M Y'),
            'jam_keluar'      => $this->tanggal_keluar?->format('H:i'),
            'batas_kembali'   => $this->batas_kembali?->format('d M Y H:i'),
            'tanggal_kembali' => $this->tanggal_kembali?->format('d M Y H:i'),
            
            // Info Keterlambatan
            'status' => $this->status,
            'status_label' => ucfirst($this->status), // Contoh: "Terlambat"
            'durasi_terlambat_menit' => $this->durasi_terlambat_menit ?? 0,
            
            // Tambahan info biar Flutter gampang styling
            'is_late' => $this->status === 'terlambat',
        ];
    }
}