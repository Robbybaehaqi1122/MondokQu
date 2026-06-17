<?php

namespace App\Modules\KegiatanQu\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kegiatan extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'nama',
        'deskripsi',
        'pembina_id',
        'jadwal',
        'tempat',
        'kuota',
        'status',
        'cover',
    ];

    protected function casts(): array
    {
        return [
            'jadwal' => 'array',
            'kuota' => 'integer',
        ];
    }

    public function pembina(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembina_id');
    }

    public function pendaftarans(): HasMany
    {
        return $this->hasMany(KegiatanPendaftaran::class, 'kegiatan_id');
    }

    public function pertemuans(): HasMany
    {
        return $this->hasMany(KegiatanPertemuan::class, 'kegiatan_id');
    }

    public function nilais(): HasMany
    {
        return $this->hasMany(KegiatanNilai::class, 'kegiatan_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}
