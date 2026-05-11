<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;

class Perizinan extends Model
{
    use BelongsToTenant;

    protected $table = 'perizinans';

    protected $fillable = [
        'pondok_id',
        'santri_id',
        'template_perizinan_id',
        'kode_surat',
        'nomor_manual',
        'tanggal_keluar',
        'batas_kembali',
        'tanggal_kembali',
        'durasi_terlambat_menit',
        'status',
        'keperluan',
        'keterangan',
        'variables',       // 🔥 WAJIB TAMBAH: Untuk menampung input dinamis
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_keluar' => 'datetime',
        'batas_kembali' => 'datetime',
        'tanggal_kembali' => 'datetime',
        'variables' => 'array', // 🔥 WAJIB TAMBAH: Agar otomatis jadi Array saat diakses di PHP
    ];

    protected $appends = [
        'status_label',
        'is_terlambat',
        'durasi_terlambat_human', // Tambahkan ini agar bisa langsung dipanggil di Blade
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function template()
    {
        return $this->belongsTo(TemplatePerizinan::class, 'template_perizinan_id');
    }

    public function pondok()
    {
        return $this->belongsTo(Pondok::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR (SMART STATUS)
    |--------------------------------------------------------------------------
    */

    public function getIsTerlambatAttribute(): bool
    {
        return is_null($this->tanggal_kembali)
            && now()->greaterThan($this->batas_kembali);
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status === 'dibatalkan') {
            return 'Dibatalkan';
        }

        if ($this->tanggal_kembali) {
            return 'Sudah Kembali';
        }

        if ($this->is_terlambat) {
            return 'Terlambat';
        }

        return 'Sedang Izin';
    }

    /*
|--------------------------------------------------------------------------
    | AUTO STATUS (HOOK)
    |--------------------------------------------------------------------------
    | Sedikit tips: Gunakan now() di luar closure jika ingin performa lebih cepat, 
    | atau biarkan di dalam agar real-time saat saving.
    */
    protected static function booted()
    {
        static::saving(function ($model) {
            if ($model->status === 'dibatalkan') {
                return;
            }

            if ($model->tanggal_kembali) {
                $model->status = 'kembali';
            } elseif (now()->greaterThan($model->batas_kembali)) {
                $model->status = 'terlambat';
            } else {
                $model->status = 'aktif';
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeByPondok(Builder $query, $pondokId)
    {
        return $query->where('pondok_id', $pondokId);
    }
    public function scopeAktif(Builder $query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeKembali(Builder $query)
    {
        return $query->where('status', 'kembali');
    }

    public function scopeDibatalkan(Builder $query)
    {
        return $query->where('status', 'dibatalkan');
    }

    public function scopeBelumKembali(Builder $query)
    {
        return $query->whereNull('tanggal_kembali');
    }

    public function scopeTerlambat(Builder $query)
    {
        return $query->whereNull('tanggal_kembali')
            ->where('batas_kembali', '<', now());
    }

    public function scopeHariIni(Builder $query)
    {
        return $query->whereDate('tanggal_keluar', today());
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------------
    */

    public function tandaiKembali()
    {
        $this->update([
            'tanggal_kembali' => now(),
            'status' => 'kembali'
        ]);
    }

    public function batalkan()
    {
        $this->update([
            'status' => 'dibatalkan'
        ]);
    }

    public function isTerlambat()
    {
        return $this->status === 'aktif' && now()->gt($this->batas_kembali);
    }
    public function getDurasiTerlambatAttribute()
    {
        if (!$this->durasi_terlambat_menit) return null;

        $jam = floor($this->durasi_terlambat_menit / 60);
        $menit = $this->durasi_terlambat_menit % 60;

        return "{$jam} jam {$menit} menit";
    }

    public function getDurasiTerlambatHumanAttribute()
    {
        if (!$this->durasi_terlambat_menit) return '-';
        
        $menit = $this->durasi_terlambat_menit;
        $hari = floor($menit / 1440);
        $jam = floor(($menit % 1440) / 60);
        $sisaMenit = $menit % 60;

        return ($hari > 0 ? "$hari Hari " : "") . ($jam > 0 ? "$jam Jam " : "") . "$sisaMenit Menit";
    }
}