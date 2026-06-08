<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Shared\Traits\BelongsToTenant;

class AmbangSanksi extends Model
{
    use BelongsToTenant;
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