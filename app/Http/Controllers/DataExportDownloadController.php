<?php

namespace App\Http\Controllers;

use App\Enums\ExportFormat;
use App\Models\DataExport;
use App\Models\Santri;
use App\Models\SantriInvoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataExportDownloadController extends Controller
{
    public function __invoke(Request $request, DataExport $dataExport): StreamedResponse
    {
        $user = $request->user();

        abort_unless($dataExport->isOwnedBy($user), 404);
        abort_unless($dataExport->isCompleted(), 404);
        abort_unless(Storage::disk($dataExport->disk)->exists($dataExport->path), 404);
        abort_unless($this->userCanAccessExport($user, $dataExport->type), 404);

        $format = ExportFormat::tryFrom($dataExport->format ?? 'xlsx') ?? ExportFormat::XLSX;

        return Storage::disk($dataExport->disk)->download($dataExport->path, $dataExport->filename, [
            'Content-Type' => $format->mimeType(),
        ]);
    }

    protected function userCanAccessExport(?User $user, string $type): bool
    {
        if (! $user) {
            return false;
        }

        $tenant = $user->tenant;

        if ($tenant && ! $tenant->hasAccess()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return match ($type) {
            DataExport::TYPE_SANTRI => $user->can('viewAny', Santri::class),
            DataExport::TYPE_SANTRI_INVOICES => $user->can('viewAny', SantriInvoice::class),
            default => false,
        };
    }
}
