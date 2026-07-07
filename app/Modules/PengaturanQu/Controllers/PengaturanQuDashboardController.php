<?php

namespace App\Modules\PengaturanQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PengaturanQu\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengaturanQuDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $tenant = $currentUser->tenant;

        $blogCount = Blog::query()->visibleTo($currentUser)->count();
        $publishedCount = Blog::query()->visibleTo($currentUser)->published()->count();
        $latestBlogs = Blog::query()->visibleTo($currentUser)->with('author')->latest()->limit(5)->get();

        $settings = $tenant?->settings ?? [];

        return view('modules.pengaturan-qu.dashboard', compact(
            'blogCount', 'publishedCount', 'latestBlogs', 'settings'
        ));
    }
}
