<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Communication extends Model
{
    use BelongsToTenant;

    protected $table = 'communications';

    protected $fillable = [
        'tenant_id',
        'santri_id',
        'user_id',
        'message',
        'direction',
        'is_read',
        'parent_id',
        'is_replied',
        'forwarded_from_id',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'is_replied' => 'boolean',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function forwardedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'forwarded_from_id');
    }

    public function forwardedMessages(): HasMany
    {
        return $this->hasMany(self::class, 'forwarded_from_id');
    }
}
