<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $fillable = [
        'pondok_id',
        'nama'
    ];

    public function santris()
    {
        return $this->hasMany(Santri::class);
    }
}