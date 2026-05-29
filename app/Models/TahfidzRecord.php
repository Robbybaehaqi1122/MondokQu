<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TahfidzRecord extends Model
{
    use BelongsToTenant;

    public const EVALUATION_LANCAR = 'lancar';

    public const EVALUATION_PERLU_PENGULANGAN = 'perlu_pengulangan';

    public const EVALUATION_BELUM_LANCAR = 'belum_lancar';

    protected $fillable = [
        'tenant_id',
        'tahfidz_session_id',
        'surah_id',
        'verse_start',
        'verse_end',
        'evaluation',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'verse_start' => 'integer',
            'verse_end' => 'integer',
        ];
    }

    public static function availableEvaluations(): array
    {
        return [
            self::EVALUATION_LANCAR,
            self::EVALUATION_PERLU_PENGULANGAN,
            self::EVALUATION_BELUM_LANCAR,
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(TahfidzSession::class, 'tahfidz_session_id');
    }

    public function surah(): BelongsTo
    {
        return $this->belongsTo(TahfidzSurah::class, 'surah_id');
    }

    public function evaluationLabel(): string
    {
        return match ($this->evaluation) {
            self::EVALUATION_LANCAR => 'Lancar',
            self::EVALUATION_PERLU_PENGULANGAN => 'Perlu Pengulangan',
            self::EVALUATION_BELUM_LANCAR => 'Belum Lancar',
            default => ucfirst($this->evaluation),
        };
    }

    public function verseRangeLabel(): string
    {
        if ($this->verse_start === $this->verse_end) {
            return "Ayat {$this->verse_start}";
        }

        return "Ayat {$this->verse_start}-{$this->verse_end}";
    }
}
