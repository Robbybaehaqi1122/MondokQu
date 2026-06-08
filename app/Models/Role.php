<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'tenant_id',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeForTenant($query, ?int $tenantId)
    {
        if ($tenantId === null) {
            return $query->whereNull('tenant_id');
        }

        return $query->where('tenant_id', $tenantId);
    }

    protected static function booted(): void
    {
        static::creating(function (Role $role) {
            if (is_null($role->tenant_id) && auth()->check() && ! auth()->user()?->isSuperAdmin()) {
                $role->tenant_id = auth()->user()->tenant_id;
            }
        });
    }
}
