<?php

namespace App\Models;

use App\Domains\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AbsensiSesiSantri extends Pivot
{
    use BelongsToTenant;

    protected $table = 'absensi_sesi_santri';

    protected $fillable = [
        'pondok_id',
        'absensi_sesi_id',
        'santri_id'
    ];

    public function santris()
    {
        return $this->belongsToMany(Santri::class, 'absensi_sesi_santri', 'absensi_sesi_id', 'santri_id');
    }

}