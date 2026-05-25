<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    /** @use HasFactory<RoomFactory> */
    use BelongsToTenant, HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'capacity',
        'status',
        'description',
        'created_by',
    ];

    /**
     * Get the casts for the model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
        ];
    }

    /**
     * Get available room statuses.
     *
     * @return array<int, string>
     */
    public static function availableStatuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
        ];
    }

    /**
     * Get the tenant that owns this room.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user that created this room.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get santri assigned to this room.
     */
    public function santris(): HasMany
    {
        return $this->hasMany(Santri::class);
    }

    /**
     * Get transfer records that moved santri from this room.
     */
    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(RoomTransfer::class, 'from_room_id');
    }

    /**
     * Get transfer records that moved santri into this room.
     */
    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(RoomTransfer::class, 'to_room_id');
    }

    /**
     * Resolve whether room still has available capacity.
     */
    public function hasAvailableCapacity(int $additionalSantri = 1): bool
    {
        if (! $this->capacity) {
            return true;
        }

        return $this->santris()
            ->where('status', Santri::STATUS_ACTIVE)
            ->count() + $additionalSantri <= $this->capacity;
    }

    /**
     * Resolve a human-friendly status label.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_INACTIVE => 'Nonaktif',
            default => ucfirst($this->status),
        };
    }
}
