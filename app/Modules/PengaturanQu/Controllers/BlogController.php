<?php

namespace App\Modules\PengaturanQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PengaturanQu\Models\Blog;
use App\Modules\PengaturanQu\Models\BlogCategory;
use App\Modules\PengaturanQu\Requests\StoreBlogRequest;
use App\Modules\PengaturanQu\Requests\UpdateBlogRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $search = trim((string) $request->string('q'));

        $blogs = Blog::query()
            ->visibleTo($currentUser)
            ->with('author', 'categories')
            ->when($search !== '', fn ($q) => $q->where('title', 'like', "%{$search}%"))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('modules.pengaturan-qu.blog.index', [
            'blogs' => $blogs,
            'search' => $search,
        ]);
    }

    public function create(Request $request): View
    {
        $currentUser = $request->user();
        $categories = BlogCategory::query()->visibleTo($currentUser)->orderBy('name')->get();

        return view('modules.pengaturan-qu.blog.create', compact('categories'));
    }

    public function store(StoreBlogRequest $request): RedirectResponse
    {
        $currentUser = $request->user();
        $tenantId = $currentUser->effectiveTenantId();

        if (! $tenantId) {
            return redirect()->route('pengaturan.blog.index')
                ->with('error', 'Akun Anda belum terhubung ke tenant pondok.');
        }

        $data = $request->validated();
        $data['tenant_id'] = $tenantId;
        $data['author_id'] = $currentUser->id;
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')
                ->store('blog-images/'.$tenantId, 'public');
        }

        $categories = $data['categories'] ?? [];
        unset($data['categories']);

        $blog = Blog::query()->create($data);
        $blog->categories()->sync($categories);

        return redirect()->route('pengaturan.blog.index')
            ->with('success', 'Blog berhasil dibuat.');
    }

    public function edit(Request $request, Blog $blog): View
    {
        $currentUser = $request->user();
        abort_unless((int) $blog->tenant_id === (int) $currentUser->effectiveTenantId(), 403);

        $categories = BlogCategory::query()->visibleTo($currentUser)->orderBy('name')->get();

        return view('modules.pengaturan-qu.blog.edit', compact('blog', 'categories'));
    }

    public function update(UpdateBlogRequest $request, Blog $blog): RedirectResponse
    {
        $currentUser = $request->user();
        abort_unless((int) $blog->tenant_id === (int) $currentUser->effectiveTenantId(), 403);

        $data = $request->validated();
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);

        if ($request->hasFile('featured_image')) {
            if ($blog->featured_image) {
                Storage::disk('public')->delete($blog->featured_image);
            }
            $data['featured_image'] = $request->file('featured_image')
                ->store('blog-images/'.$blog->tenant_id, 'public');
        }

        $categories = $data['categories'] ?? [];
        unset($data['categories']);

        $blog->update($data);
        $blog->categories()->sync($categories);

        return redirect()->route('pengaturan.blog.index')
            ->with('success', 'Blog berhasil diperbarui.');
    }

    public function destroy(Request $request, Blog $blog): RedirectResponse
    {
        $currentUser = $request->user();
        abort_unless((int) $blog->tenant_id === (int) $currentUser->effectiveTenantId(), 403);

        if ($blog->featured_image) {
            Storage::disk('public')->delete($blog->featured_image);
        }

        $blog->delete();

        return redirect()->route('pengaturan.blog.index')
            ->with('success', 'Blog berhasil dihapus.');
    }
}
