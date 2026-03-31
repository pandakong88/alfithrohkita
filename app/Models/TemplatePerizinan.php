<?php

namespace App\Models;

use App\Models\Perizinan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplatePerizinan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'deskripsi',
        'is_active'
    ];

    public function perizinans()
    {
        return $this->hasMany(Perizinan::class);
    }
}