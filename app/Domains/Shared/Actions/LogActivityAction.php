<?php 

namespace App\Domains\Shared\Actions;

use App\Models\ActivityLog;

class LogActivityAction
{
    public function execute(
        string $event,
        $subject,
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null,
        array $meta = []
    ): void {

        ActivityLog::create([
            'pondok_id'   => auth()->user()?->pondok_id,
            'causer_id'   => auth()->id(),
            'event'       => $event,
            'subject_type'=> get_class($subject),
            'subject_id'  => $subject->id ?? null,
            'description' => $description,
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
            'meta'        => array_merge([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ], $meta),
        ]);
    }
}
