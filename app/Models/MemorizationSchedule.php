<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemorizationSchedule extends Model
{
    use BelongsToTenant;

    public const DAY_MONDAY = 'monday';

    public const DAY_TUESDAY = 'tuesday';

    public const DAY_WEDNESDAY = 'wednesday';

    public const DAY_THURSDAY = 'thursday';

    public const DAY_FRIDAY = 'friday';

    public const DAY_SATURDAY = 'saturday';

    public const DAY_SUNDAY = 'sunday';

    protected $fillable = [
        'tenant_id',
        'musyrif_id',
        'day_of_week',
        'start_time',
        'end_time',
        'max_santri',
        'room_id',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'max_santri' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public static function daysOfWeek(): array
    {
        return [
            self::DAY_MONDAY,
            self::DAY_TUESDAY,
            self::DAY_WEDNESDAY,
            self::DAY_THURSDAY,
            self::DAY_FRIDAY,
            self::DAY_SATURDAY,
            self::DAY_SUNDAY,
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function musyrif(): BelongsTo
    {
        return $this->belongsTo(User::class, 'musyrif_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function dayLabel(): string
    {
        return match ($this->day_of_week) {
            self::DAY_MONDAY => 'Senin',
            self::DAY_TUESDAY => 'Selasa',
            self::DAY_WEDNESDAY => 'Rabu',
            self::DAY_THURSDAY => 'Kamis',
            self::DAY_FRIDAY => 'Jumat',
            self::DAY_SATURDAY => 'Sabtu',
            self::DAY_SUNDAY => 'Minggu',
            default => ucfirst($this->day_of_week),
        };
    }

    public function timeRangeLabel(): string
    {
        $start = Carbon::parse($this->start_time)->format('H:i');
        $end = Carbon::parse($this->end_time)->format('H:i');

        return "{$start} - {$end} WIB";
    }

    public function scopeForDay(Builder $query, string $day): Builder
    {
        return $query->where('day_of_week', $day);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
