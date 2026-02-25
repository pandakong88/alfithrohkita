<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domains\Shared\Traits\BelongsToTenant;


class Wali extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $table = 'walis';

    protected $fillable = [
        'pondok_id',
        'nama',
        'nik',
        'no_hp',
        'alamat',
        'pekerjaan',
        'created_by',
        'updated_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function pondok()
    {
        return $this->belongsTo(Pondok::class);
    }
    public function santris()
    {
        return $this->hasMany(Santri::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nama', 'like', "%{$search}%")
              ->orWhere('no_hp', 'like', "%{$search}%");
        });
    }
}
