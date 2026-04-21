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
     * Determine whether the tenant already requires payment.
     */
    public function requiresPayment(): bool
    {
        return ! $this->hasAccess();
    }
}
