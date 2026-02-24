<?php
namespace App\Domains\Tenant\Actions;

use App\Models\Pondok;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Storage;
// use Illuminate\Support\Str;

class UpdateTenantAction
{
    public function execute(Pondok $pondok, array $data): Pondok
    {
        $old = $pondok->toArray();

        // Update logo jika ada file baru
        if (!empty($data['logo'])) {
            // Hapus logo lama jika ada
            if ($pondok->logo && Storage::disk('public')->exists($pondok->logo)) {
                Storage::disk('public')->delete($pondok->logo);
            }
            // Upload logo baru
            $logoPath = $data['logo']->store('pondok/logo', 'public');
        } else {
            $logoPath = $pondok->logo;
        }

        $pondok->update([
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
            'logo' => $logoPath,
        ]);

        // Activity Log
        ActivityLog::create([
            'pondok_id' => $pondok->id,
            'causer_id' => auth()->id(),
            'event' => 'update',
            'subject_type' => Pondok::class,
            'subject_id' => $pondok->id,
            'description' => 'Mengubah data pondok',
            'old_values' => $old,
            'new_values' => $pondok->toArray(),
            'meta' => null,
        ]);

        return $pondok;
    }
}
