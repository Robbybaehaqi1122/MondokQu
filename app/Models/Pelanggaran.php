<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pelanggaran extends Model
{
    use BelongsToTenant;

    protected $table = 'pelanggarans';

    protected $fillable = [
        'tenant_id',
        'santri_id',
        'kategori_id',
        'keterangan',
        'poin',
        'dicatat_oleh',
        'tanggal',
    ];

    protected function casts(): array
    {
        return [
            'poin' => 'integer',
            'tanggal' => 'date',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(PelanggaranKategori::class, 'kategori_id');
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
