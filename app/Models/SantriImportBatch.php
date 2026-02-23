<?php

namespace App\Models;

use App\Domains\Shared\Traits\BelongsToTenant;
use App\Models\SantriImportRow;
use Illuminate\Database\Eloquent\Model;

class SantriImportBatch extends Model
{
    use BelongsToTenant;

    protected $table = 'santri_import_batches';

    protected $fillable = [
        'pondok_id',
        'uploaded_by',
        'filename',
        'total_rows',
        'valid_rows',
        'invalid_rows',
        'status',
    ];

    public function rows()
    {
        return $this->hasMany(SantriImportRow::class, 'batch_id');
    }

    public function uploader()
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }
}