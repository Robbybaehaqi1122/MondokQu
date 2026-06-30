<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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

            \Sentry\addBreadcrumb(new \Sentry\Breadcrumb(
                \Sentry\Breadcrumb::LEVEL_WARNING,
                \Sentry\Breadcrumb::TYPE_NAVIGATION,
                'middleware',
                'EnsureTenantSubscriptionIsActive: no tenant for user ' . ($user->id ?? '?')
            ));

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

        \Sentry\addBreadcrumb(new \Sentry\Breadcrumb(
            \Sentry\Breadcrumb::LEVEL_WARNING,
            \Sentry\Breadcrumb::TYPE_NAVIGATION,
            'middleware',
            'EnsureTenantSubscriptionIsActive: subscription expired for tenant ' . ($tenant->id ?? '?')
        ));

        $request->session()->reflash();

        return redirect()
            ->route('subscription.expired')
            ->with('error', 'Masa trial atau langganan tenant Anda sudah berakhir. Silakan perpanjang paket untuk melanjutkan.');
    }
}
