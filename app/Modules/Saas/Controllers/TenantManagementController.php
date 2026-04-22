<?php

namespace App\Modules\Saas\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Auth\Actions\SendEmailVerificationNotificationAction;
use App\Modules\Saas\Actions\UpdateTenantSubscriptionAction;
use App\Modules\Saas\Requests\StoreTenantRequest;
use App\Modules\Saas\Requests\UpdateTenantSubscriptionRequest;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantManagementController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger,
        protected SendEmailVerificationNotificationAction $sendVerificationNotification
    ) {
    }

    /**
     * Display the tenant management page.
     */
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $search = trim((string) $request->string('search'));
        $status = $request->string('status')->toString();

        return view('modules.saas.tenants.index', [
            'tenants' => Tenant::query()
                ->with(['owner'])
                ->withCount(['users', 'santris'])
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($tenantQuery) use ($search) {
                        $tenantQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%")
                            ->orWhere('contact_email', 'like', "%{$search}%")
                            ->orWhere('contact_phone_number', 'like', "%{$search}%");
                    });
                })
                ->when(in_array($status, [
                    Tenant::SUBSCRIPTION_TRIAL,
                    Tenant::SUBSCRIPTION_ACTIVE,
                    Tenant::SUBSCRIPTION_GRACE,
                    Tenant::SUBSCRIPTION_EXPIRED,
                ], true), fn ($query) => $query->where('subscription_status', $status))
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString(),
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Store a newly created tenant with an initial trial window.
     */
    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $actor = $request->user();

        $tenant = DB::transaction(function () use ($validated, $request): Tenant {
            $tenant = Tenant::query()->create([
                'name' => $validated['name'],
                'slug' => ($validated['slug'] ?? null) ?: Str::slug($validated['name']),
                'contact_email' => ($validated['contact_email'] ?? null) ?: null,
                'contact_phone_number' => ($validated['contact_phone_number'] ?? null) ?: null,
                'subscription_plan' => config('saas.default_plan', 'trial'),
                'subscription_status' => Tenant::SUBSCRIPTION_TRIAL,
                'trial_ends_at' => now()->addDays((int) config('saas.trial_days', 14)),
                'subscription_starts_at' => null,
                'subscription_ends_at' => null,
                'grace_ends_at' => null,
                'owner_id' => null,
            ]);

            if ($request->boolean('create_owner_account')) {
                $owner = User::query()->create([
                    'tenant_id' => $tenant->id,
                    'name' => $validated['owner_name'],
                    'username' => $validated['owner_username'],
                    'email' => $validated['owner_email'],
                    'phone_number' => ($validated['owner_phone_number'] ?? null) ?: null,
                    'status' => User::STATUS_ACTIVE,
                    'created_by' => $request->user()?->id,
                    'password_change_required' => true,
                    'password' => $validated['owner_password'],
                ]);

                $owner->syncRoles(['Admin']);

                $tenant->forceFill([
                    'owner_id' => $owner->id,
                ])->save();
            }

            return $tenant;
        });

        $this->activityLogger->log(
            action: 'tenant_created',
            actor: $actor,
            target: $tenant,
            description: 'Tenant baru dibuat dari panel SaaS.',
            properties: [
                'tenant_slug' => $tenant->slug,
                'subscription_status' => $tenant->subscription_status,
                'trial_ends_at' => $tenant->trial_ends_at?->toDateTimeString(),
                'owner_requested' => $request->boolean('create_owner_account'),
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        if ($request->boolean('create_owner_account')) {
            $owner = User::query()
                ->where('tenant_id', $tenant->id)
                ->where('email', $validated['owner_email'])
                ->first();

            if ($owner) {
                $this->activityLogger->log(
                    action: 'tenant_owner_created',
                    actor: $actor,
                    target: $owner,
                    description: 'Akun owner/admin tenant dibuat saat provisioning tenant.',
                    properties: [
                        'tenant_id' => $tenant->id,
                        'tenant_name' => $tenant->name,
                        'tenant_slug' => $tenant->slug,
                        'password_change_required' => true,
                    ],
                    ipAddress: $request->ip(),
                    userAgent: $request->userAgent()
                );
            }

            $verificationSent = $owner ? $this->sendVerificationNotification->handle($owner) : false;
        }

        return redirect()
            ->route('saas.tenants.show', $tenant)
            ->with(
                'success',
                $request->boolean('create_owner_account')
                    ? (($verificationSent ?? false)
                        ? 'Tenant baru berhasil dibuat, masa trial aktif, dan akun admin tenant sudah disiapkan.'
                        : 'Tenant baru berhasil dibuat dan akun admin tenant sudah disiapkan. Password awal tetap bisa dipakai, tetapi email verifikasi belum berhasil dikirim saat ini.')
                    : 'Tenant baru berhasil dibuat dan masa trial sudah diaktifkan.'
            );
    }

    /**
     * Display the selected tenant detail page.
     */
    public function show(Request $request, Tenant $tenant): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $tenant->load(['owner', 'users.roles']);
        $tenant->loadCount(['users', 'santris', 'activityLogs']);

        return view('modules.saas.tenants.show', [
            'tenant' => $tenant,
            'accessSummary' => [
                'has_access' => $tenant->hasAccess(),
                'access_label' => $tenant->hasAccess() ? 'Akses Aktif' : 'Akses Diblokir',
                'access_reason' => $tenant->onTrial()
                    ? 'Tenant masih dalam masa trial.'
                    : ($tenant->hasPaidSubscription()
                        ? 'Tenant memiliki subscription aktif.'
                        : ($tenant->onGracePeriod()
                            ? 'Tenant sedang berada di masa grace period.'
                            : 'Tenant perlu pembayaran atau aktivasi ulang untuk mengakses aplikasi.')),
            ],
            'recentUsers' => $tenant->users()
                ->with('roles')
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }

    /**
     * Update the subscription state for the selected tenant.
     */
    public function updateSubscription(
        UpdateTenantSubscriptionRequest $request,
        Tenant $tenant,
        UpdateTenantSubscriptionAction $updateTenantSubscription
    ): RedirectResponse
    {
        $validated = $request->validated();
        $previousSnapshot = $tenant->only([
            'subscription_plan',
            'subscription_status',
            'trial_ends_at',
            'subscription_starts_at',
            'subscription_ends_at',
            'grace_ends_at',
        ]);
        $result = $updateTenantSubscription->handle($tenant, $validated, $request->user());
        $tenant->refresh();

        $this->activityLogger->log(
            action: 'subscription_updated',
            actor: $request->user(),
            target: $tenant,
            description: 'Status subscription tenant diperbarui dari panel SaaS.',
            properties: [
                'action' => $validated['action'],
                'admin_note' => $validated['admin_note'] ?? null,
                'before' => $previousSnapshot,
                'after' => $tenant->only([
                    'subscription_plan',
                    'subscription_status',
                    'trial_ends_at',
                    'subscription_starts_at',
                    'subscription_ends_at',
                    'grace_ends_at',
                ]),
                'history_id' => $result['history']->id,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()
            ->with('success', $result['message']);
    }
}
