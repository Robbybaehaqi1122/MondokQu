<?php

namespace App\Modules\Saas\Controllers;

use App\Actions\Saas\CreateTenantRoles;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Auth\Actions\SendEmailVerificationNotificationAction;
use App\Modules\Saas\Actions\UpdateTenantSubscriptionAction;
use App\Modules\Saas\Jobs\DeleteTenantJob;
use App\Modules\Saas\Requests\DeleteTenantRequest;
use App\Modules\Saas\Requests\StoreTenantRequest;
use App\Modules\Saas\Requests\UpdateTenantSubscriptionRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class TenantManagementController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger,
        protected SendEmailVerificationNotificationAction $sendVerificationNotification
    ) {}

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
                ->when(in_array($status, Tenant::subscriptionStatuses(), true), fn ($query) => $query->where('subscription_status', $status))
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

            app(CreateTenantRoles::class)->handle($tenant);

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

                $adminRole = Role::where('tenant_id', $tenant->id)->where('name', 'Admin')->firstOrFail();
                $owner->syncRoles([$adminRole]);

                $tenant->forceFill([
                    'owner_id' => $owner->id,
                ])->save();
            }

            $tenant->setSettings([
                'max_users' => (int) ($validated['max_users'] ?? config('saas.limits.max_users', 50)),
                'max_santri' => (int) ($validated['max_santri'] ?? config('saas.limits.max_santri', 200)),
                'max_storage_mb' => (int) ($validated['max_storage_mb'] ?? config('saas.limits.max_storage_mb', 1024)),
            ])->save();

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
     * Show the wizard form to create a new tenant.
     */
    public function create(Request $request): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $modules = [
            ['key' => 'santri', 'label' => 'SantriQu (Manajemen Santri)', 'icon' => 'ti-users'],
            ['key' => 'absensi', 'label' => 'AbsenQu (Kehadiran)', 'icon' => 'ti-calendar-check'],
            ['key' => 'tahfidz', 'label' => 'TahfidzQu (Hafalan)', 'icon' => 'ti-book'],
            ['key' => 'akademik', 'label' => 'AkademikQu (Mapel & Nilai)', 'icon' => 'ti-school'],
            ['key' => 'pelanggaran', 'label' => 'PelanggaranQu (Tata Tertib)', 'icon' => 'ti-alert-triangle'],
            ['key' => 'komunikasi', 'label' => 'KomunikasiQu (Pesan)', 'icon' => 'ti-message'],
            ['key' => 'kesehatan', 'label' => 'KesehatanQu (UKS)', 'icon' => 'ti-heartbeat'],
            ['key' => 'bendahara', 'label' => 'Bendahara (KeuanganQu)', 'icon' => 'ti-wallet'],
            ['key' => 'inventaris', 'label' => 'InventarisQu (Aset)', 'icon' => 'ti-package'],
            ['key' => 'kegiatan', 'label' => 'KegiatanQu (Ekstrakurikuler)', 'icon' => 'ti-calendar-event'],
            ['key' => 'musyrif', 'label' => 'Musyrif (Pembina)', 'icon' => 'ti-shield'],
        ];

        return view('modules.saas.tenants.create', [
            'modules' => $modules,
            'defaultLimits' => [
                'max_users' => config('saas.limits.max_users', 50),
                'max_santri' => config('saas.limits.max_santri', 200),
                'max_storage_mb' => config('saas.limits.max_storage_mb', 1024),
            ],
            'categories' => ['Pesantren', 'Madrasah', 'Tahfidz'],
            'defaultActiveModules' => ['santri', 'absensi', 'tahfidz', 'akademik', 'pelanggaran', 'komunikasi', 'kesehatan', 'inventaris', 'kegiatan'],
        ]);
    }

    /**
     * Store a tenant from the wizard submission.
     */
    public function wizardStore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $validated = $request->validate([
            // Step 1 - Data Pondok
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique(Tenant::class, 'slug')],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone_number' => ['nullable', 'string', 'max:30'],
            'category' => ['nullable', 'string', 'in:Pesantren,Madrasah,Tahfidz'],
            'address' => ['nullable', 'string', 'max:500'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg', 'max:2048'],
            'max_users' => ['nullable', 'integer', 'min:1'],
            'max_santri' => ['nullable', 'integer', 'min:1'],
            'max_storage_mb' => ['nullable', 'integer', 'min:1'],
            // Step 2 - Admin Tenant
            'owner_name' => ['nullable', 'string', 'max:255', 'required_with:owner_email'],
            'owner_username' => ['nullable', 'string', 'max:255', 'required_with:owner_email', Rule::unique(User::class, 'username')],
            'owner_email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'owner_phone_number' => ['nullable', 'string', 'max:30'],
            'owner_password' => ['nullable', Password::min(8), 'required_with:owner_email', 'confirmed'],
            // Step 3 - Modul Aktif
            'active_modules' => ['nullable', 'array'],
            'active_modules.*' => ['string', 'in:santri,absensi,tahfidz,akademik,pelanggaran,komunikasi,kesehatan,bendahara,inventaris,kegiatan,musyrif'],
        ], [
            'name.required' => 'Nama pondok wajib diisi.',
            'slug.alpha_dash' => 'Slug hanya boleh berisi huruf, angka, tanda hubung, atau underscore.',
            'slug.unique' => 'Slug sudah dipakai.',
            'contact_email.email' => 'Email kontak harus valid.',
            'category.in' => 'Kategori pondok tidak valid.',
            'logo.image' => 'Logo harus berupa gambar.',
            'logo.mimes' => 'Logo harus berformat PNG, JPG, JPEG, atau SVG.',
            'logo.max' => 'Logo maksimal 2 MB.',
            'owner_name.required_with' => 'Nama admin wajib diisi jika mengisi email admin.',
            'owner_username.required_with' => 'Username admin wajib diisi jika mengisi email admin.',
            'owner_username.unique' => 'Username admin sudah digunakan.',
            'owner_email.email' => 'Email admin harus valid.',
            'owner_email.unique' => 'Email admin sudah digunakan.',
            'owner_password.required_with' => 'Password admin wajib diisi jika mengisi email admin.',
            'owner_password.confirmed' => 'Konfirmasi password tidak cocok.',
            'active_modules.*.in' => 'Modul yang dipilih tidak valid.',
        ]);

        $actor = $request->user();
        $createOwner = filled($validated['owner_email'] ?? null);

        $tenant = DB::transaction(function () use ($validated, $request, $createOwner): Tenant {
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

            app(CreateTenantRoles::class)->handle($tenant);

            $settings = [
                'max_users' => (int) ($validated['max_users'] ?? config('saas.limits.max_users', 50)),
                'max_santri' => (int) ($validated['max_santri'] ?? config('saas.limits.max_santri', 200)),
                'max_storage_mb' => (int) ($validated['max_storage_mb'] ?? config('saas.limits.max_storage_mb', 1024)),
                'category' => $validated['category'] ?? '',
                'address' => $validated['address'] ?? '',
                'active_modules' => $validated['active_modules'] ?? [],
            ];

            if ($request->hasFile('logo')) {
                $settings['logo_path'] = $request->file('logo')->store('tenant-branding/'.$tenant->id, 'public');
            }

            $tenant->setSettings($settings)->save();

            if ($createOwner) {
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

                $adminRole = Role::where('tenant_id', $tenant->id)->where('name', 'Admin')->firstOrFail();
                $owner->syncRoles([$adminRole]);

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
            description: 'Tenant baru dibuat melalui wizard 3 langkah.',
            properties: [
                'tenant_slug' => $tenant->slug,
                'subscription_status' => $tenant->subscription_status,
                'trial_ends_at' => $tenant->trial_ends_at?->toDateTimeString(),
                'category' => $validated['category'] ?? '',
                'owner_created' => $createOwner,
                'active_modules' => $validated['active_modules'] ?? [],
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        if ($createOwner) {
            $owner = User::query()
                ->where('tenant_id', $tenant->id)
                ->where('email', $validated['owner_email'])
                ->first();

            if ($owner) {
                $this->activityLogger->log(
                    action: 'tenant_owner_created',
                    actor: $actor,
                    target: $owner,
                    description: 'Akun owner/admin tenant dibuat melalui wizard.',
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
                $createOwner
                    ? (($verificationSent ?? false)
                        ? 'Tenant baru berhasil dibuat melalui wizard. Akun admin tenant sudah disiapkan.'
                        : 'Tenant baru berhasil dibuat melalui wizard. Akun admin tenant sudah disiapkan, namun email verifikasi belum berhasil dikirim.')
                    : 'Tenant baru berhasil dibuat melalui wizard.'
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
                'access_reason' => $tenant->isDeleting()
                    ? 'Tenant sedang masuk antrean penghapusan permanen.'
                    : ($tenant->onTrial()
                    ? 'Tenant masih dalam masa trial.'
                    : ($tenant->hasPaidSubscription()
                        ? 'Tenant memiliki subscription aktif.'
                        : ($tenant->onGracePeriod()
                            ? 'Tenant sedang berada di masa grace period.'
                            : 'Tenant perlu pembayaran atau aktivasi ulang untuk mengakses aplikasi.'))),
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
    ): RedirectResponse {
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

    public function updateCapacity(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $validated = $request->validate([
            'max_users' => ['required', 'integer', 'min:1'],
            'max_santri' => ['required', 'integer', 'min:1'],
            'max_storage_mb' => ['required', 'integer', 'min:1'],
        ]);

        $tenant->setSettings([
            'max_users' => (int) $validated['max_users'],
            'max_santri' => (int) $validated['max_santri'],
            'max_storage_mb' => (int) $validated['max_storage_mb'],
        ])->save();

        $this->activityLogger->log(
            action: 'tenant_capacity_updated',
            actor: $request->user(),
            target: $tenant,
            description: 'Kapasitas tenant diperbarui.',
            properties: [
                'max_users' => $validated['max_users'],
                'max_santri' => $validated['max_santri'],
                'max_storage_mb' => $validated['max_storage_mb'],
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()
            ->with('success', 'Kapasitas tenant berhasil diperbarui.');
    }

    /**
     * Queue a tenant and its tenant-owned operational data for permanent deletion.
     */
    public function destroy(
        DeleteTenantRequest $request,
        Tenant $tenant
    ): RedirectResponse {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $actor = $request->user();
        $deleteReason = $request->validated('delete_reason');
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();

        $result = DB::transaction(function () use ($actor, $deleteReason, $ipAddress, $tenant, $userAgent): array {
            $lockedTenant = Tenant::query()
                ->lockForUpdate()
                ->findOrFail($tenant->id);

            if ($lockedTenant->isDeleting()) {
                return [
                    'queued' => false,
                    'message' => 'Penghapusan tenant sudah masuk antrean dan sedang diproses.',
                ];
            }

            if ($actor && $lockedTenant->users()->whereKey($actor->id)->exists()) {
                return [
                    'queued' => false,
                    'message' => 'Tenant ini memuat akun yang sedang Anda gunakan, sehingga tidak bisa dihapus permanen.',
                ];
            }

            $previousStatus = $lockedTenant->subscription_status;

            $lockedTenant->forceFill([
                'subscription_status' => Tenant::SUBSCRIPTION_DELETING,
            ])->save();

            $this->activityLogger->log(
                action: 'tenant_delete_requested',
                actor: $actor,
                target: $lockedTenant,
                description: 'Tenant ditandai untuk penghapusan permanen melalui background queue.',
                properties: [
                    'tenant_id' => $lockedTenant->id,
                    'tenant_name' => $lockedTenant->name,
                    'tenant_slug' => $lockedTenant->slug,
                    'previous_subscription_status' => $previousStatus,
                    'delete_reason' => $deleteReason,
                ],
                ipAddress: $ipAddress,
                userAgent: $userAgent
            );

            return [
                'queued' => true,
                'tenant_id' => $lockedTenant->id,
                'actor_id' => $actor?->id,
                'delete_reason' => $deleteReason,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'message' => 'Tenant '.$lockedTenant->name.' ditandai sedang dihapus. Proses penghapusan berjalan di background queue.',
            ];
        });

        if (! $result['queued']) {
            return back()
                ->with('error', $result['message']);
        }

        DeleteTenantJob::dispatch(
            $result['tenant_id'],
            $result['actor_id'],
            $result['delete_reason'],
            $result['ip_address'],
            $result['user_agent']
        );

        return redirect()
            ->route('saas.tenants.index')
            ->with('success', $result['message']);
    }
}
