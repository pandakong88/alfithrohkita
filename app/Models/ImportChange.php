<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportChange extends Model
{
    protected $fillable = [
        'batch_id',
        'row_id',
        'entity',
        'entity_id',
        'column_name',
        'old_value',
        'new_value'
    ];

    public function batch()
    {
        return $this->belongsTo(ImportBatch::class);
    }

    public function row()
    {
        return $this->belongsTo(ImportRow::class);
    }
}