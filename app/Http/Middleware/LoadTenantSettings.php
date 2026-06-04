<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class LoadTenantSettings
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->tenant_id) {
            $tenant = $user->tenant;
            $settings = $tenant?->settings ?? [];

            config([
                'tenant_settings' => $settings,
                'app.ponpes_name' => $settings['ponpes_name'] ?? $tenant?->name ?? config('app.name', 'Mondok Qu'),
                'app.ponpes_address' => $settings['ponpes_address'] ?? '',
                'app.ponpes_phone' => $settings['ponpes_phone'] ?? '',
                'app.ponpes_email' => $settings['ponpes_email'] ?? $tenant?->contact_email ?? '',
                'app.ponpes_website' => $settings['website'] ?? '',
                'app.tenant_theme_color' => $settings['theme_color'] ?? '#0d9488',
                'app.tenant_logo' => $settings['logo_path'] ?? null,
                'app.tenant_favicon' => $settings['favicon_path'] ?? null,
            ]);

            View::share('tenantSettings', $settings);
            View::share('tenantThemeColor', $settings['theme_color'] ?? '#0d9488');
            View::share('tenantLogoUrl', isset($settings['logo_path']) ? asset('storage/'.$settings['logo_path']) : null);
            View::share('tenantFaviconUrl', isset($settings['favicon_path']) ? asset('storage/'.$settings['favicon_path']) : null);
        }

        return $next($request);
    }
}
