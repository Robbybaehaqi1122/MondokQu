<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\SantriInvoiceFactory;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SantriInvoice extends Model
{
    /** @use HasFactory<SantriInvoiceFactory> */
    use BelongsToTenant, HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PARTIAL = 'partial';

    public const STATUS_PAID = 'paid';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'santri_id',
        'invoice_number',
        'title',
        'period_month',
        'period_year',
        'due_date',
        'amount',
        'paid_amount',
        'status',
        'notes',
        'created_by',
    ];

    /**
     * Get the casts for the model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'amount' => 'integer',
            'paid_amount' => 'integer',
            'period_month' => 'integer',
            'period_year' => 'integer',
        ];
    }

    /**
     * Get available invoice statuses.
     *
     * @return array<int, string>
     */
    public static function availableStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_PARTIAL,
            self::STATUS_PAID,
        ];
    }

    /**
     * Get the tenant that owns this invoice.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the santri billed by this invoice.
     */
    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    /**
     * Get the user that created this invoice.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get recorded payments for this invoice.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SantriPayment::class);
    }

    /**
     * Get payment proof confirmations submitted by wali santri.
     */
    public function paymentConfirmations(): HasMany
    {
        return $this->hasMany(SantriPaymentConfirmation::class);
    }

    /**
     * Apply filters used by invoice list and export.
     */
    public function scopeWithFilters(Builder $query, string $search = '', string $status = '', string $santriId = ''): Builder
    {
        $search = trim($search);
        $status = trim($status);
        $santriId = trim($santriId);

        return $query
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $invoiceQuery) use ($search): void {
                    $invoiceQuery
                        ->where('invoice_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhereHas('santri', function (Builder $santriQuery) use ($search): void {
                            $santriQuery
                                ->where('nis', 'like', "%{$search}%")
                                ->orWhere('full_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', function (Builder $query) use ($status): void {
                if ($status === 'overdue') {
                    $query->overdue();

                    return;
                }

                if (in_array($status, self::availableStatuses(), true)) {
                    $query->where('status', $status);
                }
            })
            ->when($santriId !== '', fn (Builder $query) => $query->where('santri_id', $santriId));
    }

    /**
     * Limit the query to invoices that are past due and not fully paid.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query
            ->where('status', '!=', self::STATUS_PAID)
            ->whereDate('due_date', '<', now()->toDateString());
    }

    /**
     * Resolve the remaining amount for this invoice.
     */
    public function outstandingAmount(): int
    {
        return max(0, $this->amount - $this->paid_amount);
    }

    /**
     * Determine whether the invoice is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status !== self::STATUS_PAID
            && $this->due_date?->isPast();
    }

    /**
     * Refresh paid amount and status from recorded payments.
     */
    public function refreshPaymentStatus(): void
    {
        $paidAmount = $this->payments()->sum('amount');
        $invoiceAmount = $this->amount;

        if ($paidAmount > $invoiceAmount) {
            throw new DomainException('Total pembayaran tidak boleh melebihi nominal tagihan.');
        }

        $this->forceFill([
            'paid_amount' => $paidAmount,
            'status' => match (true) {
                $paidAmount >= $invoiceAmount => self::STATUS_PAID,
                $paidAmount > 0 => self::STATUS_PARTIAL,
                default => self::STATUS_PENDING,
            },
        ])->save();
    }

    /**
     * Resolve a human-friendly status label.
     */
    public function statusLabel(): string
    {
        if ($this->isOverdue()) {
            return 'Tunggakan';
        }

        return match ($this->status) {
            self::STATUS_PAID => 'Lunas',
            self::STATUS_PARTIAL => 'Sebagian',
            self::STATUS_PENDING => 'Menunggu Bayar',
            default => ucfirst($this->status),
        };
    }
}
