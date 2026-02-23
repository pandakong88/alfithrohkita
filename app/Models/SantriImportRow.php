<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SantriImportRow extends Model
{
    protected $table = 'santri_import_rows';

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
        return $this->belongsTo(SantriImportBatch::class, 'batch_id');
    }
}