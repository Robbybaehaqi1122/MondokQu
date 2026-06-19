<?php

namespace App\Modules\Santri\Controllers;

use App\Enums\ExportFormat;
use App\Exports\SantriExcelExport;
use App\Exports\SantriPdfExport;
use App\Exports\SantriTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\SantriImport;
use App\Jobs\ProcessSantriImportJob;
use App\Models\ActivityLog;
use App\Models\DataExport;
use App\Models\DataImport;
use App\Models\MataPelajaran;
use App\Models\Room;
use App\Models\Santri;
use App\Models\SantriDocument;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Santri\Requests\ImportSantriRequest;
use App\Modules\Santri\Requests\StoreSantriRequest;
use App\Modules\Santri\Requests\UpdateSantriRequest;
use App\Services\ActivityLogger;
use App\Services\DataExportManager;
use App\Services\FormatDispatcher;
use App\Services\SantriService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SantriManagementController extends Controller
{
    public function __construct(
        protected SantriService $santriService,
        protected DataExportManager $dataExportManager,
        protected FormatDispatcher $formatDispatcher,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Santri::class);

        $currentUser = $request->user();
        $query = trim((string) $request->string('q'));
        $selectedStatus = trim((string) $request->string('status'));
        $selectedGender = trim((string) $request->string('gender'));

        $baseQuery = Santri::query()->visibleTo($currentUser);

        $santris = (clone $baseQuery)
            ->with(['creator', 'guardians', 'room', 'documents'])
            ->withFilters($query, $selectedStatus, $selectedGender)
            ->orderBy('full_name')
            ->paginate(10)
            ->withQueryString();
        $tenantIds = $santris->getCollection()
            ->pluck('tenant_id')
            ->push($currentUser?->tenant_id)
            ->filter()
            ->unique()
            ->values();

        return view('santri.index', [
            'allSantriCount' => (clone $baseQuery)->count(),
            'filters' => [
                'q' => $query,
                'status' => $selectedStatus,
                'gender' => $selectedGender,
            ],
            'genders' => $this->genderOptions(),
            'canCreateSantri' => $currentUser?->can('create', Santri::class) ?? false,
            'guardianUserOptionsByTenant' => $this->guardianUserOptionsByTenant($currentUser, $tenantIds),
            'roomOptionsByTenant' => $this->roomOptionsByTenant($currentUser, $tenantIds),
            'dataExports' => DataExport::query()
                ->visibleTo($currentUser)
                ->forType(DataExport::TYPE_SANTRI)
                ->latest()
                ->limit(5)
                ->get(),
            'statuses' => $this->statusOptions(),
            'santris' => $santris,
        ]);
    }

    public function export(Request $request): RedirectResponse|BinaryFileResponse|StreamedResponse|Response
    {
        $this->authorize('viewAny', Santri::class);

        $currentUser = $request->user();
        $query = trim((string) $request->string('q'));
        $selectedStatus = trim((string) $request->string('status'));
        $selectedGender = trim((string) $request->string('gender'));
        $format = ExportFormat::tryFrom($request->string('format', 'xlsx')) ?? ExportFormat::XLSX;

        $rowCount = (new SantriExcelExport($currentUser, $query, $selectedStatus, $selectedGender))->query()->count();

        if ($this->dataExportManager->shouldQueue($rowCount)) {
            $filename = match ($format) {
                ExportFormat::XLSX => (new SantriExcelExport)->filename(),
                ExportFormat::PDF => (new SantriPdfExport($currentUser, $query, $selectedStatus, $selectedGender))->filename(),
            };

            $this->dataExportManager->queue(
                $currentUser,
                DataExport::TYPE_SANTRI,
                'Export Data Santri',
                $filename,
                [
                    'q' => $query,
                    'status' => $selectedStatus,
                    'gender' => $selectedGender,
                ],
                $rowCount,
                $format->value
            );

            return redirect()
                ->route('santri.index', array_filter([
                    'q' => $query,
                    'status' => $selectedStatus,
                    'gender' => $selectedGender,
                ], fn ($value) => $value !== ''))
                ->with('success', 'Export data santri sedang diproses di background. Link download akan muncul di daftar export terbaru setelah selesai.');
        }

        return $this->formatDispatcher->downloadSantri($currentUser, $format, $query, $selectedStatus, $selectedGender);
    }

    public function show(Santri $santri): View
    {
        $this->authorize('view', $santri);

        $santri->load(['creator', 'guardians', 'room', 'documents']);

        $currentUser = request()->user();

        $activities = ActivityLog::query()
            ->with('actor')
            ->where('target_type', Santri::class)
            ->where('target_id', $santri->id)
            ->latest()
            ->paginate(30);

        $mapels = MataPelajaran::query()
            ->visibleTo($currentUser)
            ->active()
            ->orderBy('nama')
            ->get(['id', 'nama']);

        $totalPoin = $santri->totalPoin();
        $currentThreshold = $santri->currentSanctionThreshold();
        $nextThreshold = $santri->nextSanctionThreshold();

        return view('santri.show', [
            'canDeleteSantri' => $currentUser?->can('delete', $santri) ?? false,
            'canUpdateSantri' => $currentUser?->can('update', $santri) ?? false,
            'santri' => $santri,
            'activities' => $activities,
            'mapels' => $mapels,
            'totalPoin' => $totalPoin,
            'currentThreshold' => $currentThreshold,
            'nextThreshold' => $nextThreshold,
        ]);
    }

    public function store(StoreSantriRequest $request): RedirectResponse
    {
        $this->authorize('create', Santri::class);

        try {
            $this->santriService->create(
                tenantId: (int) $request->user()->tenant_id,
                validated: $request->validated(),
                photo: $request->file('photo'),
                guardianUserIds: $request->guardianUserIds(),
                actor: $request->user(),
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );
        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('santri.index')
            ->with('success', 'Data santri berhasil ditambahkan.');
    }

    public function update(UpdateSantriRequest $request, Santri $santri): RedirectResponse
    {
        $this->authorize('update', $santri);

        $this->santriService->update(
            santri: $santri,
            validated: $request->validated(),
            photo: $request->file('photo'),
            deletePhoto: $request->boolean('delete_photo'),
            guardianUserIds: $request->guardianUserIds(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()
            ->route('santri.index')
            ->with('success', 'Data santri berhasil diperbarui.');
    }

    public function destroy(Request $request, Santri $santri): RedirectResponse
    {
        $this->authorize('delete', $santri);

        $this->santriService->delete(
            santri: $santri,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()
            ->route('santri.index')
            ->with('success', 'Data santri berhasil dihapus.');
    }

    public function uploadDocument(Request $request, Santri $santri): RedirectResponse
    {
        $this->authorize('update', $santri);

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:'.implode(',', array_keys(SantriDocument::types()))],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $file = $request->file('file');
        $path = $file->store('santri-documents/'.$santri->id, 'public');

        SantriDocument::query()->create([
            'santri_id' => $santri->id,
            'type' => $validated['type'],
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'notes' => $validated['notes'],
            'uploaded_by' => $request->user()->id,
        ]);

        app(ActivityLogger::class)->log(
            action: 'santri_document_uploaded',
            actor: $request->user(),
            target: $santri,
            description: "Upload dokumen {$validated['type']} untuk {$santri->full_name}.",
            properties: [
                'document_type' => $validated['type'],
                'santri_id' => $santri->id,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()
            ->route('santri.show', $santri)
            ->with('success', 'Dokumen berhasil diupload.');
    }

    public function downloadDocument(Santri $santri, SantriDocument $document): StreamedResponse|RedirectResponse
    {
        $this->authorize('view', $santri);

        if ($document->santri_id !== $santri->id) {
            abort(404);
        }

        if (! Storage::disk('public')->exists($document->file_path)) {
            return redirect()
                ->route('santri.show', $santri)
                ->with('error', 'File dokumen tidak ditemukan.');
        }

        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }

    public function destroyDocument(Request $request, Santri $santri, SantriDocument $document): RedirectResponse
    {
        $this->authorize('update', $santri);

        if ($document->santri_id !== $santri->id) {
            abort(404);
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        app(ActivityLogger::class)->log(
            action: 'santri_document_deleted',
            actor: $request->user(),
            target: $santri,
            description: "Hapus dokumen {$document->typeLabel()} untuk {$santri->full_name}.",
            properties: [
                'document_type' => $document->type,
                'santri_id' => $santri->id,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()
            ->route('santri.show', $santri)
            ->with('success', 'Dokumen berhasil dihapus.');
    }

    public function importIndex(Request $request): View
    {
        $this->authorize('create', Santri::class);

        $currentUser = $request->user();

        $dataImports = DataImport::query()
            ->visibleTo($currentUser)
            ->forType(DataImport::TYPE_SANTRI)
            ->latest()
            ->limit(10)
            ->get();

        return view('santri.import.index', [
            'dataImports' => $dataImports,
            'tenants' => $currentUser->isSuperAdmin() ? Tenant::query()->orderBy('name')->get() : collect(),
        ]);
    }

    public function downloadTemplate(Request $request): StreamedResponse|Response|BinaryFileResponse
    {
        $this->authorize('create', Santri::class);

        $format = (string) $request->string('format', 'csv');

        if ($format === 'xlsx') {
            return Excel::download(
                new SantriTemplateExport,
                (new SantriTemplateExport)->filename()
            );
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template-import-santri.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'nama',
                'nis',
                'nisn',
                'tempat_lahir',
                'tanggal_lahir',
                'jenis_kelamin',
                'alamat',
                'nama_ayah',
                'no_telp_ayah',
                'pendidikan_ayah',
                'pekerjaan_ayah',
                'nama_ibu',
                'no_telp_ibu',
                'pendidikan_ibu',
                'pekerjaan_ibu',
                'no_telp_wali',
                'hubungan_wali',
                'alamat_wali',
                'kamar',
                'status',
            ]);
            fputcsv($handle, [
                'Ali bin Abi Thalib',
                '2026001',
                '1234567890',
                'Jakarta',
                '2010-06-17',
                'laki-laki',
                'Jl. Contoh No. 123, Jakarta',
                'Abu Thalib',
                '081234567890',
                'SMA',
                'Wiraswasta',
                'Fatimah',
                '081234567891',
                'SMP',
                'Ibu Rumah Tangga',
                '081234567892',
                'Paman',
                'Jl. Wali No. 789, Jakarta',
                'Al-Ghazali',
                'aktif',
            ]);
            fputcsv($handle, [
                'Aisyah binti Abu Bakar',
                '2026002',
                '1234567891',
                'Bandung',
                '2011-08-22',
                'perempuan',
                'Jl. Contoh No. 456, Bandung',
                'Abu Bakar',
                '081234567893',
                'S1',
                'Guru',
                'Ummi Rahmah',
                '081234567894',
                'D3',
                'Perawat',
                '081234567895',
                'Kakek',
                'Jl. Wali No. 100, Bandung',
                'Al-Farabi',
                'aktif',
            ]);
            fclose($handle);
        };

        return response()->streamDownload($callback, 'template-import-santri.csv', $headers);
    }

    public function previewImport(ImportSantriRequest $request): View|RedirectResponse
    {
        $this->authorize('create', Santri::class);

        $currentUser = $request->user();
        $tenantId = $request->input('tenant_id', $currentUser->tenant_id);
        $file = $request->file('file');

        $importer = new SantriImport($tenantId, (int) $currentUser->id);
        $rows = Excel::toCollection($importer, $file)->first() ?? collect();

        if ($rows->isEmpty()) {
            return redirect()
                ->route('santri.import.index')
                ->with('error', 'File tidak mengandung data atau format tidak dikenali.');
        }

        $result = $importer->preview($rows);

        $previewKey = 'santri_import_preview_'.$currentUser->id;
        Storage::disk('local')->put(
            "temp/{$previewKey}.json",
            json_encode([
                'rows' => $rows->toArray(),
                'tenant_id' => $tenantId,
                'user_id' => $currentUser->id,
            ])
        );

        return view('santri.import.preview', [
            'previewKey' => $previewKey,
            'totalRows' => $result['total'],
            'validCount' => $result['valid_count'],
            'errorCount' => $result['error_count'],
            'validRows' => $result['valid_rows'],
            'errorRows' => $result['error_rows'],
        ]);
    }

    public function processImport(Request $request): RedirectResponse
    {
        $this->authorize('create', Santri::class);

        $currentUser = $request->user();
        $previewKey = $request->input('preview_key');

        if (! $previewKey || ! Storage::disk('local')->exists("temp/{$previewKey}.json")) {
            return redirect()
                ->route('santri.import.index')
                ->with('error', 'Sesi import tidak valid. Silakan upload ulang file.');
        }

        $data = json_decode(Storage::disk('local')->get("temp/{$previewKey}.json"), true);
        $rows = collect($data['rows']);
        $tenantId = $data['tenant_id'] ?? $currentUser->tenant_id;

        $importer = new SantriImport($tenantId, (int) $currentUser->id);

        $threshold = (int) config('imports.queue_threshold', 500);

        if ($rows->count() >= $threshold) {
            $import = DataImport::query()->create([
                'tenant_id' => $currentUser->tenant_id,
                'user_id' => $currentUser->id,
                'type' => DataImport::TYPE_SANTRI,
                'name' => 'Import Data Santri',
                'status' => DataImport::STATUS_PENDING,
                'total_rows' => $rows->count(),
                'expires_at' => now()->addHours(24),
            ]);

            $importPath = "imports/{$import->id}/data.json";
            Storage::disk('local')->put($importPath, json_encode($data));
            $import->forceFill(['disk' => 'local', 'path' => $importPath])->save();

            ProcessSantriImportJob::dispatch($import->id);

            Storage::disk('local')->delete("temp/{$previewKey}.json");

            return redirect()
                ->route('santri.import.index')
                ->with('success', 'Import data santri sedang diproses di background. Status import dapat dilihat di halaman ini.');
        }

        $result = $importer->import($rows);

        Storage::disk('local')->delete("temp/{$previewKey}.json");

        $this->logImportActivity($currentUser, $result, $request);

        return redirect()
            ->route('santri.import.index')
            ->with('importResult', [
                'success' => $result['success_rows'],
                'failed' => $result['failed_rows'],
                'total' => $result['total'],
                'errors' => $result['errors'],
            ]);
    }

    protected function logImportActivity(User $actor, array $result, Request $request): void
    {
        app(ActivityLogger::class)->log(
            action: 'santri_imported',
            actor: $actor,
            target: null,
            description: "Import data santri: {$result['success_rows']} berhasil, {$result['failed_rows']} gagal dari {$result['total']} baris.",
            properties: [
                'success_rows' => $result['success_rows'],
                'failed_rows' => $result['failed_rows'],
                'total_rows' => $result['total'],
                'errors' => $result['errors']->toArray(),
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );
    }

    protected function statusOptions(): Collection
    {
        return collect(Santri::availableStatuses())
            ->map(fn (string $status): array => [
                'value' => $status,
                'label' => match ($status) {
                    Santri::STATUS_ACTIVE => 'Aktif',
                    Santri::STATUS_LEAVE => 'Libur',
                    Santri::STATUS_EXITED => 'Keluar',
                    Santri::STATUS_ALUMNI => 'Alumni',
                    default => ucfirst($status),
                },
            ]);
    }

    protected function genderOptions(): Collection
    {
        return collect(Santri::availableGenders())
            ->map(fn (string $gender): array => [
                'value' => $gender,
                'label' => match ($gender) {
                    Santri::GENDER_MALE => 'Laki-laki',
                    Santri::GENDER_FEMALE => 'Perempuan',
                    default => ucfirst($gender),
                },
            ]);
    }

    protected function guardianUserOptionsByTenant(?User $currentUser, Collection $tenantIds): Collection
    {
        if (! $currentUser || $tenantIds->isEmpty()) {
            return collect();
        }

        return User::query()
            ->visibleTo($currentUser)
            ->whereIn('tenant_id', $tenantIds)
            ->whereHas('roles', fn ($query) => $query->where('name', 'Wali Santri'))
            ->orderBy('name')
            ->get(['id', 'tenant_id', 'name', 'username', 'email'])
            ->groupBy('tenant_id');
    }

    protected function roomOptionsByTenant(?User $currentUser, Collection $tenantIds): Collection
    {
        if (! $currentUser || $tenantIds->isEmpty()) {
            return collect();
        }

        return Room::query()
            ->withoutTenantScope()
            ->when(! $currentUser->isSuperAdmin(), fn ($query) => $query->where('tenant_id', $currentUser->tenant_id))
            ->whereIn('tenant_id', $tenantIds)
            ->orderBy('name')
            ->get(['id', 'tenant_id', 'name', 'status'])
            ->groupBy('tenant_id');
    }
}
