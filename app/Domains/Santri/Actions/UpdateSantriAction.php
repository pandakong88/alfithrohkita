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

            if ($data->wali_id) {
                $waliValid = Wali::where('id', $data->wali_id)
                    ->where('pondok_id', $pondokId)
                    ->exists();

                if (!$waliValid) {
                    throw ValidationException::withMessages([
                        'wali_id' => 'Wali tidak valid.'
                    ]);
                }
            }

            $oldValues = $santri->toArray();

            try {
                $santri->update([
                    'nis' => $data->nis,
                    'nama_lengkap' => $data->nama_lengkap,
                    'jenis_kelamin' => $data->jenis_kelamin,
                    'tempat_lahir' => $data->tempat_lahir,
                    'tanggal_lahir' => $data->tanggal_lahir,
                    'alamat' => $data->alamat,
                    'no_hp' => $data->no_hp,
                    'status' => $data->status,
                    'tanggal_masuk' => $data->tanggal_masuk,
                    'tanggal_keluar' => $data->tanggal_keluar,
                    'wali_id' => $data->wali_id,
                    'updated_by' => $user->id,
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                throw ValidationException::withMessages([
                    'nis' => 'NIS sudah digunakan.'
                ]);
            }

            $this->logActivity->execute(
                event: 'santri.updated',
                subject: $santri,
                description: 'Memperbarui santri',
                oldValues: $oldValues,
                newValues: $santri->fresh()->toArray()
            );

            return $santri;
        });
    }
}