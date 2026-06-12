<?php

namespace App\Modules\Backup\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\CreateTenantBackup;
use App\Models\Backup;
use App\Models\Tenant;
use App\Services\TenantBackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function __construct(
        protected TenantBackupService $backupService
    ) {}

    public function index(Request $request): View
    {
        $currentUser = $request->user();

        if ($currentUser->isSuperAdmin()) {
            $tenantId = $request->integer('tenant') ?: null;

            $backups = Backup::query()
                ->withoutTenantScope()
                ->with(['tenant', 'creator'])
                ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                ->orderBy('created_at', 'desc')
                ->paginate(20)
                ->withQueryString();

            $tenantOptions = Tenant::query()
                ->whereIn('subscription_status', ['trial', 'active', 'grace'])
                ->orderBy('name')
                ->get(['id', 'name']);
        } else {
            $backups = Backup::query()
                ->visibleTo($currentUser)
                ->with(['creator'])
                ->orderBy('created_at', 'desc')
                ->paginate(20)
                ->withQueryString();

            $tenantOptions = collect();
        }

        return view('modules.backup.index', [
            'backups' => $backups,
            'tenantOptions' => $tenantOptions,
            'tenantId' => $request->integer('tenant') ?: null,
            'isSuperAdmin' => $currentUser->isSuperAdmin(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $currentUser = $request->user();

        if ($currentUser->isSuperAdmin()) {
            $tenantId = $request->integer('tenant');

            if (! $tenantId) {
                return redirect()
                    ->route('backup.index')
                    ->with('error', 'Pilih tenant yang akan di-backup.');
            }

            $tenant = Tenant::findOrFail($tenantId);
        } else {
            $tenant = $currentUser->tenant;

            if (! $tenant) {
                return redirect()
                    ->route('backup.index')
                    ->with('error', 'Akun Anda belum terhubung ke tenant.');
            }
        }

        if (config('backups.queue', 'default') !== 'sync') {
            CreateTenantBackup::dispatch($tenant, $currentUser, Backup::TYPE_MANUAL);

            return redirect()
                ->route('backup.index')
                ->with('success', "Backup untuk {$tenant->name} sedang diproses.");
        }

        try {
            $this->backupService->storeBackup($tenant, $currentUser, Backup::TYPE_MANUAL);

            return redirect()
                ->route('backup.index')
                ->with('success', "Backup untuk {$tenant->name} berhasil dibuat.");
        } catch (\Throwable $e) {
            return redirect()
                ->route('backup.index')
                ->with('error', "Gagal membuat backup: {$e->getMessage()}");
        }
    }

    public function download(Request $request, Backup $backup): StreamedResponse
    {
        $currentUser = $request->user();

        if (! $currentUser->isSuperAdmin()) {
            abort_unless((int) $backup->tenant_id === (int) $currentUser->tenant_id, 404);
        }

        abort_unless($backup->isCompleted(), 404);
        abort_unless($backup->fileExists(), 404);

        return Storage::disk($backup->disk)->download(
            $backup->getFilePath(),
            $backup->filename
        );
    }

    public function restore(Request $request, Backup $backup): RedirectResponse
    {
        $currentUser = $request->user();

        if (! $currentUser->isSuperAdmin()) {
            abort_unless((int) $backup->tenant_id === (int) $currentUser->tenant_id, 404);
        }

        abort_unless($backup->isCompleted(), 404);
        abort_unless($backup->fileExists(), 404);

        try {
            $result = $this->backupService->restoreBackup($backup);

            return redirect()
                ->route('backup.index')
                ->with('success', "Restore berhasil! {$result['statements_executed']} tabel dipulihkan.");
        } catch (\Throwable $e) {
            return redirect()
                ->route('backup.index')
                ->with('error', "Gagal restore: {$e->getMessage()}");
        }
    }

    public function destroy(Request $request, Backup $backup): RedirectResponse
    {
        $currentUser = $request->user();

        if (! $currentUser->isSuperAdmin()) {
            abort_unless((int) $backup->tenant_id === (int) $currentUser->tenant_id, 404);
        }

        if ($backup->fileExists()) {
            Storage::disk($backup->disk)->delete($backup->getFilePath());
        }

        $backup->delete();

        return redirect()
            ->route('backup.index')
            ->with('success', 'File backup berhasil dihapus.');
    }
}
