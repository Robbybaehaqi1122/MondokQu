<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahfidzSession extends Model
{
    use BelongsToTenant;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'tenant_id',
        'santri_id',
        'musyrif_id',
        'session_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
        ];
    }

    public static function availableStatuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_COMPLETED,
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

    public function musyrif(): BelongsTo
    {
        return $this->belongsTo(User::class, 'musyrif_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(TahfidzRecord::class, 'tahfidz_session_id');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_COMPLETED => 'Selesai',
            default => ucfirst($this->status),
        };
    }
}
