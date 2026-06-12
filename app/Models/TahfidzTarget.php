<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TahfidzTarget extends Model
{
    use BelongsToTenant;

    public const TYPE_JUZ = 'juz';
    public const TYPE_SURAH = 'surah';
    public const TYPE_AYAT = 'ayat';

    protected $fillable = [
        'tenant_id',
        'santri_id',
        'target_type',
        'target_value',
        'target_date',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'target_date' => 'date',
            'target_value' => 'integer',
        ];
    }

    public static function availableTypes(): array
    {
        return [
            ['value' => self::TYPE_JUZ, 'label' => 'Juz'],
            ['value' => self::TYPE_SURAH, 'label' => 'Surah'],
            ['value' => self::TYPE_AYAT, 'label' => 'Ayat'],
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function typeLabel(): string
    {
        return match ($this->target_type) {
            self::TYPE_JUZ => 'Juz',
            self::TYPE_SURAH => 'Surah',
            self::TYPE_AYAT => 'Ayat',
            default => ucfirst($this->target_type),
        };
    }

    public function computeProgress(): int
    {
        $santriId = $this->santri_id;

        $recordsQuery = TahfidzRecord::query()
            ->whereHas('session', fn ($q) => $q->where('santri_id', $santriId))
            ->where('evaluation', TahfidzRecord::EVALUATION_LANCAR);

        return match ($this->target_type) {
            self::TYPE_JUZ => TahfidzSurah::whereIn('id', (clone $recordsQuery)
                ->select('surah_id')
                ->distinct()
            )->distinct('juz')->count('juz'),

            self::TYPE_SURAH => (clone $recordsQuery)
                ->distinct('surah_id')
                ->count('surah_id'),

            self::TYPE_AYAT => (clone $recordsQuery)
                ->get()
                ->sum(fn ($r) => ($r->verse_end - $r->verse_start + 1)),

            default => 0,
        };
    }

    public function progressPercentage(): float
    {
        if ($this->target_value <= 0) {
            return 0;
        }

        $current = $this->computeProgress();

        return round(min(100, ($current / $this->target_value) * 100), 1);
    }

    public function isDeadlineNear(): bool
    {
        if (! $this->target_date) {
            return false;
        }

        return $this->target_date->isFuture() && $this->target_date->diffInDays(now()) <= 30;
    }

    public function isOverdue(): bool
    {
        if (! $this->target_date) {
            return false;
        }

        return $this->target_date->isPast() && $this->progressPercentage() < 100;
    }
}
