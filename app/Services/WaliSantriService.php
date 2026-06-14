<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\SantriInvoice;
use App\Models\SantriPaymentConfirmation;
use App\Models\User;
use App\Notifications\WaliPaymentProofSubmittedNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class WaliSantriService
{
    public function buildSummary($invoiceBaseQuery, $paymentBaseQuery, int $childrenCount): array
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

    public function buildAttendanceSummary($attendanceBaseQuery): array
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

    public function recentAttendanceRecords($attendanceBaseQuery)
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

    public function buildLeaveSummary($leaveRequestBaseQuery): array
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

    public function pendingPaymentConfirmationsByInvoice(?User $currentUser, $santriIds): Collection
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

    public function storePaymentConfirmation(
        SantriInvoice $invoice,
        array $validated,
        User $user,
        ?string $proofPath,
    ): SantriPaymentConfirmation {
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

        $confirmation = SantriPaymentConfirmation::query()->create([
            'tenant_id' => $invoice->tenant_id,
            'santri_invoice_id' => $invoice->id,
            'santri_id' => $invoice->santri_id,
            'submitted_by' => $user->id,
            'amount' => $amount,
            'paid_at' => $validated['paid_at'],
            'payment_method' => $validated['payment_method'],
            'reference_number' => $validated['reference_number'] ?? null,
            'proof_path' => $proofPath,
            'note' => $validated['note'] ?? null,
            'status' => SantriPaymentConfirmation::STATUS_PENDING,
        ]);

        $this->notifyPaymentProofReviewers($confirmation);

        return $confirmation;
    }

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
}
