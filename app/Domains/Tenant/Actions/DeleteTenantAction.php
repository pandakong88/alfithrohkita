<?php
namespace App\Domains\Tenant\Actions;

use App\Models\Pondok;
use App\Models\ActivityLog;

class DeleteTenantAction
{
    public function execute(Pondok $pondok): void
    {
        $old = $pondok->toArray();
        $pondok->delete();

        // Activity Log
        ActivityLog::create([
            'pondok_id' => $pondok->id,
            'causer_id' => auth()->id(),
            'event' => 'delete',
            'subject_type' => Pondok::class,
            'subject_id' => $pondok->id,
            'description' => 'Menghapus pondok',
            'old_values' => $old,
            'new_values' => null,
            'meta' => null,
        ]);
    }
}
