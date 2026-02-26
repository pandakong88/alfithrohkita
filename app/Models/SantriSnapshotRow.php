<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SantriSnapshotRow extends Model
{
    protected $fillable = [
        'batch_id',
        'row_number',
        'payload',
        'errors',
        'is_valid',
    ];

    protected $casts = [
        'payload' => 'array',
        'errors' => 'array',
        'is_valid' => 'boolean',
    ];

    public function batch()
    {
        return $this->belongsTo(SantriSnapshotBatch::class, 'batch_id');
    }
}