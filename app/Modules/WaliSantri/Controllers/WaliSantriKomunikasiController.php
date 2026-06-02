<?php

namespace App\Modules\WaliSantri\Controllers;

use App\Models\Communication;
use App\Models\Santri;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WaliSantriKomunikasiController extends \App\Http\Controllers\Controller
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
        $santriIds = $currentUser->guardianSantris()->pluck('id');

        if (! $santriIds->contains($santri->id)) {
            abort(403);
        }

        $communications = Communication::query()
            ->where('santri_id', $santri->id)
            ->with('user')
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
        ]);

        $santriIds = $currentUser->guardianSantris()->pluck('id');

        if (! $santriIds->contains((int) $validated['santri_id'])) {
            abort(403);
        }

        Communication::query()->create([
            'tenant_id' => $currentUser->tenant_id,
            'santri_id' => $validated['santri_id'],
            'user_id' => $currentUser->id,
            'message' => $validated['message'],
            'direction' => 'outgoing',
            'is_read' => false,
        ]);

        return redirect()
            ->route('wali-santri.komunikasi.show', $validated['santri_id'])
            ->with('success', 'Pesan berhasil dikirim.');
    }
}
