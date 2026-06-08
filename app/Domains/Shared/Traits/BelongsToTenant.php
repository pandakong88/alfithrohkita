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
                if (!$model->pondok_id) {
                    $model->pondok_id = auth()->user()->pondok_id;
                }
            }
        });

        // Global tenant scope
        static::addGlobalScope('tenant', function (Builder $builder) {

            if (app()->runningInConsole()) {
                return;
            }

            if (!auth()->check()) {
                return;
            }

            $user = auth()->user();

            // Super admin boleh lihat semua (Bypass global scope untuk mencegah loop tak terbatas)
            $isSuperAdmin = $user->roles()
                ->withoutGlobalScopes()
                ->where('name', 'super_admin')
                ->exists();

            if ($isSuperAdmin) {
                return;
            }

            $builder->where(
                $builder->getModel()->getTable() . '.pondok_id',
                $user->pondok_id
            );
        });
    }
}