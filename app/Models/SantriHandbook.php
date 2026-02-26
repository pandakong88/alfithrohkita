<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SantriHandbook extends Model
{
    protected $fillable = [
        'pondok_id',
        'version',
        'release_date',
        'status',
        'description',
        'file_path',
        'created_by',
    ];

    protected $casts = [
        'release_date' => 'date',
    ];
}