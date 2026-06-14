<?php

namespace App\Modules\WaliSantri\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Communication;
use App\Models\Santri;
use App\Models\User;
use App\Notifications\NewReplyNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class WaliSantriKomunikasiController extends Controller
{
    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $santris = $currentUser->guardianSantris()->orderBy('full_name')->get();
        $santriIds = $santris->pluck('id');

        $communications = Communication::query()
            ->whereIn('santri_id', $santriIds)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('wali-santri.komunikasi.index', [
            'santris' => $santris,
            'communications' => $communications,
        ]);
    }

    public function show(Request $request, Santri $santri): View
    {
        $currentUser = $request->user();
        $santriIds = $currentUser->guardianSantris()->pluck('santris.id');

        if (! $santriIds->contains($santri->id)) {
            abort(403);
        }

        $communications = Communication::query()
            ->where('santri_id', $santri->id)
            ->with(['user', 'parent', 'replies.user', 'forwardedFrom'])
            ->orderBy('created_at', 'asc')
            ->get();

        Communication::query()
            ->where('santri_id', $santri->id)
            ->where('direction', 'incoming')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('wali-santri.komunikasi.show', [
            'santri' => $santri,
            'communications' => $communications,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $currentUser = $request->user();

        if (! $currentUser?->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'message' => ['required', 'string', 'max:5000'],
            'parent_id' => ['nullable', 'exists:communications,id'],
        ]);

        $santriIds = $currentUser->guardianSantris()->pluck('santris.id');

        if (! $santriIds->contains((int) $validated['santri_id'])) {
            abort(403);
        }

        $communication = Communication::query()->create([
            'tenant_id' => $currentUser->tenant_id,
            'santri_id' => $validated['santri_id'],
            'user_id' => $currentUser->id,
            'message' => $validated['message'],
            'direction' => 'outgoing',
            'is_read' => false,
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        if ($validated['parent_id'] ?? null) {
            Communication::query()
                ->where('id', $validated['parent_id'])
                ->update(['is_replied' => true]);
        }

        $staff = User::query()
            ->where('tenant_id', $currentUser->tenant_id)
            ->where('status', User::STATUS_ACTIVE)
            ->permission('manage komunikasi')
            ->get();

        if ($staff->isNotEmpty()) {
            Notification::send($staff, new NewReplyNotification($communication));
        }

        return redirect()
            ->route('wali-santri.komunikasi.show', $validated['santri_id'])
            ->with('success', 'Pesan berhasil dikirim.');
    }
}
