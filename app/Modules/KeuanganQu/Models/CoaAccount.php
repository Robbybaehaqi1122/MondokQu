<?php

namespace App\Modules\KeuanganQu\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoaAccount extends Model
{
    use BelongsToTenant;

    protected $table = 'coa_accounts';

    protected $fillable = [
        'tenant_id', 'parent_id', 'code', 'name', 'type',
        'normal_balance', 'description', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public const TYPES = [
        'aset' => 'Aset',
        'kewajiban' => 'Kewajiban',
        'modal' => 'Modal',
        'pendapatan' => 'Pendapatan',
        'beban' => 'Beban',
    ];

    public const NORMAL_BALANCE = [
        'aset' => 'debit',
        'kewajiban' => 'kredit',
        'modal' => 'kredit',
        'pendapatan' => 'kredit',
        'beban' => 'debit',
    ];

    public static function getTypes(): array
    {
        return self::TYPES;
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function journalDetails(): HasMany
    {
        return $this->hasMany(JournalEntryDetail::class, 'coa_account_id');
    }

    public static function scopeParentsOnly($query)
    {
        return $query->whereNull('parent_id');
    }

    public static function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getNameWithCodeAttribute(): string
    {
        return "{$this->code} - {$this->name}";
    }
}
