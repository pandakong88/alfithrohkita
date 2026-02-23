<?php
namespace App\Domains\Tenant\Services;

use App\Models\Pondok;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class PondokService
{
    public function create(array $data)
    {
        $pondok = Pondok::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
        ]);

        $admin = User::create([
            'name' => $data['admin_name'],
            'email' => $data['admin_email'],
            'password' => Hash::make($data['admin_password']),
            'role' => 'admin_pondok',
            'pondok_id' => $pondok->id,
        ]);

        return $pondok;
    }
}
