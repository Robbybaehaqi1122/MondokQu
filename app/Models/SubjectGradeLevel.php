<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectGradeLevel extends Model
{
    use BelongsToTenant;

    protected $table = 'subject_grade_level';

    protected $fillable = [
        'tenant_id',
        'grade_level_id',
        'mata_pelajaran_id',
        'kkm',
        'is_active',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'kkm' => 'integer',
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class);
    }
}
