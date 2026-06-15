<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttitudeGrade extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'santri_id',
        'semester',
        'aspect',
        'aspect_name',
        'predicate',
        'description',
        'created_by',
    ];

    public const ASPECT_SPIRITUAL = 'spiritual';

    public const ASPECT_SOSIAL = 'sosial';

    public const PREDICATE_SB = 'SB';

    public const PREDICATE_B = 'B';

    public const PREDICATE_C = 'C';

    public const PREDICATE_K = 'K';

    public static function aspects(string $aspect): array
    {
        return match ($aspect) {
            self::ASPECT_SPIRITUAL => ['Kejujuran', 'Kedisiplinan Ibadah', 'Akhlak'],
            self::ASPECT_SOSIAL => ['Tanggung Jawab', 'Kerjasama', 'Sopan Santun'],
            default => [],
        };
    }

    public static function allAspects(): array
    {
        return [
            self::ASPECT_SPIRITUAL => self::aspects(self::ASPECT_SPIRITUAL),
            self::ASPECT_SOSIAL => self::aspects(self::ASPECT_SOSIAL),
        ];
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function inputBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function availablePredicates(): array
    {
        return [
            self::PREDICATE_SB => 'Sangat Baik',
            self::PREDICATE_B => 'Baik',
            self::PREDICATE_C => 'Cukup',
            self::PREDICATE_K => 'Kurang',
        ];
    }

    public function predicateLabel(): string
    {
        return self::availablePredicates()[$this->predicate] ?? '-';
    }
}
