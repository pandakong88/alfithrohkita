<?php

namespace App\Domains\Wali\Actions;

use App\Models\Wali;
use App\Domains\Wali\DTO\UpdateWaliData;
use App\Domains\Shared\Actions\LogActivityAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateWaliAction
{
    public function __construct(
        protected LogActivityAction $logActivity
    ) {}

    public function execute(Wali $wali, UpdateWaliData $data): Wali
    {
        return DB::transaction(function () use ($wali, $data) {

            $user = Auth::user();

            $exists = Wali::where('pondok_id', $user->pondok_id)
                ->where('no_hp', $data->no_hp)
                ->where('id', '!=', $wali->id)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'no_hp' => 'Nomor HP sudah digunakan wali lain.'
                ]);
            }

            $oldValues = $wali->toArray();

            $wali->update([
                'nama' => $data->nama,
                'nik' => $data->nik,
                'no_hp' => $data->no_hp,
                'alamat' => $data->alamat,
                'pekerjaan' => $data->pekerjaan,
                'updated_by' => $user->id,
            ]);

            $this->logActivity->execute(
                event: 'wali.updated',
                subject: $wali,
                description: 'Memperbarui data wali',
                oldValues: $oldValues,
                newValues: $wali->fresh()->toArray()
            );

            return $wali;
        });
    }
}