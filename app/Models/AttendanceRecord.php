<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\AttendanceRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    /** @use HasFactory<AttendanceRecordFactory> */
    use BelongsToTenant, HasFactory;

    public const STATUS_PRESENT = 'present';

    public const STATUS_PERMISSION = 'permission';

    public const STATUS_SICK = 'sick';

    public const STATUS_ABSENT = 'absent';

    public const STATUS_LATE = 'late';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'attendance_session_id',
        'santri_id',
        'status',
        'notes',
        'recorded_by',
        'recorded_at',
    ];

    /**
     * Get the casts for the model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
        ];
    }

    /**
     * Get available record statuses.
     *
     * @return array<int, string>
     */
    public static function availableStatuses(): array
    {
        return [
            self::STATUS_PRESENT,
            self::STATUS_PERMISSION,
            self::STATUS_SICK,
            self::STATUS_ABSENT,
            self::STATUS_LATE,
        ];
    }

    /**
     * Get status options with labels.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function statusOptions(): array
    {
        return [
            ['value' => self::STATUS_PRESENT, 'label' => 'Hadir'],
            ['value' => self::STATUS_PERMISSION, 'label' => 'Izin'],
            ['value' => self::STATUS_SICK, 'label' => 'Sakit'],
            ['value' => self::STATUS_ABSENT, 'label' => 'Alpa'],
            ['value' => self::STATUS_LATE, 'label' => 'Terlambat'],
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function statusLabel(): string
    {
        $statusOption = collect(self::statusOptions())
            ->firstWhere('value', $this->status);

        return $statusOption['label'] ?? ucfirst($this->status);
    }
}
