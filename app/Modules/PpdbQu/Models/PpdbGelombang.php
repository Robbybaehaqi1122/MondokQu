<?php

namespace App\Modules\PpdbQu\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PpdbGelombang extends Model
{
    use BelongsToTenant;

    protected $table = 'ppdb_gelombangs';

    protected $fillable = [
        'tenant_id',
        'nama',
        'tanggal_mulai',
        'tanggal_selesai',
        'kuota',
        'biaya_pendaftaran',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'kuota' => 'integer',
            'biaya_pendaftaran' => 'decimal:0',
        ];
    }

    public function pendaftarans(): HasMany
    {
        return $this->hasMany(PpdbPendaftaran::class, 'gelombang_id');
    }

    public function pengumumans(): HasMany
    {
        return $this->hasMany(PpdbPengumuman::class, 'gelombang_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeSedangBerlangsung($query)
    {
        return $query->where('status', 'aktif')
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_selesai', '>=', now());
    }
}
