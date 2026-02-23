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

            // ==========================================
            // 1️⃣ Tentukan wali
            // ==========================================

            $waliId = null;

            // 🔹 Mode 1: Wali existing
            if ($data->wali_id) {

                $wali = Wali::where('id', $data->wali_id)
                    ->where('pondok_id', $pondokId)
                    ->first();

                if (!$wali) {
                    throw ValidationException::withMessages([
                        'wali_id' => 'Wali tidak valid untuk pondok ini.'
                    ]);
                }

                $waliId = $wali->id;
            }

            // 🔹 Mode 2: Buat wali baru
            elseif ($data->wali_nama) {

                if (!$data->wali_no_hp) {
                    throw ValidationException::withMessages([
                        'wali_no_hp' => 'Nomor HP wali wajib diisi.'
                    ]);
                }

                $wali = Wali::create([
                    'pondok_id' => $pondokId,
                    'nama' => $data->wali_nama,
                    'no_hp' => $data->wali_no_hp,
                    'alamat' => $data->wali_alamat,
                    'pekerjaan' => $data->wali_pekerjaan,
                    'created_by' => $user->id,
                ]);

                $waliId = $wali->id;
            }

            else {
                throw ValidationException::withMessages([
                    'wali' => 'Pilih wali atau buat wali baru.'
                ]);
            }

            // ==========================================
            // 2️⃣ Validasi unique NIS per pondok
            // ==========================================

            $exists = Santri::where('pondok_id', $pondokId)
                ->where('nis', $data->nis)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'nis' => 'NIS sudah digunakan di pondok ini.'
                ]);
            }

            // ==========================================
            // 3️⃣ Create Santri
            // ==========================================

            $santri = Santri::create([
                'pondok_id'     => $pondokId,
                'wali_id'       => $waliId,
                'nis'           => $data->nis,
                'nama_lengkap'  => $data->nama_lengkap,
                'jenis_kelamin' => $data->jenis_kelamin,
                'tempat_lahir'  => $data->tempat_lahir,
                'tanggal_lahir' => $data->tanggal_lahir,
                'alamat'        => $data->alamat,
                'no_hp'         => $data->no_hp,
                'tanggal_masuk' => $data->tanggal_masuk,
                'status'        => 'active',
                'created_by'    => $user->id,
            ]);

            // ==========================================
            // 4️⃣ Log activity
            // ==========================================

            $this->logActivity->execute(
                event: 'santri.created',
                subject: $santri,
                description: 'Membuat data santri baru',
                oldValues: null,
                newValues: $santri->toArray(),
                meta: [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]
            );

            return $santri;
        });
    }
}