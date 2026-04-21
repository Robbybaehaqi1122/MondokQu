<?php

namespace App\Modules\Saas\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantSubscriptionHistory;
use App\Models\User;
use App\Modules\Saas\Requests\StoreTenantRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Throwable;

class TenantManagementController extends Controller
{
    /**
     * Display the tenant management page.
     */
    public function index(): View
    {
        return view('modules.saas.tenants.index', [
            'tenants' => Tenant::query()
                ->with(['owner'])
                ->withCount(['users', 'santris'])
                ->orderBy('name')
                ->paginate(10),
        ]);
    }

    /**
     * Store a newly created tenant with an initial trial window.
     */
    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $validated = $request->validated();

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
                    'password_change_required' => false,
                    'password' => $validated['owner_password'],
                ]);

                $owner->syncRoles(['Admin']);

                $tenant->forceFill([
                    'owner_id' => $owner->id,
                ])->save();
            }

            return $tenant;
        });

        if ($request->boolean('create_owner_account')) {
            $owner = User::query()
                ->where('tenant_id', $tenant->id)
                ->where('email', $validated['owner_email'])
                ->first();

            $verificationSent = $owner ? $this->sendVerificationNotificationSafely($owner) : false;
        }

        return redirect()
            ->route('saas.tenants.show', $tenant)
            ->with(
                'success',
                $request->boolean('create_owner_account')
                    ? (($verificationSent ?? false)
                        ? 'Tenant baru berhasil dibuat, masa trial aktif, dan akun admin tenant sudah disiapkan.'
                        : 'Tenant baru berhasil dibuat dan akun admin tenant sudah disiapkan, tetapi email verifikasinya belum bisa dikirim.')
                    : 'Tenant baru berhasil dibuat dan masa trial sudah diaktifkan.'
            );
    }

    /**
     * Display the selected tenant detail page.
     */
    public function show(Tenant $tenant): View
    {
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
    public function updateSubscription(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'action' => ['required', 'string', 'in:activate_trial,extend_trial,activate_subscription,mark_grace,mark_expired'],
            'trial_ends_at' => ['nullable', 'date', 'after:now'],
            'grace_ends_at' => ['nullable', 'date', 'after:now'],
            'subscription_duration' => ['nullable', 'string', 'in:1_month,3_months,6_months,12_months'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ], [
            'action.required' => 'Aksi subscription wajib dipilih.',
            'action.in' => 'Aksi subscription yang dipilih tidak valid.',
            'trial_ends_at.date' => 'Tanggal akhir trial harus berupa tanggal yang valid.',
            'trial_ends_at.after' => 'Tanggal akhir trial harus lebih besar dari waktu sekarang.',
            'grace_ends_at.date' => 'Tanggal akhir grace period harus berupa tanggal yang valid.',
            'grace_ends_at.after' => 'Tanggal akhir grace period harus lebih besar dari waktu sekarang.',
            'subscription_duration.in' => 'Durasi subscription yang dipilih tidak valid.',
            'admin_note.max' => 'Catatan admin maksimal 1000 karakter.',
        ])->validateWithBag('subscriptionControl');

        $action = $validated['action'];

        if (in_array($action, ['activate_trial', 'extend_trial'], true) && empty($validated['trial_ends_at'])) {
            return back()
                ->withInput()
                ->withErrors([
                    'trial_ends_at' => 'Silakan pilih tanggal akhir trial terlebih dahulu.',
                ], 'subscriptionControl');
        }

        if ($action === 'mark_grace' && empty($validated['grace_ends_at'])) {
            return back()
                ->withInput()
                ->withErrors([
                    'grace_ends_at' => 'Silakan pilih tanggal akhir grace period terlebih dahulu.',
                ], 'subscriptionControl');
        }

        if ($action === 'activate_subscription' && empty($validated['subscription_duration'])) {
            return back()
                ->withInput()
                ->withErrors([
                    'subscription_duration' => 'Silakan pilih durasi subscription terlebih dahulu.',
                ], 'subscriptionControl');
        }

        $actionedAt = now();
        $trialEndsAt = ! empty($validated['trial_ends_at']) ? Carbon::parse($validated['trial_ends_at']) : null;
        $graceEndsAt = ! empty($validated['grace_ends_at']) ? Carbon::parse($validated['grace_ends_at']) : null;
        $subscriptionEndAt = match ($validated['subscription_duration'] ?? null) {
            '1_month' => $actionedAt->copy()->addMonth(),
            '3_months' => $actionedAt->copy()->addMonths(3),
            '6_months' => $actionedAt->copy()->addMonths(6),
            '12_months' => $actionedAt->copy()->addYear(),
            default => null,
        };

        [$periodStartsAt, $periodEndsAt] = DB::transaction(function () use ($action, $actionedAt, $graceEndsAt, $request, $subscriptionEndAt, $tenant, $trialEndsAt, $validated): array {
            $periodStartsAt = null;
            $periodEndsAt = null;

            match ($action) {
                'activate_trial' => $tenant->forceFill([
                    'subscription_plan' => config('saas.default_plan', 'trial'),
                    'subscription_status' => Tenant::SUBSCRIPTION_TRIAL,
                    'trial_ends_at' => $trialEndsAt,
                    'subscription_starts_at' => null,
                    'subscription_ends_at' => null,
                    'grace_ends_at' => null,
                ])->save(),
                'extend_trial' => $tenant->forceFill([
                    'subscription_plan' => config('saas.default_plan', 'trial'),
                    'subscription_status' => Tenant::SUBSCRIPTION_TRIAL,
                    'trial_ends_at' => $trialEndsAt,
                    'subscription_starts_at' => null,
                    'subscription_ends_at' => null,
                    'grace_ends_at' => null,
                ])->save(),
                'activate_subscription' => $tenant->forceFill([
                    'subscription_plan' => 'basic',
                    'subscription_status' => Tenant::SUBSCRIPTION_ACTIVE,
                    'subscription_starts_at' => $actionedAt,
                    'subscription_ends_at' => $subscriptionEndAt,
                    'grace_ends_at' => null,
                ])->save(),
                'mark_grace' => $tenant->forceFill([
                    'subscription_plan' => $tenant->subscription_plan ?: 'basic',
                    'subscription_status' => Tenant::SUBSCRIPTION_GRACE,
                    'subscription_ends_at' => $tenant->subscription_ends_at ?: $actionedAt,
                    'grace_ends_at' => $graceEndsAt,
                ])->save(),
                'mark_expired' => $tenant->forceFill([
                    'subscription_plan' => $tenant->subscription_plan ?: 'basic',
                    'subscription_status' => Tenant::SUBSCRIPTION_EXPIRED,
                    'grace_ends_at' => $actionedAt->copy()->subSecond(),
                ])->save(),
            };

            [$periodStartsAt, $periodEndsAt] = match ($action) {
                'activate_trial', 'extend_trial' => [$actionedAt, $trialEndsAt],
                'activate_subscription' => [$actionedAt, $subscriptionEndAt],
                'mark_grace' => [$actionedAt, $graceEndsAt],
                'mark_expired' => [$actionedAt, $actionedAt],
            };

            TenantSubscriptionHistory::query()->create([
                'tenant_id' => $tenant->id,
                'action' => $action,
                'period_starts_at' => $periodStartsAt,
                'period_ends_at' => $periodEndsAt,
                'admin_note' => filled($validated['admin_note'] ?? null) ? $validated['admin_note'] : null,
                'changed_by' => $request->user()?->id,
            ]);

            return [$periodStartsAt, $periodEndsAt];
        });

        $message = match ($action) {
            'activate_trial' => 'Trial tenant berhasil diaktifkan dengan tanggal akhir yang dipilih.',
            'extend_trial' => 'Masa trial tenant berhasil diatur ulang sesuai tanggal yang dipilih.',
            'activate_subscription' => 'Subscription tenant berhasil diaktifkan sesuai durasi yang dipilih.',
            'mark_grace' => 'Tenant berhasil dipindahkan ke masa grace period sesuai tanggal yang dipilih.',
            'mark_expired' => 'Tenant berhasil ditandai sebagai expired.',
        };

        return back()
            ->with('success', $message);
    }

    /**
     * Attempt to send the verification email without aborting tenant provisioning.
     */
    protected function sendVerificationNotificationSafely(User $user): bool
    {
        try {
            $user->sendEmailVerificationNotification();

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
