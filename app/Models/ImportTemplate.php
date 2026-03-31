<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportTemplate extends Model
{
    protected $fillable = [
        'pondok_id',
        'nama_template'
    ];

    public function fields()
    {
        return $this->belongsToMany(
            ImportField::class,
            'import_template_fields',
            'template_id',
            'field_id'
        )->withPivot('order');
    }
}