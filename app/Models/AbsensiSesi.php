<?php

namespace App\Models;

use App\Domains\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AbsensiSesi extends Model 
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'absensi_sesi';

    protected $fillable = [
        'pondok_id', 
        'nama_sesi', 
        'target_tipe', 
        'target_id',
        'jam_mulai', 
        'jam_selesai',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke data Absensi (Log absensi santri)
     */
    public function absensis(): HasMany 
    {
        return $this->hasMany(Absensi::class, 'sesi_id');
    }

    /**
     * Relasi Many-to-Many untuk tipe Plotting/Manual
     */
    public function santris(): BelongsToMany
    {
        return $this->belongsToMany(Santri::class, 'absensi_sesi_santri', 'absensi_sesi_id', 'santri_id')
                    ->using(AbsensiSesiSantri::class)
                    ->withTimestamps();
    }

    /**
     * Relasi ke Master Kelas jika target_tipe = 'kelas'
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'target_id');
    }

    /**
     * Relasi ke Master Kamar jika target_tipe = 'kamar'
     */
    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class, 'target_id');
    }

    /**
     * Relasi ke Master Komplek jika target_tipe = 'komplek' (TAMBAHAN)
     */
    public function komplek(): BelongsTo
    {
        return $this->belongsTo(Komplek::class, 'target_id');
    }

    /**
     * Scope untuk mengambil hanya sesi yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Helper untuk mempermudah pemanggilan Nama Target (Accessor)
     */
    public function getTargetDisplayNameAttribute()
    {
        return match($this->target_tipe) {
            'kelas'    => $this->kelas->nama ?? 'N/A',
            'kamar'    => $this->kamar->nama ?? 'N/A',
            'komplek'  => $this->komplek->nama ?? 'N/A',
            'plotting' => 'Plotting Santri',
            default    => 'Global',
        };
    }
}