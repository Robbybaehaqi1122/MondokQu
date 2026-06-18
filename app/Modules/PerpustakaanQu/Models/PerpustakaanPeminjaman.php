<?php

namespace App\Modules\PerpustakaanQu\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Santri;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerpustakaanPeminjaman extends Model
{
    use BelongsToTenant;

    protected $table = 'perpustakaan_peminjamans';

    protected $fillable = [
        'tenant_id',
        'kitab_id',
        'santri_id',
        'tanggal_pinjam',
        'tanggal_jatuh_tempo',
        'tanggal_kembali',
        'denda',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pinjam' => 'date',
            'tanggal_jatuh_tempo' => 'date',
            'tanggal_kembali' => 'date',
            'denda' => 'integer',
        ];
    }

    const STATUS_DIPINJAM = 'dipinjam';
    const STATUS_DIKEMBALIKAN = 'dikembalikan';
    const STATUS_TERLAMBAT = 'terlambat';

    public function kitab(): BelongsTo
    {
        return $this->belongsTo(PerpustakaanKitab::class, 'kitab_id');
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function scopeStatus($query, $status)
    {
        if ($status) {
            $query->where('status', $status);
        }
    }

    public function scopeSantri($query, $santriId)
    {
        if ($santriId) {
            $query->where('santri_id', $santriId);
        }
    }
}
