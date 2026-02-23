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
        'is_active'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
