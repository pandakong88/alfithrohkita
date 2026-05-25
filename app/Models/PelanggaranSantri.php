<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PelanggaranSantri extends Model
{
    use SoftDeletes;

    protected $table = 'pelanggaran_santris';

    protected $fillable = [
        'pondok_id',
        'santri_id',
        'absensi_id',
        'kategori_id',
        'kategori_sumber',
        'judul_pelanggaran',
        'poin',
        'tanggal',
        'catatan_detail',
        'foto_bukti',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function pondok(): BelongsTo
    {
        return $this->belongsTo(Pondok::class, 'pondok_id');
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function kategoriPelanggaran(): BelongsTo
    {
        return $this->belongsTo(KategoriPelanggaran::class, 'kategori_id');
    }

    public function absensi(): BelongsTo
    {
        return $this->belongsTo(Absensi::class, 'absensi_id');
    }

    // Pengurus yang menginput pelanggaran
    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}