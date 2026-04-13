<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Shared\Traits\BelongsToTenant;

class Perizinan extends Model
{
    use BelongsToTenant;

    protected $table = 'perizinans';

    protected $fillable = [
        'pondok_id',
        'santri_id',
        'template_perizinan_id',
        'tanggal_keluar',
        'batas_kembali',
        'tanggal_kembali',
        'status',
        'keperluan',
        'keterangan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_keluar' => 'datetime',
        'batas_kembali' => 'datetime',
        'tanggal_kembali' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
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
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeTerlambat($query)
    {
        return $query->where('status', 'terlambat');
    }

    public function scopeBelumKembali($query)
    {
        return $query->whereNull('tanggal_kembali');
    }

}