<?php

namespace App\Models;

use App\Domains\Shared\Traits\BelongsToTenant;
use App\Models\WaliImportRow;
use Illuminate\Database\Eloquent\Model;

class WaliImportBatch extends Model
{
    use BelongsToTenant;

    protected $table = 'wali_import_batches';

    protected $fillable = [
        'pondok_id',
        'uploaded_by',
        'filename',
        'total_rows',
        'valid_rows',
        'invalid_rows',
        'status',
        'committed_by',
        'committed_at',
    ];

    protected $casts = [
        'total_rows' => 'integer',
        'valid_rows' => 'integer',
        'invalid_rows' => 'integer',
    ];

    public function rows()
    {
        return $this->hasMany(WaliImportRow::class, 'batch_id');
    }

    public function uploader()
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }

    public function committer()
    {
        return $this->belongsTo(\App\Models\User::class, 'committed_by');
    }
}
