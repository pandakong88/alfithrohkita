<?php

namespace App\Models;

use App\Domains\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pelanggaran extends Model 
{
    use BelongsToTenant, SoftDeletes;

    // Pastikan ini sesuai dengan nama tabel di file migrasi Anda
    protected $table = 'pelanggarans'; 
    
    protected $fillable = [
        'pondok_id', // WAJIB ada agar Trait BelongsToTenant bisa menyimpan ID pondok
        'santri_id', 
        'absensi_id', 
        'judul_pelanggaran', 
        'poin', 
        'tanggal',
        'keterangan'
    ];

    /**
     * Relasi ke Santri
     */
    public function santri(): BelongsTo 
    {
        // Laravel akan otomatis mencari 'santri_id'
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    /**
     * Relasi ke Absensi
     */
    public function absensi(): BelongsTo 
    {
        return $this->belongsTo(Absensi::class, 'absensi_id');
    }
}