<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataPelajaran extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'nama',
        'deskripsi',
        'kkm',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'kkm' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function nilaiSantris(): HasMany
    {
        return $this->hasMany(NilaiSantri::class, 'mata_pelajaran_id');
    }

    public function gradeLevels()
    {
        return $this->belongsToMany(GradeLevel::class, 'subject_grade_level')
            ->withPivot(['kkm', 'is_active', 'order'])
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForGradeLevel($query, GradeLevel|int $gradeLevel)
    {
        $gradeLevelId = $gradeLevel instanceof GradeLevel ? $gradeLevel->id : $gradeLevel;

        return $query->whereHas('gradeLevels', function ($q) use ($gradeLevelId) {
            $q->where('grade_level_id', $gradeLevelId)
                ->where('grade_levels.is_active', true);
        });
    }
}
