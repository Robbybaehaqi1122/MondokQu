<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Santri extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'nis',
        'name',
        'gender',
        'birth_place',
        'birth_date',
        'address',
        'phone_number',
        'photo_path',
        'status', // e.g., aktif, lulus, keluar
        'guardian_user_ids',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'guardian_user_ids' => 'array',
    ];

    /**
     * Relasi ke User (Wali Santri).
     * Satu santri bisa memiliki beberapa wali (ayah/ibu),
     * dan satu user wali bisa memantau beberapa santri.
     */
    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'santri_guardian', 'santri_id', 'user_id')
            ->withTimestamps();
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }
}
