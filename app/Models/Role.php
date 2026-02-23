<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'pondok_id',
    ];

    public function pondok()
    {
        return $this->belongsTo(Pondok::class);
    }
}
