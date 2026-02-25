<?php

namespace App\Domains\Santri\Actions;

use App\Models\Santri;
use App\Models\Wali;
use App\Domains\Santri\DTO\CreateSantriData;
use App\Domains\Shared\Actions\LogActivityAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateSantriAction
{
    public function __construct(
        protected LogActivityAction $logActivity
    ) {}

    public function execute(CreateSantriData $data): Santri
    {
        return DB::transaction(function () use ($data) {

            $user = Auth::user();
            $pondokId = $user->pondok_id;

            // Validasi wali milik pondok
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

            try {
                $santri = Santri::create([
                    'pondok_id' => $pondokId,
                    'wali_id' => $data->wali_id,
                    'nis' => $data->nis,
                    'nama_lengkap' => $data->nama_lengkap,
                    'jenis_kelamin' => $data->jenis_kelamin,
                    'tempat_lahir' => $data->tempat_lahir,
                    'tanggal_lahir' => $data->tanggal_lahir,
                    'alamat' => $data->alamat,
                    'no_hp' => $data->no_hp,
                    'tanggal_masuk' => $data->tanggal_masuk,
                    'status' => 'active',
                    'created_by' => $user->id,
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                throw ValidationException::withMessages([
                    'nis' => 'NIS sudah digunakan di pondok ini.'
                ]);
            }

            $this->logActivity->execute(
                event: 'santri.created',
                subject: $santri,
                description: 'Membuat santri baru',
                newValues: $santri->toArray()
            );

            return $santri;
        });
    }
}