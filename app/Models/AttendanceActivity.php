<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\AttendanceActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceActivity extends Model
{
    /** @use HasFactory<AttendanceActivityFactory> */
    use BelongsToTenant, HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const DAY_MONDAY = 'monday';

    public const DAY_TUESDAY = 'tuesday';

    public const DAY_WEDNESDAY = 'wednesday';

    public const DAY_THURSDAY = 'thursday';

    public const DAY_FRIDAY = 'friday';

    public const DAY_SATURDAY = 'saturday';

    public const DAY_SUNDAY = 'sunday';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'start_time',
        'end_time',
        'active_days',
        'responsible_user_id',
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
            'active_days' => 'array',
        ];
    }

    /**
     * Get available activity statuses.
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
     * Get supported active day keys.
     *
     * @return array<int, string>
     */
    public static function availableDayKeys(): array
    {
        return array_keys(self::dayLabels());
    }

    /**
     * Get day labels in display order.
     *
     * @return array<string, string>
     */
    public static function dayLabels(): array
    {
        return [
            self::DAY_MONDAY => 'Senin',
            self::DAY_TUESDAY => 'Selasa',
            self::DAY_WEDNESDAY => 'Rabu',
            self::DAY_THURSDAY => 'Kamis',
            self::DAY_FRIDAY => 'Jumat',
            self::DAY_SATURDAY => 'Sabtu',
            self::DAY_SUNDAY => 'Ahad',
        ];
    }

    /**
     * Sort and remove invalid duplicate day values.
     *
     * @param array<int, string> $days
     * @return array<int, string>
     */
    public static function normalizeDays(array $days): array
    {
        $selected = array_flip(array_unique($days));

        return collect(self::availableDayKeys())
            ->filter(fn (string $day): bool => array_key_exists($day, $selected))
            ->values()
            ->all();
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_INACTIVE => 'Nonaktif',
            default => ucfirst($this->status),
        };
    }

    public function activeDayLabels(): string
    {
        $labels = self::dayLabels();

        return collect($this->active_days ?? [])
            ->map(fn (string $day): ?string => $labels[$day] ?? null)
            ->filter()
            ->implode(', ');
    }

    public function timeRangeLabel(): string
    {
        $startTime = $this->formatTime($this->start_time);
        $endTime = $this->formatTime($this->end_time);

        return $endTime ? "{$startTime} - {$endTime}" : $startTime;
    }

    public function timeInputValue(?string $value): ?string
    {
        return $this->formatTime($value);
    }

    protected function formatTime(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return substr($value, 0, 5);
    }
}
