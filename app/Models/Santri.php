<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Santri extends Model
{
    use HasFactory;

    public const GENDER_MALE = 'male';
    public const GENDER_FEMALE = 'female';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_LEAVE = 'leave';
    public const STATUS_EXITED = 'exited';
    public const STATUS_ALUMNI = 'alumni';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nis',
        'full_name',
        'gender',
        'birth_place',
        'birth_date',
        'address',
        'guardian_name',
        'father_name',
        'mother_name',
        'guardian_phone_number',
        'emergency_contact',
        'entry_date',
        'entry_year',
        'room_name',
        'notes',
        'status',
        'photo_path',
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
            'birth_date' => 'date',
            'entry_date' => 'date',
        ];
    }

    /**
     * Get the user that created this santri record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the available santri statuses.
     *
     * @return array<int, string>
     */
    public static function availableStatuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_LEAVE,
            self::STATUS_EXITED,
            self::STATUS_ALUMNI,
        ];
    }

    /**
     * Get the available santri genders.
     *
     * @return array<int, string>
     */
    public static function availableGenders(): array
    {
        return [
            self::GENDER_MALE,
            self::GENDER_FEMALE,
        ];
    }

    /**
     * Resolve the photo URL for presentation.
     */
    public function photoUrl(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        if (filter_var($this->photo_path, FILTER_VALIDATE_URL)) {
            return $this->photo_path;
        }

        return str_starts_with($this->photo_path, '/')
            ? $this->photo_path
            : asset('storage/'.$this->photo_path);
    }

    /**
     * Resolve a human-friendly gender label.
     */
    public function genderLabel(): string
    {
        return match ($this->gender) {
            self::GENDER_MALE => 'Laki-laki',
            self::GENDER_FEMALE => 'Perempuan',
            default => '-',
        };
    }

    /**
     * Resolve a human-friendly status label.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_LEAVE => 'Cuti',
            self::STATUS_EXITED => 'Keluar',
            self::STATUS_ALUMNI => 'Alumni',
            default => '-',
        };
    }
}
