<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    public const SUBSCRIPTION_TRIAL = 'trial';

    public const SUBSCRIPTION_ACTIVE = 'active';

    public const SUBSCRIPTION_GRACE = 'grace';

    public const SUBSCRIPTION_EXPIRED = 'expired';

    public const SUBSCRIPTION_DELETING = 'deleting';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'contact_email',
        'contact_phone_number',
        'subscription_plan',
        'subscription_status',
        'trial_ends_at',
        'subscription_starts_at',
        'subscription_ends_at',
        'grace_ends_at',
        'owner_id',
    ];

    /**
     * Get the casts for the model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'subscription_starts_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
            'grace_ends_at' => 'datetime',
        ];
    }

    /**
     * Get the owner of the tenant.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the users that belong to the tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the santri that belong to the tenant.
     */
    public function santris(): HasMany
    {
        return $this->hasMany(Santri::class);
    }

    /**
     * Get rooms that belong to the tenant.
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Get the activity logs that belong to the tenant.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Get the subscription history entries that belong to the tenant.
     */
    public function subscriptionHistories(): HasMany
    {
        return $this->hasMany(TenantSubscriptionHistory::class);
    }

    /**
     * Get the billing notes that belong to the tenant.
     */
    public function billingNotes(): HasMany
    {
        return $this->hasMany(TenantBillingNote::class);
    }

    /**
     * Get the santri invoices that belong to the tenant.
     */
    public function santriInvoices(): HasMany
    {
        return $this->hasMany(SantriInvoice::class);
    }

    /**
     * Get the santri payments that belong to the tenant.
     */
    public function santriPayments(): HasMany
    {
        return $this->hasMany(SantriPayment::class);
    }

    /**
     * Get the payment confirmations that belong to the tenant.
     */
    public function santriPaymentConfirmations(): HasMany
    {
        return $this->hasMany(SantriPaymentConfirmation::class);
    }

    /**
     * Get the attendance activities that belong to the tenant.
     */
    public function attendanceActivities(): HasMany
    {
        return $this->hasMany(AttendanceActivity::class);
    }

    /**
     * Get the attendance sessions that belong to the tenant.
     */
    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class);
    }

    /**
     * Get the attendance records that belong to the tenant.
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Get the leave requests that belong to the tenant.
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Get the data exports that belong to the tenant.
     */
    public function dataExports(): HasMany
    {
        return $this->hasMany(DataExport::class);
    }

    /**
     * Get the room transfers that belong to the tenant.
     */
    public function roomTransfers(): HasMany
    {
        return $this->hasMany(RoomTransfer::class);
    }

    /**
     * Get the guardian link records that belong to the tenant.
     */
    public function santriGuardians(): HasMany
    {
        return $this->hasMany(SantriGuardian::class);
    }

    /**
     * Determine whether the tenant is still in the trial period.
     */
    public function onTrial(): bool
    {
        return $this->subscription_status === self::SUBSCRIPTION_TRIAL
            && $this->trial_ends_at?->isFuture();
    }

    /**
     * Determine whether the tenant subscription is active.
     */
    public function hasPaidSubscription(): bool
    {
        return $this->subscription_status === self::SUBSCRIPTION_ACTIVE
            && $this->subscription_ends_at?->isFuture();
    }

    /**
     * Determine whether the tenant is in grace period after subscription expiry.
     */
    public function onGracePeriod(): bool
    {
        return $this->subscription_status === self::SUBSCRIPTION_GRACE
            && $this->grace_ends_at?->isFuture();
    }

    /**
     * Determine whether the tenant can still access the app.
     */
    public function hasAccess(): bool
    {
        return $this->onTrial() || $this->hasPaidSubscription() || $this->onGracePeriod();
    }

    /**
     * Determine whether the tenant is queued for permanent deletion.
     */
    public function isDeleting(): bool
    {
        return $this->subscription_status === self::SUBSCRIPTION_DELETING;
    }

    /**
     * Determine whether the tenant already requires payment.
     */
    public function requiresPayment(): bool
    {
        return ! $this->hasAccess();
    }

    /**
     * Get tenant subscription lifecycle statuses.
     *
     * @return array<int, string>
     */
    public static function subscriptionStatuses(): array
    {
        return [
            self::SUBSCRIPTION_TRIAL,
            self::SUBSCRIPTION_ACTIVE,
            self::SUBSCRIPTION_GRACE,
            self::SUBSCRIPTION_EXPIRED,
            self::SUBSCRIPTION_DELETING,
        ];
    }
}
