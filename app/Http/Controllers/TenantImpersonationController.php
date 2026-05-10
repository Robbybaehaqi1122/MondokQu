<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantImpersonationController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    /**
     * Start impersonating a tenant user for support troubleshooting.
     */
    public function store(Request $request, Tenant $tenant, User $user): RedirectResponse
    {
        $actor = $request->user();

        abort_unless($actor?->isSuperAdmin(), 403);

        if ($request->session()->has('impersonation.impersonator_id')) {
            return back()->with('error', 'Akhiri sesi impersonation yang sedang berjalan sebelum masuk sebagai user lain.');
        }

        if ((int) $user->tenant_id !== (int) $tenant->id || $user->isSuperAdmin()) {
            return back()->with('error', 'User tenant yang dipilih tidak valid untuk impersonation.');
        }

        if (! $user->canAuthenticate()) {
            return back()->with('error', 'User tenant sedang tidak aktif sehingga tidak dapat di-impersonate.');
        }

        $this->activityLogger->log(
            action: 'tenant_impersonation_started',
            actor: $actor,
            target: $user,
            description: 'Superadmin mulai impersonate user tenant.',
            properties: [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'target_user_id' => $user->id,
                'target_user_name' => $user->name,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('impersonation', [
            'impersonator_id' => $actor->id,
            'impersonator_name' => $actor->name,
            'target_user_id' => $user->id,
            'target_user_name' => $user->name,
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
            'started_at' => now()->toDateTimeString(),
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Anda sedang login sebagai '.$user->name.' untuk troubleshooting tenant '.$tenant->name.'.');
    }

    /**
     * Stop impersonation and return to the original superadmin account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $impersonation = $request->session()->get('impersonation');

        if (! is_array($impersonation) || empty($impersonation['impersonator_id'])) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Tidak ada sesi impersonation yang sedang berjalan.');
        }

        $targetUser = $request->user();
        $impersonator = User::query()->find($impersonation['impersonator_id']);

        if (! $impersonator || ! $impersonator->isSuperAdmin()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('error', 'Sesi impersonation tidak valid. Silakan login ulang.');
        }

        Auth::login($impersonator);
        $request->session()->regenerate();
        $request->session()->forget('impersonation');

        $this->activityLogger->log(
            action: 'tenant_impersonation_stopped',
            actor: $impersonator,
            target: $targetUser,
            description: 'Superadmin mengakhiri impersonation user tenant.',
            properties: [
                'target_user_id' => $targetUser?->id,
                'target_user_name' => $targetUser?->name,
                'tenant_id' => $impersonation['tenant_id'] ?? null,
                'tenant_name' => $impersonation['tenant_name'] ?? null,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('saas.tenants.show', $impersonation['tenant_id'])
            ->with('success', 'Sesi impersonation dihentikan. Anda kembali sebagai '.$impersonator->name.'.');
    }
}
