<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pondok extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'slug',
        'address',
        'phone',
        'logo',
        'is_active',
        'nis_pattern',
        'nis_auto_generate',
    ];

    protected $casts = [
        'nis_auto_generate' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
