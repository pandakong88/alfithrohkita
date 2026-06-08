<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Shared\Traits\BelongsToTenant;

class Kelas extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'pondok_id',
        'nama'
    ];

    public function santris()
    {
        return $this->hasMany(Santri::class);
    }
}