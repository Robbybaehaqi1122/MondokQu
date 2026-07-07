<?php

namespace App\Modules\PengaturanQu\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Blog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'author_id',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Blog $blog): void {
            if (! $blog->slug) {
                $blog->slug = Str::slug($blog->title);
            }
            if ($blog->is_published && ! $blog->published_at) {
                $blog->published_at = now();
            }
        });

        static::updating(function (Blog $blog): void {
            if ($blog->isDirty('is_published') && $blog->is_published && ! $blog->published_at) {
                $blog->published_at = now();
            }
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(BlogCategory::class, 'blog_blog_category');
    }

    public function scopePublished($query): void
    {
        $query->where('is_published', true)->whereNotNull('published_at');
    }

    public function getExcerptHtml(): string
    {
        return $this->excerpt ?? Str::limit(strip_tags($this->content), 200);
    }

    public function getReadingTime(): string
    {
        $words = str_word_count(strip_tags($this->content));
        $minutes = max(1, ceil($words / 200));
        return "{$minutes} menit baca";
    }
}
