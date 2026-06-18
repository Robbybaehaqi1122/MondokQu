<?php

namespace App\Modules\PpdbQu\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PpdbPendaftaran extends Model
{
    use BelongsToTenant;

    protected $table = 'ppdb_pendaftarans';

    protected $fillable = [
        'tenant_id',
        'gelombang_id',
        'nomor_pendaftaran',
        'nama_lengkap',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'no_hp',
        'email',
        'asal_sekolah',
        'nama_ayah',
        'nama_ibu',
        'no_hp_orangtua',
        'berkas',
        'status',
        'catatan',
        'diterima_at',
        'daftar_ulang_at',
        'diproses_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'berkas' => 'array',
            'diterima_at' => 'datetime',
            'daftar_ulang_at' => 'datetime',
        ];
    }

    public const STATUS_MENUNGGU = 'menunggu';
    public const STATUS_DIPROSES = 'diproses';
    public const STATUS_DITERIMA = 'diterima';
    public const STATUS_DITOLAK = 'ditolak';
    public const STATUS_DAFTAR_ULANG = 'daftar_ulang';

    public function gelombang(): BelongsTo
    {
        return $this->belongsTo(PpdbGelombang::class, 'gelombang_id');
    }

    public function seleksis(): HasMany
    {
        return $this->hasMany(PpdbSeleksi::class, 'pendaftaran_id');
    }

    public function diprosesOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    public static function generateNomorPendaftaran(int $tenantId, int $gelombangId): string
    {
        $last = static::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->whereYear('created_at', now()->year)
            ->count();

        return sprintf('PPDB-%s-%s-%05d', now()->year, $gelombangId, $last + 1);
    }
}
