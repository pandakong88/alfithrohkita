<?php

namespace App\Models;

use App\Domains\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    use BelongsToTenant;

    protected $table = 'import_batches';

    protected $fillable = [
        'pondok_id',
        'template_id',
        'template_name',
        'uploaded_by',
        'filename',
        'file_path',
        'entity',
        'total_rows',
        'processed_rows',
        'valid_rows',
        'invalid_rows',
        'mode_missing_nis',
        'mode_existing_nis',
        'status',
        'committed_by',
        'committed_at',
    ];

    protected $casts = [
        'total_rows' => 'integer',
        'processed_rows' => 'integer',
        'valid_rows' => 'integer',
        'invalid_rows' => 'integer',
        'committed_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function pondok(): BelongsTo
    {
        return $this->belongsTo(Pondok::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ImportTemplate::class, 'template_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function committer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'committed_by');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(ImportRow::class, 'batch_id');
    }

    public function changes()
    {
        return $this->hasMany(\App\Models\ImportChange::class,'batch_id');
    }
    
}