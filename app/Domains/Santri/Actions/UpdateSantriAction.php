<?php

namespace App\Domains\Santri\Actions;

use App\Models\Santri;
use App\Models\Wali;
use App\Domains\Santri\DTO\UpdateSantriData;
use App\Domains\Shared\Actions\LogActivityAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateSantriAction
{
    public function __construct(
        protected LogActivityAction $logActivity
    ) {}

    public function execute(Santri $santri, UpdateSantriData $data): Santri
    {
        return DB::transaction(function () use ($santri, $data) {

            $user = Auth::user();
            $pondokId = $user->pondok_id;

            // 1️⃣ Pastikan santri milik tenant
            if ($santri->pondok_id !== $pondokId) {
                abort(403);
            }

            // 2️⃣ Validasi wali milik tenant
            $wali = Wali::where('id', $data->wali_id)
                ->where('pondok_id', $pondokId)
                ->first();

            if (!$wali) {
                throw ValidationException::withMessages([
                    'wali_id' => 'Wali tidak valid untuk pondok ini.'
                ]);
            }

            // 3️⃣ Validasi unique NIS (kecuali dirinya)
            $exists = Santri::where('pondok_id', $pondokId)
                ->where('nis', $data->nis)
                ->where('id', '!=', $santri->id)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'nis' => 'NIS sudah digunakan di pondok ini.'
                ]);
            }

            // 4️⃣ Snapshot lama
            $oldValues = $santri->getOriginal();

            // 5️⃣ Update
            $santri->update([
                'wali_id'       => $data->wali_id,
                'nis'           => $data->nis,
                'nama_lengkap'  => $data->nama_lengkap,
                'jenis_kelamin' => $data->jenis_kelamin,
                'tempat_lahir'  => $data->tempat_lahir,
                'tanggal_lahir' => $data->tanggal_lahir,
                'alamat'        => $data->alamat,
                'no_hp'         => $data->no_hp,
                'status'        => $data->status,
                'tanggal_masuk' => $data->tanggal_masuk,
                'tanggal_keluar'=> $data->tanggal_keluar,
                'updated_by'    => $user->id,
            ]);

            // 6️⃣ Log activity
            $this->logActivity->execute(
                event: 'santri.updated',
                subject: $santri,
                description: 'Memperbarui data santri',
                oldValues: $oldValues,
                newValues: $santri->fresh()->toArray(),
                meta: [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]
            );

            return $santri->fresh();
        });
    }
}
