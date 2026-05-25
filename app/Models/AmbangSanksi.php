<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AmbangSanksi extends Model
{
    protected $table = 'ambang_sanksis';

    protected $fillable = [
        'pondok_id',
        'nama_sanksi',
        'minimal_poin',
        'konsekuensi',
    ];

    public function pondok(): BelongsTo
    {
        return $this->belongsTo(Pondok::class, 'pondok_id');
    }
}