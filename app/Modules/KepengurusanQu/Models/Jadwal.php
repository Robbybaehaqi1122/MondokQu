<?php

namespace App\Modules\KepengurusanQu\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Jadwal extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'kegiatan',
        'pengajar_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'tempat',
        'keterangan',
        'created_by',
    ];

    public function pengajar(): BelongsTo
    {
        return $this->belongsTo(Pengajar::class, 'pengajar_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSearch($query, ?string $search)
    {
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kegiatan', 'like', "%{$search}%")
                  ->orWhere('tempat', 'like', "%{$search}%");
            });
        }
    }

    public function scopeHari($query, ?string $hari)
    {
        if ($hari) {
            $query->where('hari', $hari);
        }
    }

    public function scopePengajar($query, ?string $pengajarId)
    {
        if ($pengajarId) {
            $query->where('pengajar_id', $pengajarId);
        }
    }
}
