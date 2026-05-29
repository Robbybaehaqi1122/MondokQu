<?php

namespace App\Models;

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
}
