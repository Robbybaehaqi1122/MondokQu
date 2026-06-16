<?php

namespace App\Modules\Komunikasi\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Communication;
use App\Models\Santri;
use App\Models\User;
use App\Notifications\NewReplyNotification;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class AdminKomunikasiController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    public function index(Request $request): View
    {
        $currentUser = $request->user();

        $tab = trim((string) $request->string('tab', 'inbox'));
        $search = trim((string) $request->string('q'));
        $messageSearch = trim((string) $request->string('pesan'));
        $status = trim((string) $request->string('status'));
        $direction = trim((string) $request->string('direction'));
        $dateFrom = trim((string) $request->string('date_from'));
        $dateTo = trim((string) $request->string('date_to'));
        $userId = trim((string) $request->string('user_id'));
        $sort = trim((string) $request->string('sort', 'terbaru'));

        $commQuery = Communication::query()
            ->visibleTo($currentUser)
            ->when($tab === 'inbox', fn ($q) => $q->inbox())
            ->when($tab === 'archived', fn ($q) => $q->archived())
            ->when($messageSearch !== '', fn ($q) => $q->where('message', 'like', "%{$messageSearch}%"))
            ->when($direction !== '', fn ($q) => $q->where('direction', $direction))
            ->when($dateFrom !== '', fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->when($userId !== '', fn ($q) => $q->where('user_id', $userId));

        $santriIds = (clone $commQuery)
            ->select('santri_id')
            ->distinct()
            ->pluck('santri_id');

        $santris = Santri::query()
            ->whereIn('id', $santriIds)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->when($status === 'unread', function ($query) {
                $query->whereHas('communications', fn ($q) => $q->where('direction', 'outgoing')->where('is_read', false)->inbox());
            })
            ->when($status === 'replied', function ($query) {
                $query->whereHas('communications', fn ($q) => $q->where('is_replied', true)->inbox());
            })
            ->orderBy(function ($query) {
                $query->select('created_at')
                    ->from('communications')
                    ->whereColumn('santri_id', 'santris.id')
                    ->latest()
                    ->limit(1);
            }, $sort === 'terlama' ? 'asc' : 'desc')
            ->paginate(20)
            ->withQueryString();

        $latestMessages = collect();
        foreach ($santris as $santri) {
            $latest = Communication::query()
                ->visibleTo($currentUser)
                ->where('santri_id', $santri->id)
                ->when($tab === 'inbox', fn ($q) => $q->inbox())
                ->when($tab === 'archived', fn ($q) => $q->archived())
                ->latest()
                ->first();
            if ($latest) {
                $latestMessages->put($santri->id, $latest);
            }
        }

        $unreadCounts = collect();
        foreach ($santris as $santri) {
            $count = Communication::query()
                ->visibleTo($currentUser)
                ->where('santri_id', $santri->id)
                ->where('direction', 'outgoing')
                ->where('is_read', false)
                ->inbox()
                ->count();
            if ($count > 0) {
                $unreadCounts->put($santri->id, $count);
            }
        }

        $staffUsers = User::query()
            ->where('tenant_id', $currentUser->tenant_id)
            ->where('status', User::STATUS_ACTIVE)
            ->permission('manage komunikasi')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('modules.komunikasi.index', [
            'tab' => $tab,
            'santris' => $santris,
            'latestMessages' => $latestMessages,
            'unreadCounts' => $unreadCounts,
            'staffUsers' => $staffUsers,
            'filters' => [
                'q' => $search,
                'pesan' => $messageSearch,
                'status' => $status,
                'direction' => $direction,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'user_id' => $userId,
                'sort' => $sort,
            ],
        ]);
    }

    public function trash(Request $request): View
    {
        $currentUser = $request->user();

        $search = trim((string) $request->string('q'));

        $communications = Communication::query()
            ->visibleTo($currentUser)
            ->onlyTrashed()
            ->with(['santri', 'user'])
            ->when($search !== '', fn ($q) => $q->where('message', 'like', "%{$search}%"))
            ->orderBy('deleted_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('modules.komunikasi.trash', [
            'communications' => $communications,
            'filters' => ['q' => $search],
        ]);
    }

    public function show(Request $request, Santri $santri): View
    {
        $currentUser = $request->user();

        $santri = Santri::query()
            ->visibleTo($currentUser)
            ->findOrFail($santri->id);

        $communications = Communication::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $santri->id)
            ->with(['user', 'parent', 'replies.user', 'forwardedFrom'])
            ->withTrashed()
            ->orderBy('created_at', 'asc')
            ->get();

        $santriList = Santri::query()
            ->visibleTo($currentUser)
            ->where('id', '!=', $santri->id)
            ->orderBy('full_name')
            ->get();

        return view('modules.komunikasi.show', [
            'santri' => $santri,
            'communications' => $communications,
            'santriList' => $santriList,
        ]);
    }

    public function archive(Request $request, Communication $communication): RedirectResponse
    {
        $currentUser = $request->user();

        $communication = Communication::query()
            ->visibleTo($currentUser)
            ->findOrFail($communication->id);

        $communication->update(['archived_at' => now()]);

        $this->activityLogger->log(
            action: 'komunikasi_archived',
            actor: $currentUser,
            target: $communication,
            description: "Mengarsipkan pesan dari {$communication->santri?->full_name}.",
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()->back()->with('success', 'Pesan berhasil diarsipkan.');
    }

    public function restore(Request $request, Communication $communication): RedirectResponse
    {
        $currentUser = $request->user();

        $communication = Communication::query()
            ->visibleTo($currentUser)
            ->withTrashed()
            ->findOrFail($communication->id);

        $communication->restore();

        $this->activityLogger->log(
            action: 'komunikasi_restored',
            actor: $currentUser,
            target: $communication,
            description: "Mengembalikan pesan dari {$communication->santri?->full_name}.",
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()->back()->with('success', 'Pesan berhasil dikembalikan.');
    }

    public function forceDelete(Request $request, Communication $communication): RedirectResponse
    {
        $currentUser = $request->user();

        $communication = Communication::query()
            ->visibleTo($currentUser)
            ->withTrashed()
            ->findOrFail($communication->id);

        $santriName = $communication->santri?->full_name;
        $communication->forceDelete();

        $this->activityLogger->log(
            action: 'komunikasi_force_deleted',
            actor: $currentUser,
            target: $communication,
            description: "Menghapus permanen pesan dari {$santriName}.",
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()->back()->with('success', 'Pesan berhasil dihapus permanen.');
    }

    public function destroy(Request $request, Communication $communication): RedirectResponse
    {
        $currentUser = $request->user();

        $communication = Communication::query()
            ->visibleTo($currentUser)
            ->findOrFail($communication->id);

        $communication->delete();

        $this->activityLogger->log(
            action: 'komunikasi_deleted',
            actor: $currentUser,
            target: $communication,
            description: "Menghapus pesan dari {$communication->santri?->full_name}.",
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()->back()->with('success', 'Pesan berhasil dipindahkan ke sampah.');
    }

    public function batch(Request $request): RedirectResponse
    {
        $currentUser = $request->user();

        $validated = $request->validate([
            'action' => ['required', 'string', 'in:archive,restore,delete,mark_read'],
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:santris,id'],
        ]);

        $action = $validated['action'];

        $communicationQuery = Communication::query()
            ->visibleTo($currentUser)
            ->whereIn('santri_id', $validated['ids']);

        $affected = match ($action) {
            'archive' => (clone $communicationQuery)->inbox()->update(['archived_at' => now()]),
            'delete' => (clone $communicationQuery)->inbox()->delete(),
            'restore' => (clone $communicationQuery)->onlyTrashed()->restore(),
            'mark_read' => (clone $communicationQuery)
                ->where('direction', 'outgoing')
                ->where('is_read', false)
                ->update(['is_read' => true]),
        };

        $this->activityLogger->log(
            action: 'komunikasi_batch_'.$action,
            actor: $currentUser,
            target: null,
            description: "Batch {$action} untuk {$affected} pesan dari ".count($validated['ids']).' santri.',
            properties: ['action' => $action, 'affected' => $affected, 'santri_ids' => $validated['ids']],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        $messages = [
            'archive' => count($validated['ids']).' percakapan berhasil diarsipkan.',
            'restore' => count($validated['ids']).' percakapan berhasil dikembalikan.',
            'delete' => count($validated['ids']).' percakapan berhasil dipindahkan ke sampah.',
            'mark_read' => count($validated['ids']).' percakapan berhasil ditandai terbaca.',
        ];

        return redirect()->back()->with('success', $messages[$action]);
    }

    public function store(Request $request, Santri $santri): RedirectResponse
    {
        $this->authorize('create', Communication::class);

        $currentUser = $request->user();

        $santri = Santri::query()
            ->visibleTo($currentUser)
            ->findOrFail($santri->id);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'parent_id' => ['nullable', 'exists:communications,id'],
        ]);

        $communication = Communication::query()->create([
            'tenant_id' => $currentUser->tenant_id,
            'santri_id' => $santri->id,
            'user_id' => $currentUser->id,
            'message' => $validated['message'],
            'direction' => 'incoming',
            'is_read' => false,
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        if ($validated['parent_id'] ?? null) {
            Communication::query()
                ->where('id', $validated['parent_id'])
                ->update(['is_replied' => true]);
        }

        $guardians = $santri->guardians()
            ->where('status', User::STATUS_ACTIVE)
            ->get();

        if ($guardians->isNotEmpty()) {
            Notification::send($guardians, new NewReplyNotification($communication));
        }

        $this->activityLogger->log(
            action: 'komunikasi_replied',
            actor: $currentUser,
            target: $santri,
            description: "Membalas pesan dari wali {$santri->full_name}.",
            properties: [
                'santri_id' => $santri->id,
                'santri_name' => $santri->full_name,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('komunikasi.show', $santri)
            ->with('success', 'Balasan berhasil dikirim.');
    }

    public function markAsRead(Request $request, Communication $communication): RedirectResponse
    {
        $currentUser = $request->user();

        $communication = Communication::query()
            ->visibleTo($currentUser)
            ->findOrFail($communication->id);

        $communication->update(['is_read' => true]);

        return redirect()
            ->route('komunikasi.show', $communication->santri_id)
            ->with('success', 'Pesan ditandai sudah dibaca.');
    }

    public function forward(Request $request, Santri $santri, Communication $communication): RedirectResponse
    {
        $this->authorize('create', Communication::class);

        $currentUser = $request->user();

        $santri = Santri::query()
            ->visibleTo($currentUser)
            ->findOrFail($santri->id);

        $communication = Communication::query()
            ->visibleTo($currentUser)
            ->findOrFail($communication->id);

        $validated = $request->validate([
            'target_santri_id' => ['required', 'exists:santris,id'],
        ]);

        $targetSantri = Santri::query()
            ->visibleTo($currentUser)
            ->findOrFail($validated['target_santri_id']);

        $forwarded = Communication::query()->create([
            'tenant_id' => $currentUser->tenant_id,
            'santri_id' => $targetSantri->id,
            'user_id' => $currentUser->id,
            'message' => $communication->message,
            'direction' => 'incoming',
            'is_read' => false,
            'forwarded_from_id' => $communication->id,
        ]);

        $guardians = $targetSantri->guardians()
            ->where('status', User::STATUS_ACTIVE)
            ->get();

        if ($guardians->isNotEmpty()) {
            Notification::send($guardians, new NewReplyNotification($forwarded));
        }

        $this->activityLogger->log(
            action: 'komunikasi_forwarded',
            actor: $currentUser,
            target: $targetSantri,
            description: "Meneruskan pesan ke wali {$targetSantri->full_name}.",
            properties: [
                'original_santri_id' => $santri->id,
                'target_santri_id' => $targetSantri->id,
                'communication_id' => $communication->id,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('komunikasi.show', $targetSantri)
            ->with('success', 'Pesan berhasil diteruskan.');
    }
}
