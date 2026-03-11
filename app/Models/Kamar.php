<?php

namespace App\Models;

use App\Models\Lemari;
use Illuminate\Database\Eloquent\Model;

class Kamar extends Model
{
    protected $fillable = [
        'pondok_id',
        'komplek_id',
        'nama',
        'kapasitas'
    ];

    public function komplek()
    {
        return $this->belongsTo(Komplek::class);
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