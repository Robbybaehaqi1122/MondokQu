<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\SantriInvoice;
use App\Models\SantriPayment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaliSantriDashboardController extends Controller
{
    /**
     * Display the wali santri portal dashboard.
     */
    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $children = $currentUser
            ->guardianSantris()
            ->with('room')
            ->orderBy('full_name')
            ->get();
        $santriIds = $children->pluck('id');

        $invoiceBaseQuery = SantriInvoice::query()
            ->visibleTo($currentUser)
            ->whereIn('santri_id', $santriIds);
        $paymentBaseQuery = SantriPayment::query()
            ->visibleTo($currentUser)
            ->whereIn('santri_id', $santriIds);
        $leaveRequestBaseQuery = LeaveRequest::query()
            ->visibleTo($currentUser)
            ->whereIn('santri_id', $santriIds);

        $invoiceStatsBySantri = (clone $invoiceBaseQuery)
            ->where('status', '!=', SantriInvoice::STATUS_PAID)
            ->selectRaw('santri_id, COUNT(*) as outstanding_invoices, COALESCE(SUM(amount - paid_amount), 0) as outstanding_amount')
            ->groupBy('santri_id')
            ->get()
            ->keyBy('santri_id');

        $lastPaymentsBySantri = (clone $paymentBaseQuery)
            ->latest('paid_at')
            ->limit(50)
            ->get()
            ->unique('santri_id')
            ->keyBy('santri_id');

        return view('wali-santri.dashboard', [
            'childSummaries' => $children->map(function ($santri) use ($invoiceStatsBySantri, $lastPaymentsBySantri) {
                $invoiceStats = $invoiceStatsBySantri->get($santri->id);
                $lastPayment = $lastPaymentsBySantri->get($santri->id);

                return [
                    'santri' => $santri,
                    'relationship' => $santri->pivot?->relationship ?: 'Wali',
                    'outstanding_invoices' => (int) ($invoiceStats?->outstanding_invoices ?? 0),
                    'outstanding_amount' => (float) ($invoiceStats?->outstanding_amount ?? 0),
                    'last_payment' => $lastPayment,
                ];
            }),
            'recentPayments' => (clone $paymentBaseQuery)
                ->with(['invoice', 'santri.room'])
                ->latest('paid_at')
                ->limit(6)
                ->get(),
            'recentLeaveRequests' => (clone $leaveRequestBaseQuery)
                ->with('santri.room')
                ->latest()
                ->limit(8)
                ->get(),
            'leaveSummary' => $this->buildLeaveSummary(clone $leaveRequestBaseQuery),
            'summary' => $this->buildSummary(
                clone $invoiceBaseQuery,
                clone $paymentBaseQuery,
                $children->count()
            ),
            'upcomingInvoices' => (clone $invoiceBaseQuery)
                ->with('santri.room')
                ->where('status', '!=', SantriInvoice::STATUS_PAID)
                ->orderBy('due_date')
                ->limit(8)
                ->get(),
        ]);
    }

    /**
     * Build leave request summary for all linked santri.
     */
    protected function buildLeaveSummary($leaveRequestBaseQuery): array
    {
        $today = now()->toDateString();

        return [
            'pending' => (clone $leaveRequestBaseQuery)
                ->where('status', LeaveRequest::STATUS_PENDING)
                ->count(),
            'approved' => (clone $leaveRequestBaseQuery)
                ->where('status', LeaveRequest::STATUS_APPROVED)
                ->count(),
            'rejected' => (clone $leaveRequestBaseQuery)
                ->where('status', LeaveRequest::STATUS_REJECTED)
                ->count(),
            'completed' => (clone $leaveRequestBaseQuery)
                ->where('status', LeaveRequest::STATUS_COMPLETED)
                ->count(),
            'active_today' => (clone $leaveRequestBaseQuery)
                ->where('status', LeaveRequest::STATUS_APPROVED)
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->count(),
        ];
    }

    /**
     * Display an invoice detail for a linked santri.
     */
    public function showInvoice(Request $request, SantriInvoice $invoice): View
    {
        $invoice = $this->resolveLinkedInvoice($request, $invoice);

        return view('wali-santri.invoice-show', [
            'invoice' => $invoice,
            'payments' => $invoice->payments,
        ]);
    }

    /**
     * Display a print-friendly receipt for a linked invoice.
     */
    public function printInvoice(Request $request, SantriInvoice $invoice): View
    {
        $invoice = $this->resolveLinkedInvoice($request, $invoice);

        return view('wali-santri.invoice-receipt', [
            'invoice' => $invoice,
            'payments' => $invoice->payments,
        ]);
    }

    /**
     * Build financial summary for all linked santri.
     */
    protected function buildSummary($invoiceBaseQuery, $paymentBaseQuery, int $childrenCount): array
    {
        $outstandingQuery = (clone $invoiceBaseQuery)
            ->where('status', '!=', SantriInvoice::STATUS_PAID);

        return [
            'children_count' => $childrenCount,
            'outstanding_invoices' => (clone $outstandingQuery)->count(),
            'outstanding_amount' => (clone $outstandingQuery)
                ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as total')
                ->value('total') ?? 0,
            'overdue_invoices' => (clone $outstandingQuery)
                ->whereDate('due_date', '<', now()->toDateString())
                ->count(),
            'paid_this_month' => (clone $paymentBaseQuery)
                ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount'),
        ];
    }

    /**
     * Resolve an invoice that belongs to one of the current wali user's linked santri.
     */
    protected function resolveLinkedInvoice(Request $request, SantriInvoice $invoice): SantriInvoice
    {
        $currentUser = $request->user();
        $santriIds = $currentUser
            ->guardianSantris()
            ->pluck('santris.id');

        abort_if($santriIds->isEmpty(), 404);

        return SantriInvoice::query()
            ->visibleTo($currentUser)
            ->with([
                'santri.room',
                'tenant',
                'payments' => fn ($query) => $query
                    ->with('recorder')
                    ->latest('paid_at')
                    ->latest('id'),
            ])
            ->whereKey($invoice->id)
            ->whereIn('santri_id', $santriIds)
            ->firstOrFail();
    }
}
