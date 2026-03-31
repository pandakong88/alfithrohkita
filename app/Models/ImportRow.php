<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportRow extends Model
{
    protected $table = 'import_rows';

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

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function batch()
    {
        return $this->belongsTo(ImportBatch::class, 'batch_id');
    }

    public function changes()
    {
        return $this->hasMany(ImportChange::class,'row_id');
    }
    
}