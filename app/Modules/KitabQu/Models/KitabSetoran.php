<?php

namespace App\Modules\KitabQu\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KitabSetoran extends Model
{
    use BelongsToTenant;

    protected $table = 'kitab_setorans';

    protected $fillable = [
        'tenant_id',
        'santri_id',
        'kitab_id',
        'tanggal_setoran',
        'materi',
        'status',
        'catatan',
        'approved_by',
        'approved_at',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_DISETUJUI = 'disetujui';
    const STATUS_DITOLAK = 'ditolak';

    protected function casts(): array
    {
        return [
            'tanggal_setoran' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function kitab(): BelongsTo
    {
        return $this->belongsTo(Kitab::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeSantri($query, ?string $santriId)
    {
        if ($santriId) {
            $query->where('santri_id', $santriId);
        }
    }

    public function scopeKitab($query, ?string $kitabId)
    {
        if ($kitabId) {
            $query->where('kitab_id', $kitabId);
        }
    }

    public function scopeStatus($query, ?string $status)
    {
        if ($status) {
            $query->where('status', $status);
        }
    }
}
