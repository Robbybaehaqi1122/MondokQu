<?php

use App\Http\Middleware\AuditMiddleware;
use App\Http\Middleware\CheckImpersonation;
use App\Http\Middleware\EnsurePasswordIsChanged;
use App\Http\Middleware\EnsureTenantSubscriptionIsActive;
use App\Http\Middleware\LoadTenantSettings;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Sentry\Laravel\Integration;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            CheckImpersonation::class,
            LoadTenantSettings::class,
            AuditMiddleware::class,
        ]);

        $middleware->alias([
            'audit' => AuditMiddleware::class,
            'check_impersonation' => CheckImpersonation::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'password_change_required' => EnsurePasswordIsChanged::class,
            'subscription_active' => EnsureTenantSubscriptionIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions);
    })->create();
