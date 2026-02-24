<?php 

namespace App\Domains\Tenant\Actions;

use App\Models\Pondok;
use App\Models\ActivityLog;

class ToggleTenantStatusAction
{
    public function execute(Pondok $pondok): Pondok
    {
        $old = $pondok->toArray();
        $pondok->update([
            'is_active' => !$pondok->is_active
        ]);
        $pondok->refresh();

        // Activity Log
        ActivityLog::create([
            'pondok_id' => $pondok->id,
            'causer_id' => auth()->id(),
            'event' => 'toggle',
            'subject_type' => Pondok::class,
            'subject_id' => $pondok->id,
            'description' => 'Toggle status pondok',
            'old_values' => $old,
            'new_values' => $pondok->toArray(),
            'meta' => null,
        ]);

        return $pondok;
    }
}
