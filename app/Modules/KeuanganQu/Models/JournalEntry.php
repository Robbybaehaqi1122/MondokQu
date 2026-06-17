<?php

namespace App\Modules\KeuanganQu\Models;

use App\Models\User;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    use BelongsToTenant;

    protected $table = 'journal_entries';

    protected $fillable = [
        'tenant_id', 'journal_number', 'description', 'entry_date',
        'period_month', 'period_year', 'status',
        'created_by', 'approved_by', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function details(): HasMany
    {
        return $this->hasMany(JournalEntryDetail::class, 'journal_entry_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public static function scopePeriod($query, int $year, int $month)
    {
        return $query->where('period_year', $year)->where('period_month', $month);
    }

    public static function scopeOrderByLatest($query)
    {
        return $query->orderByDesc('entry_date')->orderByDesc('id');
    }

    public static function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('journal_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    public static function generateJournalNumber(int $tenantId, int $year, int $month): string
    {
        $prefix = 'JNL-' . $tenantId . '-' . $year . str_pad($month, 2, '0', STR_PAD_LEFT) . '-';
        $last = self::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('journal_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('journal_number');

        $nextNumber = $last ? (int) substr($last, -4) + 1 : 1;
        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function isPosted(): bool
    {
        return $this->status === 'posted';
    }

    public function isBalanced(): bool
    {
        $totalDebit = $this->details()->sum('debit');
        $totalKredit = $this->details()->sum('kredit');
        return $totalDebit === $totalKredit;
    }

    public function approve(User $user): void
    {
        $this->update([
            'status' => 'posted',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);
    }
}
