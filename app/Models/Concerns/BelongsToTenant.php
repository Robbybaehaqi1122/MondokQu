<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    /**
     * Bootstrap the tenant ownership concern.
     */
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $query): void {
            $model = $query->getModel();

            if (! $model->usesTenantGlobalScope()) {
                return;
            }

            $user = auth()->user();

            if (! $user) {
                $query->whereRaw('1 = 0');

                return;
            }

            if ($user instanceof User && $user->isSuperAdmin()) {
                return;
            }

            $tenantId = $user instanceof User ? $user->tenant_id : null;

            if (! $tenantId) {
                $query->whereRaw('1 = 0');

                return;
            }

            $query->where($model->qualifyColumn('tenant_id'), $tenantId);
        });
    }

    /**
     * Determine whether this model should be scoped to the current tenant by default.
     */
    public function usesTenantGlobalScope(): bool
    {
        return ! property_exists($this, 'usesTenantGlobalScope') || $this->usesTenantGlobalScope;
    }

    /**
     * Remove the automatic tenant scope when a trusted flow needs cross-tenant data.
     */
    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }

    /**
     * Limit a query to a specific tenant.
     */
    public function scopeForTenant(Builder $query, Tenant|int|null $tenant): Builder
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        if (! $tenantId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($query->qualifyColumn('tenant_id'), $tenantId);
    }

    /**
     * Limit a query to records visible by the selected user.
     */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        return $query->forTenant($user->tenant_id);
    }
}
