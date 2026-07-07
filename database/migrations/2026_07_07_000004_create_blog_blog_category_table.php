<?php

use App\Modules\PengaturanQu\Models\Blog;
use App\Modules\PengaturanQu\Models\BlogCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_blog_category', function (Blueprint $table) {
            $table->foreignId('blog_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blog_category_id')->constrained()->cascadeOnDelete();
            $table->primary(['blog_id', 'blog_category_id']);
        });

        Blog::query()->whereNotNull('category_id')->each(function (Blog $blog) {
            $blog->categories()->attach($blog->category_id);
        });

        Schema::table('blogs', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_blog_category');

        Schema::table('blogs', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('slug')->constrained('blog_categories')->nullOnDelete();
        });
    }
};
