<?php

namespace App\Models;

use App\Domains\Shared\Traits\BelongsToTenant;
use App\Models\Absensi;
use App\Models\Kamar;
use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\Wali;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Santri extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $table = 'santris';

    protected $fillable = [
        'pondok_id',
        'wali_id',
        'kamar_id',
        'nis',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'no_hp',
        'status',
        'tanggal_masuk',
        'tanggal_keluar',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_masuk' => 'date',
        'tanggal_keluar' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function wali()
    {
        return $this->belongsTo(Wali::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function kamar()
    {
        return $this->belongsTo(Kamar::class);
    }

    public function perizinans()
    {
        return $this->hasMany(\App\Models\Perizinan::class);
    }
    public function perizinanAktif()
    {
        return $this->hasOne(\App\Models\Perizinan::class)
            ->where('status', 'aktif');
    }

    public function absensis(): HasMany 
    {
        // Parameter kedua adalah foreign key di tabel absensi
        return $this->hasMany(Absensi::class, 'santri_id');
    }

    /**
     * Relasi ke Sesi Absensi via Tabel Pivot
     */
    public function sesis(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(AbsensiSesi::class, 'absensi_sesi_santri', 'santri_id', 'absensi_sesi_id')
                    ->using(AbsensiSesiSantri::class)
                    ->withTimestamps();
    }
    /**
     * Relasi ke data Pelanggaran
     */
    public function pelanggarans(): HasMany 
    {
        return $this->hasMany(Pelanggaran::class, 'santri_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeByPondok($query, $pondokId = null)
    {
        return $query->where('pondok_id', $pondokId ?? auth()->user()->pondok_id);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeNonActive($query)
    {
        return $query->where('status', 'nonaktif');
    }

    public function scopeLulus($query)
    {
        return $query->where('status', 'lulus');
    }

    public function scopeKeluar($query)
    {
        return $query->where('status', 'keluar');
    }

    public function scopeSedangIzin($query)
    {
        return $query->where('status', 'izin');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nama_lengkap', 'like', "%{$search}%")
              ->orWhere('nis', 'like', "%{$search}%");
        });
    }
}
