<?php

namespace App\Modules\Saas\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantBillingNote;
use App\Modules\Saas\Requests\StoreBillingNoteRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingNoteController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {
    }

    /**
     * Display the billing notes page for SaaS operators.
     */
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $search = trim((string) $request->string('search'));
        $paymentMethod = $request->string('payment_method')->toString();
        $tenantId = $request->integer('tenant_id');

        return view('modules.saas.billing-notes.index', [
            'billingNotes' => TenantBillingNote::query()
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
                ->latest('paid_at')
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'tenants' => Tenant::query()
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
            'filters' => [
                'search' => $search,
                'payment_method' => $paymentMethod,
                'tenant_id' => $tenantId > 0 ? $tenantId : null,
            ],
        ]);
    }

    /**
     * Store a new manual billing note for a tenant.
     */
    public function store(StoreBillingNoteRequest $request): RedirectResponse
    {
        $validated = $request->validated();

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

        $billingNote->load('tenant');

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
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('saas.billing-notes.index')
            ->with('success', 'Billing note tenant berhasil disimpan.');
    }
}
