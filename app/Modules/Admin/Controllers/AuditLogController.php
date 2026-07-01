<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function destroy(AuditLog $log): RedirectResponse
    {
        $log->delete();

        return redirect()
            ->route('admin.audit-logs')
            ->with('success', 'Audit log berhasil dihapus.');
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        $currentUser = $request->user();

        AuditLog::query()
            ->visibleTo($currentUser)
            ->delete();

        return redirect()
            ->route('admin.audit-logs')
            ->with('success', 'Semua audit log berhasil dihapus.');
    }

    public function exportPdf(Request $request): Response
    {
        $currentUser = $request->user();
        $filters = $this->filtersFromRequest($request);
        $logs = $this->applyFilters(AuditLog::query()->visibleTo($currentUser), $filters)
            ->with('user')
            ->latest('created_at')
            ->get();

        $pdf = Pdf::loadView('exports.pdf.audit-logs', compact('logs'));

        return $pdf->download('audit-logs-'.now()->format('Ymd-His').'.pdf');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $currentUser = $request->user();
        $filters = $this->filtersFromRequest($request);
        $filename = 'audit-logs-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($currentUser, $filters): void {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'Waktu',
                'User',
                'Username',
                'Method',
                'URL',
                'Status',
                'Durasi (ms)',
                'IP Address',
                'User Agent',
            ]);

            $this->applyFilters(AuditLog::query()->visibleTo($currentUser), $filters)
                ->with('user')
                ->latest('created_at')
                ->chunk(500, function ($logs) use ($handle): void {
                    foreach ($logs as $log) {
                        fputcsv($handle, [
                            $log->created_at?->format('Y-m-d H:i:s'),
                            $log->user?->name ?? 'Guest / System',
                            $log->user?->username,
                            $log->method,
                            $log->url,
                            $log->response_status,
                            $log->duration_ms,
                            $log->ip_address,
                            $log->user_agent,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
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
