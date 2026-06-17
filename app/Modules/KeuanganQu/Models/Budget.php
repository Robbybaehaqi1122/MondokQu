<?php

namespace App\Modules\KeuanganQu\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use BelongsToTenant;

    protected $table = 'budgets';

    protected $fillable = [
        'tenant_id', 'coa_account_id', 'period_month', 'period_year',
        'amount', 'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
        ];
    }

    public function coaAccount(): BelongsTo
    {
        return $this->belongsTo(CoaAccount::class, 'coa_account_id');
    }

    public static function scopePeriod($query, int $year, int $month)
    {
        return $query->where('period_year', $year)->where('period_month', $month);
    }

    public function getSpendingAttribute(): int
    {
        $entryIds = JournalEntry::withoutTenantScope()
            ->where('tenant_id', $this->tenant_id)
            ->where('status', 'posted')
            ->where('period_year', $this->period_year)
            ->where('period_month', $this->period_month)
            ->pluck('id');

        $normalBalance = $this->coaAccount->normal_balance ?? 'debit';
        $column = $normalBalance === 'debit' ? 'debit' : 'kredit';

        return (int) JournalEntryDetail::whereIn('journal_entry_id', $entryIds)
            ->where('coa_account_id', $this->coa_account_id)
            ->sum($column);
    }

    public function getPercentageAttribute(): float
    {
        if ($this->amount <= 0) {
            return 0;
        }
        return round(($this->spending / $this->amount) * 100, 1);
    }
}
