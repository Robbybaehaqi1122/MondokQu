<?php

namespace App\Modules\Tahfidz\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\TahfidzRecord;
use App\Models\TahfidzSession;
use App\Models\TahfidzSurah;
use App\Modules\Tahfidz\Requests\StoreTahfidzSessionRequest;
use App\Notifications\Concerns\NotifiesGuardians;
use App\Notifications\NewTahfidzSessionNotification;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TahfidzSetoranController extends Controller
{
    use NotifiesGuardians;

    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $search = trim((string) $request->string('q'));
        $selectedSantri = trim((string) $request->string('santri'));
        $dateFrom = trim((string) $request->string('date_from'));
        $dateTo = trim((string) $request->string('date_to'));

        $sessions = TahfidzSession::query()
            ->visibleTo($currentUser)
            ->with(['santri', 'musyrif', 'records.surah'])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('santri', function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->when($selectedSantri !== '', fn ($query) => $query->where('santri_id', $selectedSantri))
            ->when($dateFrom !== '', fn ($query) => $query->whereDate('session_date', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($query) => $query->whereDate('session_date', '<=', $dateTo))
            ->orderBy('session_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $santriOptions = Santri::query()
            ->visibleTo($currentUser)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'nis']);

        return view('modules.tahfidz.setoran.index', [
            'sessions' => $sessions,
            'santriOptions' => $santriOptions,
            'filters' => [
                'q' => $search,
                'santri' => $selectedSantri,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'evaluationOptions' => TahfidzRecord::availableEvaluations(),
        ]);
    }

    public function create(Request $request): View
    {
        $currentUser = $request->user();

        $santriOptions = Santri::query()
            ->visibleTo($currentUser)
            ->where('status', Santri::STATUS_ACTIVE)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'nis']);

        $surahOptions = TahfidzSurah::query()
            ->orderBy('number')
            ->get();

        return view('modules.tahfidz.setoran.create', [
            'santriOptions' => $santriOptions,
            'surahOptions' => $surahOptions,
            'evaluationOptions' => TahfidzRecord::availableEvaluations(),
        ]);
    }

    public function store(StoreTahfidzSessionRequest $request): RedirectResponse
    {
        $this->authorize('create', TahfidzSession::class);

        $currentUser = $request->user();
        $validated = $request->validated();

        $santri = Santri::findOrFail($validated['santri_id']);

        $session = DB::transaction(function () use ($validated, $currentUser, $santri) {
            $session = TahfidzSession::query()->create([
                'tenant_id' => $santri->tenant_id,
                'santri_id' => $santri->id,
                'musyrif_id' => $currentUser->id,
                'session_date' => $validated['session_date'],
                'status' => $validated['status'] ?? TahfidzSession::STATUS_COMPLETED,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['records'] as $record) {
                TahfidzRecord::query()->create([
                    'tenant_id' => $santri->tenant_id,
                    'tahfidz_session_id' => $session->id,
                    'surah_id' => $record['surah_id'],
                    'verse_start' => $record['verse_start'],
                    'verse_end' => $record['verse_end'],
                    'evaluation' => $record['evaluation'],
                    'notes' => $record['notes'] ?? null,
                ]);
            }

            return $session;
        });

        $santriName = $session->santri?->full_name ?? "Santri #{$session->santri_id}";

        $session->loadMissing('records');
        $this->notifyGuardians($santri, new NewTahfidzSessionNotification($session));

        $this->activityLogger->log(
            action: 'tahfidz_setoran_created',
            actor: $currentUser,
            target: $session,
            description: "Setoran hafalan untuk {$santriName} dicatat.",
            properties: [
                'santri_id' => $session->santri_id,
                'santri_name' => $santriName,
                'session_date' => $session->session_date->toDateString(),
                'records_count' => count($validated['records']),
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('tahfidz.setoran.index')
            ->with('success', "Setoran hafalan untuk {$santriName} berhasil dicatat.");
    }

    public function show(Request $request, TahfidzSession $tahfidzSession): View
    {
        $currentUser = $request->user();

        $session = TahfidzSession::query()
            ->visibleTo($currentUser)
            ->with(['santri', 'musyrif', 'records.surah'])
            ->findOrFail($tahfidzSession->id);

        return view('modules.tahfidz.setoran.show', [
            'session' => $session,
            'evaluationOptions' => TahfidzRecord::availableEvaluations(),
        ]);
    }

    public function edit(Request $request, TahfidzSession $tahfidzSession): View
    {
        $currentUser = $request->user();

        $session = TahfidzSession::query()
            ->visibleTo($currentUser)
            ->with(['santri', 'records.surah'])
            ->findOrFail($tahfidzSession->id);

        $surahOptions = TahfidzSurah::query()
            ->orderBy('number')
            ->get();

        return view('modules.tahfidz.setoran.edit', [
            'session' => $session,
            'surahOptions' => $surahOptions,
            'evaluationOptions' => TahfidzRecord::availableEvaluations(),
        ]);
    }

    public function update(StoreTahfidzSessionRequest $request, TahfidzSession $tahfidzSession): RedirectResponse
    {
        $currentUser = $request->user();
        $validated = $request->validated();

        $session = TahfidzSession::query()
            ->visibleTo($currentUser)
            ->findOrFail($tahfidzSession->id);

        $this->authorize('update', $session);

        DB::transaction(function () use ($session, $validated) {
            $session->update([
                'session_date' => $validated['session_date'],
                'status' => $validated['status'] ?? TahfidzSession::STATUS_COMPLETED,
                'notes' => $validated['notes'] ?? null,
            ]);

            $session->records()->delete();

            foreach ($validated['records'] as $record) {
                TahfidzRecord::query()->create([
                    'tenant_id' => $session->tenant_id,
                    'tahfidz_session_id' => $session->id,
                    'surah_id' => $record['surah_id'],
                    'verse_start' => $record['verse_start'],
                    'verse_end' => $record['verse_end'],
                    'evaluation' => $record['evaluation'],
                    'notes' => $record['notes'] ?? null,
                ]);
            }
        });

        $santriName = $session->santri?->full_name ?? "Santri #{$session->santri_id}";

        $this->activityLogger->log(
            action: 'tahfidz_setoran_updated',
            actor: $currentUser,
            target: $session,
            description: "Setoran hafalan untuk {$santriName} diperbarui.",
            properties: [
                'santri_id' => $session->santri_id,
                'santri_name' => $santriName,
                'session_date' => $session->session_date->toDateString(),
                'records_count' => count($validated['records']),
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('tahfidz.setoran.index')
            ->with('success', "Setoran hafalan untuk {$santriName} berhasil diperbarui.");
    }

    public function destroy(Request $request, TahfidzSession $tahfidzSession): RedirectResponse
    {
        $currentUser = $request->user();

        $session = TahfidzSession::query()
            ->visibleTo($currentUser)
            ->findOrFail($tahfidzSession->id);

        $this->authorize('delete', $session);

        $santriName = $session->santri?->full_name ?? "Santri #{$session->santri_id}";

        $this->activityLogger->log(
            action: 'tahfidz_setoran_deleted',
            actor: $currentUser,
            target: $session,
            description: "Setoran hafalan untuk {$santriName} dihapus.",
            properties: [
                'santri_id' => $session->santri_id,
                'santri_name' => $santriName,
                'session_date' => $session->session_date->toDateString(),
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        $session->delete();

        return redirect()
            ->route('tahfidz.setoran.index')
            ->with('success', "Setoran hafalan untuk {$santriName} berhasil dihapus.");
    }
}
