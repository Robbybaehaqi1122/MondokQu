<?php

namespace App\Modules\PpdbQu\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpdbSeleksi extends Model
{
    use BelongsToTenant;

    protected $table = 'ppdb_seleksis';

    protected $fillable = [
        'tenant_id',
        'pendaftaran_id',
        'jenis',
        'nilai',
        'keterangan',
        'hasil',
        'tanggal_seleksi',
        'diuji_oleh',
    ];

    protected function casts(): array
    {
        return [
            'nilai' => 'integer',
            'tanggal_seleksi' => 'date',
        ];
    }

    const JENIS_ADMINISTRASI = 'administrasi';
    const JENIS_TES_QURAN = 'tes_baca_quran';
    const JENIS_WAWANCARA = 'wawancara';

    const HASIL_MENUNGGU = 'menunggu';
    const HASIL_LULUS = 'lulus';
    const HASIL_TIDAK_LULUS = 'tidak_lulus';

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(PpdbPendaftaran::class, 'pendaftaran_id');
    }

    public function penguji(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diuji_oleh');
    }
}
