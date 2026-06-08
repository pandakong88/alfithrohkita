<?php

namespace App\Models;

use App\Domains\Shared\Traits\BelongsToTenant;
use App\Models\Lemari;
use Illuminate\Database\Eloquent\Model;

class Kamar extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'pondok_id',
        'komplek_id',
        'nama',
        'kapasitas'
    ];

    public function kompleks()
    {
        // Tambahkan parameter kedua yaitu 'komplek_id'
        return $this->belongsTo(Komplek::class, 'komplek_id');
    }

    public function santris()
    {
        return $this->hasMany(Santri::class);
    }

    public function lemaris()
    {
        return $this->hasMany(Lemari::class);
    }
}