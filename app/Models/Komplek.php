<?php

namespace App\Models;

use App\Domains\Shared\Traits\BelongsToTenant;
use App\Models\Kamar;
use Illuminate\Database\Eloquent\Model;

class Komplek extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'pondok_id',
        'nama'
    ];

    public function kamars()
    {
        return $this->hasMany(Kamar::class);
    }
}