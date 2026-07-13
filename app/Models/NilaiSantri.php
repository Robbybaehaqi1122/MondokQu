<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaiSantri extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'santri_id',
        'mata_pelajaran_id',
        'semester',
        'nilai_pengetahuan',
        'nilai_keterampilan',
        'notes',
        'input_by',
    ];

    protected function casts(): array
    {
        return [
            'nilai_pengetahuan' => 'integer',
            'nilai_keterampilan' => 'integer',
        ];
    }

    protected $appends = ['nilai_akhir', 'predikat'];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function inputBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'input_by');
    }

    public function getNilaiAkhirAttribute(): int
    {
        return (int) round(($this->nilai_pengetahuan + $this->nilai_keterampilan) / 2);
    }

    public function getPredikatAttribute(): string
    {
        $na = $this->nilai_akhir;
        if ($na >= 86) {
            return 'A';
        }
        if ($na >= 70) {
            return 'B';
        }
        if ($na >= 55) {
            return 'C';
        }

        return 'D';
    }

    public function getTuntasAttribute(): bool
    {
        $kkm = $this->mataPelajaran?->kkm ?? 70;

        if ($this->santri?->room?->gradeLevel) {
            $pivotKkm = $this->mataPelajaran?->gradeLevels()
                ->where('grade_level_id', $this->santri->room->grade_level_id)
                ->first()?->pivot?->kkm;

            if ($pivotKkm !== null) {
                $kkm = $pivotKkm;
            }
        }

        return $this->nilai_akhir >= $kkm;
    }

    public function scopeForSemester($query, string $semester)
    {
        return $query->where('semester', $semester);
    }
}
