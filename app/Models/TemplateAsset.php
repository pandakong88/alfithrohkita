<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateAsset extends Model
{
    protected $fillable = ['pondok_id', 'file_path', 'file_name', 'file_type'];

    // Relasi ke Pondok (Opsional tapi bagus untuk ada)
    public function pondok() {
        return $this->belongsTo(Pondok::class);
    }
}