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
        $search = trim((string) $request->string('q'));

        $santriIds = Communication::query()
            ->visibleTo($currentUser)
            ->select('santri_id')
            ->distinct()
            ->pluck('santri_id');

        $santris = Santri::query()
            ->whereIn('id', $santriIds)
            ->when($search !== '', function ($query) use ($search) {
                $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            })
            ->orderBy('full_name')
            ->get();

        $latestMessages = collect();
        foreach ($santris as $santri) {
            $latest = Communication::query()
                ->visibleTo($currentUser)
                ->where('santri_id', $santri->id)
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
                ->count();
            if ($count > 0) {
                $unreadCounts->put($santri->id, $count);
            }
        }

        return view('modules.komunikasi.index', [
            'santris' => $santris,
            'latestMessages' => $latestMessages,
            'unreadCounts' => $unreadCounts,
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
