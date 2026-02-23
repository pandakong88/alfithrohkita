<?php
namespace App\Domains\Tenant\Actions;

use App\Models\Pondok;

class UpdateTenantAction
{
    public function execute(Pondok $pondok, array $data): Pondok
    {
        $pondok->update([
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
        ]);

        return $pondok;
    }
}
