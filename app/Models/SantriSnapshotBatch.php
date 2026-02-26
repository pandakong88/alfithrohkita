<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SantriSnapshotBatch extends Model
{
    protected $fillable = [
        'pondok_id',
        'uploaded_by',
        'snapshot_date',
        'filename',
        'total_rows',
        'valid_rows',
        'invalid_rows',
        'status',
        'committed_at',
        'committed_by',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
    ];

    public function rows()
    {
        return $this->hasMany(SantriSnapshotRow::class, 'batch_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}