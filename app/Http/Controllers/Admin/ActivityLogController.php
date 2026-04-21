<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Display the activity log page.
     */
    public function index(): View
    {
        $currentUser = request()->user();
        $tenantId = $currentUser && ! $currentUser->isSuperAdmin() ? $currentUser->tenant_id : null;

        return view('admin.activity-logs', [
            'logs' => ActivityLog::query()
                ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                ->with('actor')
                ->latest()
                ->paginate(20),
        ]);
    }

    /**
     * Delete all activity log records.
     */
    public function destroyAll(): RedirectResponse
    {
        $currentUser = request()->user();

        ActivityLog::query()
            ->when(
                $currentUser && ! $currentUser->isSuperAdmin() && $currentUser->tenant_id,
                fn ($query) => $query->where('tenant_id', $currentUser->tenant_id)
            )
            ->delete();

        return redirect()
            ->route('admin.activity-logs')
            ->with('success', 'Semua log activity berhasil dihapus.');
    }
}
