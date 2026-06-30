<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantSubscriptionIsActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isSuperAdmin()) {
            return $next($request);
        }

        $tenant = $user->tenant;

        if (! $tenant) {
            if ($request->routeIs([
                'profile.edit',
                'profile.update',
                'password.update',
                'logout',
                'subscription.expired',
            ])) {
                return $next($request);
            }

            Log::warning('middleware.tenant.no_tenant', [
                'user_id' => $user->id ?? '?',
                'target_route' => 'subscription.expired',
            ]);

            $request->session()->reflash();

            return redirect()
                ->route('subscription.expired')
                ->with('error', 'Akun Anda belum terhubung ke tenant pondok. Silakan hubungi admin platform.');
        }

        if ($tenant->hasAccess() || $request->routeIs([
            'profile.edit',
            'profile.update',
            'password.update',
            'logout',
            'subscription.expired',
        ])) {
            return $next($request);
        }

        Log::warning('middleware.tenant.subscription_expired', [
            'user_id' => $user->id ?? '?',
            'tenant_id' => $tenant->id ?? '?',
            'target_route' => 'subscription.expired',
        ]);

        $request->session()->reflash();

        return redirect()
            ->route('subscription.expired')
            ->with('error', 'Masa trial atau langganan tenant Anda sudah berakhir. Silakan perpanjang paket untuk melanjutkan.');
    }
}
