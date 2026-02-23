<?php
namespace App\Domains\Tenant\Actions;

use App\Models\Pondok;

class DeleteTenantAction
{
    public function execute(Pondok $pondok): void
    {
        $pondok->delete();
    }
}
