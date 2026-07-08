<?php

namespace App\Modules\Saas\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantBillingNote;
use App\Modules\Saas\Actions\UpdateTenantSubscriptionAction;
use App\Modules\Saas\Requests\StoreBillingNoteRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BillingNoteController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger,
        protected UpdateTenantSubscriptionAction $updateTenantSubscription
    ) {}

    /**
     * Display the billing notes page for SaaS operators.
     */
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $search = trim((string) $request->string('search'));
        $paymentMethod = $request->string('payment_method')->toString();
        $tenantId = $request->integer('tenant_id');
        $tenantStatus = $request->string('tenant_status')->toString();
        $paidFrom = $request->string('paid_from')->toString();
        $paidTo = $request->string('paid_to')->toString();
        $periodFrom = $request->string('period_from')->toString();
        $periodTo = $request->string('period_to')->toString();

        $billingQuery = TenantBillingNote::query()
            ->with(['tenant', 'recordedByUser'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($billingQuery) use ($search) {
                    $billingQuery
                        ->where('admin_note', 'like', "%{$search}%")
                        ->orWhere('payment_method', 'like', "%{$search}%")
                        ->orWhereHas('tenant', function ($tenantQuery) use ($search) {
                            $tenantQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('slug', 'like', "%{$search}%");
                        })
                        ->orWhereHas('recordedByUser', function ($userQuery) use ($search) {
                            $userQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%");
                        });
                });
            })
            ->when(in_array($paymentMethod, ['transfer bank', 'cash', 'e-wallet', 'qris', 'lainnya'], true), fn ($query) => $query->where('payment_method', $paymentMethod))
            ->when($tenantId > 0, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when(in_array($tenantStatus, Tenant::subscriptionStatuses(), true), fn ($query) => $query->whereHas('tenant', function ($tenantQuery) use ($tenantStatus) {
                $tenantQuery->where('subscription_status', $tenantStatus);
            }))
            ->when($paidFrom !== '', fn ($query) => $query->whereDate('paid_at', '>=', $paidFrom))
            ->when($paidTo !== '', fn ($query) => $query->whereDate('paid_at', '<=', $paidTo))
            ->when($periodFrom !== '', fn ($query) => $query->whereDate('period_starts_at', '>=', $periodFrom))
            ->when($periodTo !== '', fn ($query) => $query->whereDate('period_ends_at', '<=', $periodTo));

        $billingNotes = (clone $billingQuery)
            ->latest('paid_at')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statusCounts = (clone $billingQuery)
            ->join('tenants', 'tenant_billing_notes.tenant_id', '=', 'tenants.id')
            ->selectRaw('tenants.subscription_status, count(*) as count')
            ->groupBy('tenants.subscription_status')
            ->pluck('count', 'subscription_status')
            ->toArray();

        return view('modules.saas.billing-notes.index', [
            'billingNotes' => $billingNotes,
            'tenants' => Tenant::query()
                ->where('subscription_status', '!=', Tenant::SUBSCRIPTION_DELETING)
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
            'summary' => [
                'total_notes' => (clone $billingQuery)->count(),
                'total_amount' => (clone $billingQuery)->sum('amount'),
                'status_counts' => [
                    Tenant::SUBSCRIPTION_ACTIVE => $statusCounts[Tenant::SUBSCRIPTION_ACTIVE] ?? 0,
                    Tenant::SUBSCRIPTION_TRIAL => $statusCounts[Tenant::SUBSCRIPTION_TRIAL] ?? 0,
                    Tenant::SUBSCRIPTION_GRACE => $statusCounts[Tenant::SUBSCRIPTION_GRACE] ?? 0,
                    Tenant::SUBSCRIPTION_EXPIRED => $statusCounts[Tenant::SUBSCRIPTION_EXPIRED] ?? 0,
                    Tenant::SUBSCRIPTION_DELETING => $statusCounts[Tenant::SUBSCRIPTION_DELETING] ?? 0,
                ],
            ],
            'filters' => [
                'search' => $search,
                'payment_method' => $paymentMethod,
                'tenant_id' => $tenantId > 0 ? $tenantId : null,
                'tenant_status' => $tenantStatus,
                'paid_from' => $paidFrom,
                'paid_to' => $paidTo,
                'period_from' => $periodFrom,
                'period_to' => $periodTo,
            ],
        ]);
    }

    /**
     * Store a new manual billing note for a tenant.
     */
    public function store(StoreBillingNoteRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $subscriptionResult = null;
        $previousSubscriptionSnapshot = null;

        $billingNote = DB::transaction(function () use (&$previousSubscriptionSnapshot, &$subscriptionResult, $request, $validated): TenantBillingNote {
            $billingNote = TenantBillingNote::query()->create([
                'tenant_id' => $validated['tenant_id'],
                'paid_at' => $validated['paid_at'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'period_starts_at' => $validated['period_starts_at'],
                'period_ends_at' => $validated['period_ends_at'],
                'admin_note' => $validated['admin_note'] ?? null,
                'recorded_by' => $request->user()?->id,
            ]);

            $billingNote->loadMissing('tenant');

            if ($request->boolean('apply_subscription') && $billingNote->tenant) {
                $previousSubscriptionSnapshot = $billingNote->tenant->only([
                    'subscription_plan',
                    'subscription_status',
                    'trial_ends_at',
                    'subscription_starts_at',
                    'subscription_ends_at',
                    'grace_ends_at',
                ]);

                $subscriptionResult = $this->updateTenantSubscription->handle($billingNote->tenant, [
                    'action' => 'activate_subscription',
                    'subscription_starts_at' => $validated['period_starts_at'],
                    'subscription_ends_at' => $validated['period_ends_at'],
                    'admin_note' => $this->buildSubscriptionHistoryNote($billingNote),
                ], $request->user());

                $billingNote->tenant->refresh();
            }

            return $billingNote;
        });

        $this->activityLogger->log(
            action: 'billing_note_created',
            actor: $request->user(),
            target: $billingNote->tenant,
            description: 'Billing note tenant dicatat dari panel SaaS.',
            properties: [
                'billing_note_id' => $billingNote->id,
                'amount' => (string) $billingNote->amount,
                'payment_method' => $billingNote->payment_method,
                'period_starts_at' => $billingNote->period_starts_at?->toDateString(),
                'period_ends_at' => $billingNote->period_ends_at?->toDateString(),
                'admin_note' => $billingNote->admin_note,
                'subscription_applied' => $subscriptionResult !== null,
                'subscription_history_id' => $subscriptionResult['history']->id ?? null,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        if ($subscriptionResult !== null && $billingNote->tenant) {
            $this->activityLogger->log(
                action: 'subscription_updated',
                actor: $request->user(),
                target: $billingNote->tenant,
                description: 'Status subscription tenant diperbarui dari billing note.',
                properties: [
                    'action' => 'activate_subscription',
                    'source' => 'billing_note',
                    'billing_note_id' => $billingNote->id,
                    'before' => $previousSubscriptionSnapshot,
                    'after' => $billingNote->tenant->only([
                        'subscription_plan',
                        'subscription_status',
                        'trial_ends_at',
                        'subscription_starts_at',
                        'subscription_ends_at',
                        'grace_ends_at',
                    ]),
                    'history_id' => $subscriptionResult['history']->id,
                ],
                ipAddress: $request->ip(),
                userAgent: $request->userAgent()
            );
        }

        return redirect()
            ->route('saas.billing-notes.index')
            ->with(
                'success',
                $subscriptionResult !== null
                    ? 'Billing note berhasil disimpan dan subscription tenant sudah diperbarui.'
                    : 'Billing note tenant berhasil disimpan.'
            );
    }

    /**
     * Build a clear history note when payment is also used to activate subscription.
     */
    protected function buildSubscriptionHistoryNote(TenantBillingNote $billingNote): string
    {
        if (filled($billingNote->admin_note)) {
            return 'Diperbarui dari billing note #'.$billingNote->id.': '.$billingNote->admin_note;
        }

        return 'Diperbarui dari billing note #'.$billingNote->id.'.';
    }
}
