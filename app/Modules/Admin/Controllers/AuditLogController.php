<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $filters = $this->filtersFromRequest($request);
        $baseQuery = AuditLog::query()->visibleTo($currentUser);
        $filteredQuery = $this->applyFilters(clone $baseQuery, $filters);

        return view('admin.audit-logs', [
            'users' => User::query()
                ->visibleTo($currentUser)
                ->orderBy('name')
                ->get(['id', 'name', 'username']),
            'methodOptions' => (clone $baseQuery)
                ->select('method')
                ->distinct()
                ->orderBy('method')
                ->pluck('method'),
            'filters' => $filters,
            'summary' => [
                'total' => (clone $baseQuery)->count(),
                'filtered' => (clone $filteredQuery)->count(),
                'today' => (clone $baseQuery)->whereDate('created_at', today())->count(),
            ],
            'logs' => $filteredQuery
                ->with('user')
                ->latest('created_at')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    protected function applyFilters($query, array $filters)
    {
        return $query
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $search = $filters['search'];
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('url', 'like', "%{$search}%")
                        ->orWhere('request_data', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%");
                });
            })
            ->when($filters['method'] !== '', fn ($query) => $query->where('method', $filters['method']))
            ->when($filters['user_id'], fn ($query) => $query->where('user_id', $filters['user_id']))
            ->when($filters['date_from'] !== '', fn ($query) => $query->whereDate('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'] !== '', fn ($query) => $query->whereDate('created_at', '<=', $filters['date_to']));
    }

    protected function filtersFromRequest(Request $request): array
    {
        return [
            'search' => trim((string) $request->string('search')),
            'method' => trim((string) $request->string('method')),
            'user_id' => $request->integer('user_id') ?: null,
            'date_from' => trim((string) $request->string('date_from')),
            'date_to' => trim((string) $request->string('date_to')),
        ];
    }
}
