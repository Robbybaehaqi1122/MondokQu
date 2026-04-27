<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
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
