<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahfidzSurah extends Model
{
    protected $fillable = [
        'number',
        'name',
        'name_arabic',
        'verses_count',
        'juz',
    ];

    public function records(): HasMany
    {
        return $this->hasMany(TahfidzRecord::class, 'surah_id');
    }

    public function scopeWhereJuz(Builder $query, int $juz): Builder
    {
        return $query->whereIn('id', function ($q) use ($juz) {
            $q->select('tahfidz_surah_id')
                ->from('tahfidz_surah_juz')
                ->where('juz', $juz);
        });
    }
}
