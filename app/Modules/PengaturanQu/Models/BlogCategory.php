<?php

namespace App\Modules\PengaturanQu\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BlogCategory extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (BlogCategory $category): void {
            if (! $category->slug) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'category_id');
    }
}
