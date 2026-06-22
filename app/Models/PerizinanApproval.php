<?php

namespace App\Models;

use App\Domains\Shared\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class PerizinanApproval extends Model
{
    use BelongsToTenant;

    protected $table = 'perizinan_approvals';

    protected $fillable = [
        'pondok_id',
        'perizinan_id',
        'step_index',
        'step_name',
        'approved_by',
    ];

    public function perizinan()
    {
        return $this->belongsTo(Perizinan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function pondok()
    {
        return $this->belongsTo(Pondok::class);
    }
}
