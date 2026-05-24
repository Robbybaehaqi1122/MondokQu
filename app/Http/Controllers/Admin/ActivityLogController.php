<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityLogController extends Controller
{
    /**
     * Display the activity log page.
     */
    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $filters = $this->filtersFromRequest($request);
        $baseQuery = ActivityLog::query()->visibleTo($currentUser);
        $filteredQuery = $this->applyFilters(clone $baseQuery, $filters);

        return view('admin.activity-logs', [
            'actors' => User::query()
                ->visibleTo($currentUser)
                ->orderBy('name')
                ->get(['id', 'name', 'username']),
            'actionOptions' => (clone $baseQuery)
                ->select('action')
                ->whereNotNull('action')
                ->distinct()
                ->orderBy('action')
                ->pluck('action'),
            'filters' => $filters,
            'logSummary' => [
                'total' => (clone $baseQuery)->count(),
                'filtered' => (clone $filteredQuery)->count(),
                'today' => (clone $baseQuery)->whereDate('created_at', today())->count(),
                'destructive' => (clone $baseQuery)
                    ->where(function ($query): void {
                        $query
                            ->where('action', 'like', '%deleted%')
                            ->orWhere('action', 'like', '%destroy%');
                    })
                    ->count(),
            ],
            'logs' => $filteredQuery
                ->with('actor')
                ->latest()
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    /**
     * Export filtered activity logs as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $currentUser = $request->user();
        $filters = $this->filtersFromRequest($request);
        $filename = 'activity-logs-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($currentUser, $filters): void {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'Waktu',
                'Pelaku',
                'Username Pelaku',
                'Aksi',
                'Target',
                'Tipe Target',
                'Detail',
                'IP Address',
                'User Agent',
                'Properties',
            ]);

            $this->applyFilters(ActivityLog::query()->visibleTo($currentUser), $filters)
                ->with('actor')
                ->latest()
                ->chunk(500, function ($logs) use ($handle): void {
                    foreach ($logs as $log) {
                        fputcsv($handle, [
                            $log->created_at?->format('Y-m-d H:i:s'),
                            $log->actor_name ?? 'System',
                            $log->actor?->username,
                            $log->action,
                            $log->target_name,
                            $log->target_type ? class_basename($log->target_type) : null,
                            $log->description,
                            $log->ip_address,
                            $log->user_agent,
                            $log->properties ? json_encode($log->properties, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Delete all activity log records.
     */
    public function destroyAll(Request $request): RedirectResponse
    {
        $currentUser = $request->user();

        ActivityLog::query()
            ->visibleTo($currentUser)
            ->delete();

        return redirect()
            ->route('admin.activity-logs')
            ->with('success', 'Semua log activity berhasil dihapus.');
    }

    /**
     * Apply user-facing filters to the tenant-scoped activity log query.
     *
     * @param  array{search: string, action: string, actor_id: int|null, date_from: string, date_to: string}  $filters
     */
    protected function applyFilters($query, array $filters)
    {
        return $query
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $search = $filters['search'];

                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('actor_name', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhere('target_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%");
                });
            })
            ->when($filters['action'] !== '', fn ($query) => $query->where('action', $filters['action']))
            ->when($filters['actor_id'], fn ($query) => $query->where('actor_id', $filters['actor_id']))
            ->when($filters['date_from'] !== '', fn ($query) => $query->whereDate('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'] !== '', fn ($query) => $query->whereDate('created_at', '<=', $filters['date_to']));
    }

    /**
     * Resolve filter input shared by the page and CSV export.
     *
     * @return array{search: string, action: string, actor_id: int|null, date_from: string, date_to: string}
     */
    protected function filtersFromRequest(Request $request): array
    {
        return [
            'search' => trim((string) $request->string('search')),
            'action' => trim((string) $request->string('action')),
            'actor_id' => $request->integer('actor_id') ?: null,
            'date_from' => trim((string) $request->string('date_from')),
            'date_to' => trim((string) $request->string('date_to')),
        ];
    }
}
