<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaliImportRow extends Model
{
    protected $table = 'wali_import_rows';

    protected $fillable = [
        'batch_id',
        'row_number',
        'payload',
        'errors',
        'mode',
        'is_valid',
    ];

    protected $casts = [
        'payload' => 'array',
        'errors' => 'array',
        'is_valid' => 'boolean',
    ];

    public function batch()
    {
        return $this->belongsTo(WaliImportBatch::class, 'batch_id');
    }
}
