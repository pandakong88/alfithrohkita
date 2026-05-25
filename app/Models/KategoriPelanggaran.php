<?php 
namespace App\Models;

use App\Models\PelanggaranSantri;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriPelanggaran extends Model
{
    use SoftDeletes;

    protected $table = 'kategori_pelanggarans';

    protected $fillable = [
        'pondok_id',
        'nama_pelanggaran',
        'poin',
        'tingkat',
    ];

    // Relasi ke Pondok (SaaS Tenant)
    public function pondok(): BelongsTo
    {
        return $this->belongsTo(Pondok::class, 'pondok_id');
    }

    // Relasi ke Log Kejadian Pelanggaran
    public function pelanggaranSantris(): HasMany
    {
        return $this->hasMany(PelanggaranSantri::class, 'kategori_id');
    }
}