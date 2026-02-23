<?php 

namespace App\Domains\Tenant\Actions;

use App\Models\Pondok;

class ToggleTenantStatusAction
{
    public function execute(Pondok $pondok): Pondok
    {
        $pondok->update([
            'is_active' => !$pondok->is_active
        ]);

        return $pondok;
    }
}
