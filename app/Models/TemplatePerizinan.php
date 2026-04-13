<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplatePerizinan extends Model
{
    protected $table = 'template_perizinans';

    protected $fillable = [
        'pondok_id',
        'nama',
        'slug',
        'deskripsi',
        'format_surat',
        'required_variables',
        'file_pdf',
        'layout_print',
        'is_default',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'required_variables' => 'array',

    ];

    public function pondok()
    {
        return $this->belongsTo(Pondok::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByPondok($query, $pondokId)
    {
        return $query->where('pondok_id', $pondokId);
    }

    
}