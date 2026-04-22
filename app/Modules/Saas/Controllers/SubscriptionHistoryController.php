<?php

namespace App\Modules\Saas\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantSubscriptionHistory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionHistoryController extends Controller
{
    /**
     * Display the subscription history list for SaaS operators.
     */
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $search = trim((string) $request->string('search'));
        $action = $request->string('action')->toString();
        $tenantId = $request->integer('tenant_id');

        return view('modules.saas.subscription-histories.index', [
            'histories' => TenantSubscriptionHistory::query()
                ->with(['tenant', 'changedByUser'])
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($historyQuery) use ($search) {
                        $historyQuery
                            ->where('admin_note', 'like', "%{$search}%")
                            ->orWhereHas('tenant', function ($tenantQuery) use ($search) {
                                $tenantQuery
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('slug', 'like', "%{$search}%");
                            })
                            ->orWhereHas('changedByUser', function ($userQuery) use ($search) {
                                $userQuery
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('username', 'like', "%{$search}%");
                            });
                    });
                })
                ->when(in_array($action, ['activate_trial', 'extend_trial', 'activate_subscription', 'mark_grace', 'mark_expired'], true), fn ($query) => $query->where('action', $action))
                ->when($tenantId > 0, fn ($query) => $query->where('tenant_id', $tenantId))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'tenants' => Tenant::query()
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
            'filters' => [
                'search' => $search,
                'action' => $action,
                'tenant_id' => $tenantId > 0 ? $tenantId : null,
            ],
        ]);
    }
}
