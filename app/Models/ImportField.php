<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportField extends Model
{
    protected $fillable = [
        'field_key',
        'label',
        'entity',
        'column_name',
        'is_required'
    ];
}
