<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateVariable extends Model
{
    protected $fillable = [
        'key',
        'label',
        'source',
        'type',
        'input_type',
        'is_active',
    ];
}