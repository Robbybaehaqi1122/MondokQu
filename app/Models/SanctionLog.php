<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SanctionLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'santri_id',
        'sanction_threshold_id',
        'total_points_at_time',
        'triggered_at',
        'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'total_points_at_time' => 'integer',
            'triggered_at' => 'datetime',
            'notified_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function threshold(): BelongsTo
    {
        return $this->belongsTo(SanctionThreshold::class, 'sanction_threshold_id');
    }
}
