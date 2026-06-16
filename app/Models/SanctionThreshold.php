<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SanctionThreshold extends Model
{
    use BelongsToTenant;

    public const TYPE_WARNING = 'warning';

    public const TYPE_PARENT_CALL = 'parent_call';

    public const TYPE_GUIDANCE = 'guidance';

    public const TYPE_SUSPENSION = 'suspension';

    public const TYPE_DISMISSAL = 'dismissal';

    protected $fillable = [
        'tenant_id',
        'name',
        'sanction_type',
        'min_points',
        'max_points',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'min_points' => 'integer',
            'max_points' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function sanctionTypes(): array
    {
        return [
            self::TYPE_WARNING => 'Peringatan',
            self::TYPE_PARENT_CALL => 'Panggilan Orang Tua',
            self::TYPE_GUIDANCE => 'Pembinaan',
            self::TYPE_SUSPENSION => 'Skorsing',
            self::TYPE_DISMISSAL => 'Dikeluarkan',
        ];
    }

    public function typeLabel(): string
    {
        return self::sanctionTypes()[$this->sanction_type] ?? ucfirst($this->sanction_type);
    }

    public static function defaultThresholds(): array
    {
        return [
            ['name' => 'Peringatan Lisan', 'sanction_type' => self::TYPE_WARNING, 'min_points' => 10, 'max_points' => 24, 'description' => 'Santri mendapat peringatan lisan dari musyrif.'],
            ['name' => 'Peringatan Tertulis', 'sanction_type' => self::TYPE_WARNING, 'min_points' => 25, 'max_points' => 49, 'description' => 'Santri mendapat surat peringatan tertulis.'],
            ['name' => 'Panggilan Orang Tua', 'sanction_type' => self::TYPE_PARENT_CALL, 'min_points' => 50, 'max_points' => 74, 'description' => 'Orang tua/wali santri dipanggil ke pondok.'],
            ['name' => 'Pembinaan Intensif', 'sanction_type' => self::TYPE_GUIDANCE, 'min_points' => 75, 'max_points' => 99, 'description' => 'Santri mengikuti program pembinaan khusus.'],
            ['name' => 'Skorsing', 'sanction_type' => self::TYPE_SUSPENSION, 'min_points' => 100, 'max_points' => 149, 'description' => 'Santri diskors untuk jangka waktu tertentu.'],
            ['name' => 'Dikeluarkan', 'sanction_type' => self::TYPE_DISMISSAL, 'min_points' => 150, 'max_points' => null, 'description' => 'Santri dikeluarkan dari pondok.'],
        ];
    }
}
