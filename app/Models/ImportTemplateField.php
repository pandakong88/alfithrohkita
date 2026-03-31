<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportTemplateField extends Model
{
    protected $fillable = [
        'template_id',
        'field_id',
        'order'
    ];
}