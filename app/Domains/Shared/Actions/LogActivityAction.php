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
            // Ditambahkan pengecekan is_object agar aman jika $subject diisi null
            'subject_type'=> is_object($subject) ? get_class($subject) : null,
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
        ActivityLog::create([
            'pondok_id'   => auth()->user()?->pondok_id,
            'causer_id'   => auth()->id(),
            'event'       => 'absensi.batch_update',
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

    /**
     * Method khusus untuk log pencatatan pelanggaran massal/batch
     */
    public function logBatchPelanggaran(int $total, string $sumber): void 
    {
        ActivityLog::create([
            'pondok_id'   => auth()->user()?->pondok_id,
            'causer_id'   => auth()->id(),
            'event'       => 'pelanggaran.batch_create',
            'subject_type'=> 'App\\Models\\PelanggaranSantri', 
            'subject_id'  => 0, 
            'description' => "Mencatat {$total} pelanggaran santri baru dari sumber: {$sumber}.",
            'new_values'  => [
                'total_records' => $total,
                'sumber'        => $sumber,
            ],
            'meta'        => [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'source' => 'pelanggaran_batch_module'
            ],
        ]);
    }
}