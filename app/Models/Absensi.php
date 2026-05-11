<?php

namespace App\Models;

use App\Domains\Shared\Traits\BelongsToTenant;
// Hapus import yang tidak perlu jika berada di namespace yang sama
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model 
{
    use BelongsToTenant, SoftDeletes;

    // Nama tabel sudah benar sesuai error migrasi sebelumnya
    protected $table = 'absensi';

    protected $fillable = [
        'pondok_id', 
        'santri_id', 
        'sesi_id', 
        'tanggal', 
        'status', 
        'metode', 
        'input_by', 
        'keterangan',
        'pondok_id' // Pastikan ini ada karena Anda pakai BelongsToTenant
    ];

    /**
     * Relasi ke Santri
     * Karena nama tabelnya 'santris' (plural), Laravel akan otomatis 
     * mencari 'santri_id' di sini. Ini sudah aman.
     */
    public function santri(): BelongsTo 
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    /**
     * Relasi ke Sesi
     */
    public function sesi(): BelongsTo 
    {
        return $this->belongsTo(AbsensiSesi::class, 'sesi_id');
    }

    /**
     * Logic Otomatis Pelanggaran
     */
    protected static function booted() 
    {
        static::saved(function ($absensi) {
            if ($absensi->status === 'alfa') {
                // Gunakan updateOrCreate untuk mencegah duplikasi poin
                Pelanggaran::updateOrCreate(
                    ['absensi_id' => $absensi->id],
                    [
                        'santri_id' => $absensi->santri_id,
                        'pondok_id' => $absensi->pondok_id, // Penting untuk SaaS
                        'judul_pelanggaran' => 'Ketidakhadiran Sesi ' . ($absensi->sesi->nama_sesi ?? 'Umum'),
                        'poin' => 5,
                        'tanggal' => $absensi->tanggal,
                    ]
                );
            } else {
                // Jika status berubah jadi hadir/izin, hapus catatan pelanggaran terkait
                Pelanggaran::where('absensi_id', $absensi->id)->delete();
            }
        });

        // Tambahkan logic deleted untuk membersihkan pelanggaran jika data absensi di-delete
        static::deleted(function ($absensi) {
            Pelanggaran::where('absensi_id', $absensi->id)->delete();
        });
    }
}