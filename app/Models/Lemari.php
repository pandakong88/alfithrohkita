<?php

namespace App\Models;

use App\Domains\Shared\Traits\BelongsToTenant;
use App\Models\LemariSlot;
use Illuminate\Database\Eloquent\Model;

class Lemari extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'pondok_id',
        'kamar_id',
        'nama',
        'tipe',
        'jumlah_slot'
    ];

    public function kamar()
    {
        return $this->belongsTo(Kamar::class);
    }

    public function slots()
    {
        return $this->hasMany(LemariSlot::class);
    }
}