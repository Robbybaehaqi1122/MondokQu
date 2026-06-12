<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SantriDocument extends Model
{
    protected $fillable = [
        'santri_id',
        'type',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'notes',
        'uploaded_by',
    ];

    public const TYPE_KK = 'kk';
    public const TYPE_AKTA = 'akta';
    public const TYPE_IjazAH = 'ijazah';
    public const TYPE_KTP_AYAH = 'ktp_ayah';
    public const TYPE_KTP_IBU = 'ktp_ibu';
    public const TYPE_FOTO = 'foto';

    public static function types(): array
    {
        return [
            self::TYPE_KK => 'Kartu Keluarga',
            self::TYPE_AKTA => 'Akta Kelahiran',
            self::TYPE_IjazAH => 'Ijazah',
            self::TYPE_KTP_AYAH => 'KTP Ayah',
            self::TYPE_KTP_IBU => 'KTP Ibu',
            self::TYPE_FOTO => 'Foto Formal',
        ];
    }

    public static function requiredTypes(): array
    {
        return [
            self::TYPE_KK,
            self::TYPE_AKTA,
            self::TYPE_IjazAH,
        ];
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function typeLabel(): string
    {
        return self::types()[$this->type] ?? ucfirst($this->type);
    }

    public function fileUrl(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        if (filter_var($this->file_path, FILTER_VALIDATE_URL)) {
            return $this->file_path;
        }

        if (str_starts_with($this->file_path, '/')) {
            return $this->file_path;
        }

        if (! \Illuminate\Support\Facades\Storage::disk('public')->exists($this->file_path)) {
            return null;
        }

        return asset('storage/' . $this->file_path);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function fileSizeForHumans(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $i < count($units); $i++) {
            if ($bytes >= 1024) {
                $bytes /= 1024;
            } else {
                return round($bytes, 1) . ' ' . $units[$i];
            }
        }

        return round($bytes, 1) . ' ' . end($units);
    }
}
