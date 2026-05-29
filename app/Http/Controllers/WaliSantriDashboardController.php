<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\SantriInvoice;
use App\Models\SantriPayment;
use App\Models\SantriPaymentConfirmation;
use App\Models\User;
use App\Http\Requests\WaliSantri\StorePaymentConfirmationRequest;
use App\Notifications\WaliPaymentProofSubmittedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
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
        $attendanceBaseQuery = AttendanceRecord::query()
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
                    'outstanding_amount' => (int) ($invoiceStats?->outstanding_amount ?? 0),
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
            'recentAttendanceRecords' => $this->recentAttendanceRecords(clone $attendanceBaseQuery),
            'attendanceSummary' => $this->buildAttendanceSummary(clone $attendanceBaseQuery),
            'leaveSummary' => $this->buildLeaveSummary(clone $leaveRequestBaseQuery),
            'paymentMethods' => SantriPayment::paymentMethods(),
            'pendingPaymentConfirmationsByInvoice' => $this->pendingPaymentConfirmationsByInvoice(
                $currentUser,
                $santriIds
            ),
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
     * Build attendance summary for the last 30 days.
     */
    protected function buildAttendanceSummary($attendanceBaseQuery): array
    {
        $dateFrom = now()->subDays(29)->toDateString();
        $dateTo = now()->toDateString();
        $counts = (clone $attendanceBaseQuery)
            ->join('attendance_sessions', function ($join): void {
                $join
                    ->on('attendance_records.attendance_session_id', '=', 'attendance_sessions.id')
                    ->on('attendance_records.tenant_id', '=', 'attendance_sessions.tenant_id');
            })
            ->whereDate('attendance_sessions.session_date', '>=', $dateFrom)
            ->whereDate('attendance_sessions.session_date', '<=', $dateTo)
            ->select('attendance_records.status', DB::raw('COUNT(*) as total'))
            ->groupBy('attendance_records.status')
            ->pluck('total', 'attendance_records.status');

        return [
            'total' => (int) $counts->sum(),
            AttendanceRecord::STATUS_PRESENT => (int) $counts->get(AttendanceRecord::STATUS_PRESENT, 0),
            AttendanceRecord::STATUS_PERMISSION => (int) $counts->get(AttendanceRecord::STATUS_PERMISSION, 0),
            AttendanceRecord::STATUS_SICK => (int) $counts->get(AttendanceRecord::STATUS_SICK, 0),
            AttendanceRecord::STATUS_ABSENT => (int) $counts->get(AttendanceRecord::STATUS_ABSENT, 0),
            AttendanceRecord::STATUS_LATE => (int) $counts->get(AttendanceRecord::STATUS_LATE, 0),
        ];
    }

    /**
     * Get recent attendance records for linked santri.
     */
    protected function recentAttendanceRecords($attendanceBaseQuery)
    {
        return $attendanceBaseQuery
            ->select('attendance_records.*')
            ->join('attendance_sessions', function ($join): void {
                $join
                    ->on('attendance_records.attendance_session_id', '=', 'attendance_sessions.id')
                    ->on('attendance_records.tenant_id', '=', 'attendance_sessions.tenant_id');
            })
            ->with(['santri.room', 'session.activity'])
            ->orderByDesc('attendance_sessions.session_date')
            ->orderByDesc('attendance_records.recorded_at')
            ->orderByDesc('attendance_records.id')
            ->limit(10)
            ->get();
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
            'paymentConfirmations' => $invoice->paymentConfirmations,
            'paymentMethods' => SantriPayment::paymentMethods(),
        ]);
    }

    /**
     * Store a wali-submitted manual transfer proof for later admin verification.
     */
    public function storePaymentConfirmation(
        StorePaymentConfirmationRequest $request,
        SantriInvoice $invoice
    ): RedirectResponse {
        $invoice = $this->resolveLinkedInvoice($request, $invoice);

        $validated = $request->validated();

        if ($invoice->status === SantriInvoice::STATUS_PAID || $invoice->outstandingAmount() <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Tagihan ini sudah lunas dan tidak memerlukan bukti bayar baru.',
            ])->errorBag('paymentConfirmation');
        }

        $amount = (int) $validated['amount'];

        if ($amount > $invoice->outstandingAmount()) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal bukti bayar tidak boleh melebihi sisa tagihan.',
            ])->errorBag('paymentConfirmation');
        }

        $proofPath = $request->hasFile('proof')
            ? $request->file('proof')->store(
                'wali-payment-proofs/'.$invoice->tenant_id,
                'public'
            )
            : null;

        $confirmation = SantriPaymentConfirmation::query()->create([
            'tenant_id' => $invoice->tenant_id,
            'santri_invoice_id' => $invoice->id,
            'santri_id' => $invoice->santri_id,
            'submitted_by' => $request->user()?->id,
            'amount' => $amount,
            'paid_at' => $validated['paid_at'],
            'payment_method' => $validated['payment_method'],
            'reference_number' => $validated['reference_number'] ?? null,
            'proof_path' => $proofPath,
            'note' => $validated['note'] ?? null,
            'status' => SantriPaymentConfirmation::STATUS_PENDING,
        ]);

        $confirmation->load(['invoice', 'santri']);
        $this->notifyPaymentProofReviewers($confirmation);

        return back()
            ->with('success', 'Konfirmasi pembayaran berhasil dikirim dan menunggu verifikasi admin pondok.');
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
     * Get pending payment confirmations keyed by invoice for the current wali user.
     */
    protected function pendingPaymentConfirmationsByInvoice(?User $currentUser, $santriIds)
    {
        if (! $currentUser || $santriIds->isEmpty()) {
            return collect();
        }

        return SantriPaymentConfirmation::query()
            ->visibleTo($currentUser)
            ->where('submitted_by', $currentUser->id)
            ->whereIn('santri_id', $santriIds)
            ->pending()
            ->latest()
            ->get()
            ->groupBy('santri_invoice_id');
    }

    /**
     * Notify tenant finance operators that a wali proof needs verification.
     */
    protected function notifyPaymentProofReviewers(SantriPaymentConfirmation $confirmation): void
    {
        $reviewers = User::query()
            ->where('tenant_id', $confirmation->tenant_id)
            ->where('status', User::STATUS_ACTIVE)
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['Admin', 'Bendahara']))
            ->get();

        if ($reviewers->isEmpty()) {
            return;
        }

        Notification::send($reviewers, new WaliPaymentProofSubmittedNotification($confirmation));
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
                    'outstanding_amount' => (int) ((clone $outstandingQuery)
                ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as total')
                ->value('total') ?? 0),
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
                'paymentConfirmations' => fn ($query) => $query
                    ->where('submitted_by', $currentUser->id)
                    ->latest(),
            ])
            ->whereKey($invoice->id)
            ->whereIn('santri_id', $santriIds)
            ->firstOrFail();
    }
}
