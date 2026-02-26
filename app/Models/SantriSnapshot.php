<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SantriSnapshot extends Model
{
    protected $fillable = [
        'pondok_id',
        'santri_id',
        'snapshot_date',
        'status',
        'kelas',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
    ];

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }
}
