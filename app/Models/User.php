<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmailContract
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, MustVerifyEmail, Notifiable, HasRoles;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'username',
        'email',
        'phone_number',
        'status',
        'created_by',
        'password_change_required',
        'avatar_path',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password_change_required' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user that created this account.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(self::class, 'created_by');
    }

    /**
     * Get the tenant that owns this user.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the tenant records created by this user.
     */
    public function ownedTenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'owner_id');
    }

    /**
     * Get the available user statuses.
     *
     * @return array<int, string>
     */
    public static function availableStatuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
            self::STATUS_SUSPENDED,
        ];
    }

    /**
     * Determine whether the user is allowed to sign in.
     */
    public function canAuthenticate(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Determine whether the user is the internal superadmin account.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Superadmin');
    }

    /**
     * Determine whether the user belongs to the selected tenant.
     */
    public function belongsToTenant(?Tenant $tenant): bool
    {
        if (! $tenant || ! $this->tenant_id) {
            return false;
        }

        return $this->tenant_id === $tenant->id;
    }

    /**
     * Resolve the avatar URL for presentation.
     */
    public function avatarUrl(): ?string
    {
        if (! $this->avatar_path) {
            return null;
        }

        if (filter_var($this->avatar_path, FILTER_VALIDATE_URL)) {
            return $this->avatar_path;
        }

        return str_starts_with($this->avatar_path, '/')
            ? $this->avatar_path
            : asset('storage/'.$this->avatar_path);
    }
}
