<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LemariSlot extends Model
{
    protected $fillable = [
        'lemari_id',
        'nomor_slot',
        'santri_id',
        'status',
        'keterangan'
    ];

    public function lemari()
    {
        return $this->belongsTo(Lemari::class);
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }
}