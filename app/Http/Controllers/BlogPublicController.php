<?php

namespace App\Http\Controllers;

use App\Modules\PengaturanQu\Models\Blog;
use Illuminate\View\View;

class BlogPublicController extends Controller
{
    public function index(): View
    {
        $blogs = Blog::query()
            ->withoutTenantScope()
            ->published()
            ->with(['author', 'tenant'])
            ->orderByDesc('published_at')
            ->paginate(9);

        return view('blog.index', compact('blogs'));
    }

    public function show(string $slug): View
    {
        $blog = Blog::query()
            ->withoutTenantScope()
            ->published()
            ->where('slug', $slug)
            ->with(['author'])
            ->firstOrFail();

        $recentBlogs = Blog::query()
            ->withoutTenantScope()
            ->published()
            ->where('id', '!=', $blog->id)
            ->where('tenant_id', $blog->tenant_id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('blog.show', compact('blog', 'recentBlogs'));
    }
}
