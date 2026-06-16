<?php

namespace App\Modules\Saas\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaasResourceReportController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $search = trim((string) $request->string('search'));
        $statusFilter = trim((string) $request->string('status'));

        $tenants = Tenant::query()
            ->with('owner')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($statusFilter !== '', fn ($query) => $query->where('subscription_status', $statusFilter))
            ->withCount('users', 'santris')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $summary = [
            'total_tenants' => Tenant::query()->count(),
            'near_limit' => 0,
        ];

        foreach ($tenants as $tenant) {
            $nearLimit = false;
            foreach (['users', 'santri', 'storage'] as $resource) {
                if ($tenant->getUsagePercentage($resource) >= 80) {
                    $nearLimit = true;
                    break;
                }
            }
            if ($nearLimit) {
                $summary['near_limit']++;
            }
        }

        return view('modules.saas.resource-report', [
            'tenants' => $tenants,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'summary' => $summary,
        ]);
    }
}
