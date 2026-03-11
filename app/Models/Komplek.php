<?php

namespace App\Models;

use App\Models\Kamar;
use Illuminate\Database\Eloquent\Model;

class Komplek extends Model
{
    protected $fillable = [
        'pondok_id',
        'nama'
    ];

    public function kamars()
    {
        return $this->hasMany(Kamar::class);
    }
}