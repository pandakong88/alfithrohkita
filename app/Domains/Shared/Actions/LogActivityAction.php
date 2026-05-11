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

    /**
     * Method khusus untuk log absensi massal/batch
     */
    public function logBatchAbsensi(int $sesiId, string $tanggal, int $total): void 
    {
        \App\Models\ActivityLog::create([
            'pondok_id'   => auth()->user()?->pondok_id,
            'causer_id'   => auth()->id(),
            'event'       => 'absensi.batch_update',
            
            // Pakai nama model Absensi agar konsisten dengan format log lainnya
            // ID diisi 0 karena tidak merujuk ke 1 baris absensi saja
            'subject_type'=> 'App\\Models\\Absensi', 
            'subject_id'  => 0, 
            
            'description' => "Update massal absensi Sesi ID: {$sesiId} pada tanggal: {$tanggal}",
            'new_values'  => [
                'total_data' => $total,
                'sesi_id'    => $sesiId,
                'tanggal'    => $tanggal
            ],
            'meta'        => [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'source' => 'absensi_batch_module'
            ],
        ]);
    }
}
