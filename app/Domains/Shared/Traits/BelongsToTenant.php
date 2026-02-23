<?php

namespace App\Domains\Shared\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant()
    {
        // Auto set pondok_id saat create
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->pondok_id) {
                $model->pondok_id = auth()->user()->pondok_id;
            }
        });

        // Global tenant scope
        static::addGlobalScope('tenant', function (Builder $builder) {

            if (!auth()->check()) {
                return;
            }

            // Super admin boleh lihat semua
            if (auth()->user()->hasRole('super_admin')) {
                return;
            }

            // Selain super admin, wajib filter pondok_id
            $builder->where(
                $builder->getModel()->getTable() . '.pondok_id',
                auth()->user()->pondok_id
            );
        });
    }
}
