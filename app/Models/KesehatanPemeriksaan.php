<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class KesehatanPemeriksaan extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'santri_id',
        'tanggal_pemeriksaan',
        'keluhan',
        'diagnosis',
        'tindakan',
        'catatan',
        'dicatat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pemeriksaan' => 'date',
        ];
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function rujukan(): HasOne
    {
        return $this->hasOne(KesehatanRujukan::class, 'pemeriksaan_id');
    }

    public function pemakaianObat(): HasMany
    {
        return $this->hasMany(KesehatanPemakaianObat::class, 'pemeriksaan_id');
    }
}
