<?php

namespace App\Domains\Wali\Actions;

use App\Models\Wali;
use App\Domains\Wali\DTO\CreateWaliData;
use App\Domains\Shared\Actions\LogActivityAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateWaliAction
{
    public function __construct(
        protected LogActivityAction $logActivity
    ) {}

    public function execute(CreateWaliData $data): Wali
    {
        return DB::transaction(function () use ($data) {

            $user = Auth::user();
            $pondokId = $user->pondok_id;

            $exists = Wali::where('pondok_id', $pondokId)
                ->where('no_hp', $data->no_hp)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'no_hp' => 'Nomor HP wali sudah terdaftar di pondok ini.'
                ]);
            }

            $wali = Wali::create([
                'pondok_id' => $pondokId,
                'nama' => $data->nama,
                'nik' => $data->nik,
                'no_hp' => $data->no_hp,
                'alamat' => $data->alamat,
                'pekerjaan' => $data->pekerjaan,
                'created_by' => $user->id,
            ]);

            $this->logActivity->execute(
                event: 'wali.created',
                subject: $wali,
                description: 'Membuat wali baru',
                oldValues: null,
                newValues: $wali->toArray()
            );

            return $wali;
        });
    }
}