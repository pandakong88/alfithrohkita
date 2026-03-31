<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Perizinan extends Model
{
    use HasFactory;

    protected $fillable = [
        'santri_id',
        'template_perizinan_id',
        'tanggal_keluar',
        'batas_kembali',
        'tanggal_kembali',
        'status',
        'keperluan',
        'created_by'
    ];

    protected $casts = [
        'tanggal_keluar' => 'datetime',
        'batas_kembali' => 'datetime',
        'tanggal_kembali' => 'datetime',
    ];

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }

    public function template()
    {
        return $this->belongsTo(TemplatePerizinan::class, 'template_perizinan_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}