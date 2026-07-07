<?php

namespace App\Modules\PengaturanQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PengaturanQu\Models\BlogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $currentUser = $request->user();

        $categories = BlogCategory::query()
            ->visibleTo($currentUser)
            ->withCount('blogs')
            ->orderBy('name')
            ->paginate(15);

        return view('modules.pengaturan-qu.blog-category.index', compact('categories'));
    }

    public function create(): View
    {
        return view('modules.pengaturan-qu.blog-category.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $currentUser = $request->user();
        $tenantId = $currentUser->effectiveTenantId();

        if (! $tenantId) {
            return redirect()->route('pengaturan.blog-category.index')
                ->with('error', 'Akun Anda belum terhubung ke tenant pondok.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $data['tenant_id'] = $tenantId;
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        BlogCategory::query()->create($data);

        return redirect()->route('pengaturan.blog-category.index')
            ->with('success', 'Kategori berhasil dibuat.');
    }

    public function edit(Request $request, BlogCategory $blogCategory): View
    {
        $currentUser = $request->user();
        abort_unless((int) $blogCategory->tenant_id === (int) $currentUser->effectiveTenantId(), 403);

        return view('modules.pengaturan-qu.blog-category.edit', compact('blogCategory'));
    }

    public function update(Request $request, BlogCategory $blogCategory): RedirectResponse
    {
        $currentUser = $request->user();
        abort_unless((int) $blogCategory->tenant_id === (int) $currentUser->effectiveTenantId(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        $blogCategory->update($data);

        return redirect()->route('pengaturan.blog-category.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Request $request, BlogCategory $blogCategory): RedirectResponse
    {
        $currentUser = $request->user();
        abort_unless((int) $blogCategory->tenant_id === (int) $currentUser->effectiveTenantId(), 403);

        if ($blogCategory->blogs()->count() > 0) {
            return redirect()->route('pengaturan.blog-category.index')
                ->with('error', 'Kategori tidak bisa dihapus karena masih memiliki ' . $blogCategory->blogs()->count() . ' blog.');
        }

        $blogCategory->delete();

        return redirect()->route('pengaturan.blog-category.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
