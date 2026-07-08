<?php

namespace App\Modules\WaliSantri\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\NilaiSantri;
use App\Models\Pelanggaran;
use App\Models\Santri;
use App\Models\SantriInvoice;
use App\Models\SantriPayment;
use App\Models\TahfidzRecord;
use App\Models\TahfidzSession;
use App\Modules\WaliSantri\Requests\StorePaymentConfirmationRequest;
use App\Services\WaliSantriService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaliSantriDashboardController extends Controller
{
    public function __construct(
        protected WaliSantriService $waliSantriService,
    ) {}

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
            'recentAttendanceRecords' => $this->waliSantriService->recentAttendanceRecords(clone $attendanceBaseQuery),
            'attendanceSummary' => $this->waliSantriService->buildAttendanceSummary(clone $attendanceBaseQuery),
            'leaveSummary' => $this->waliSantriService->buildLeaveSummary(clone $leaveRequestBaseQuery),
            'paymentMethods' => SantriPayment::paymentMethods(),
            'pendingPaymentConfirmationsByInvoice' => $this->waliSantriService->pendingPaymentConfirmationsByInvoice(
                $currentUser,
                $santriIds
            ),
            'summary' => $this->waliSantriService->buildSummary(
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

    public function storePaymentConfirmation(
        StorePaymentConfirmationRequest $request,
        SantriInvoice $invoice
    ): RedirectResponse {
        $invoice = $this->resolveLinkedInvoice($request, $invoice);

        $proofPath = $request->hasFile('proof')
            ? $request->file('proof')->store(
                'wali-payment-proofs/'.$invoice->tenant_id,
                'public'
            )
            : null;

        $this->waliSantriService->storePaymentConfirmation(
            invoice: $invoice,
            validated: $request->validated(),
            user: $request->user(),
            proofPath: $proofPath,
        );

        return back()
            ->with('success', 'Konfirmasi pembayaran berhasil dikirim dan menunggu verifikasi admin pondok.');
    }

    public function printInvoice(Request $request, SantriInvoice $invoice): View
    {
        $invoice = $this->resolveLinkedInvoice($request, $invoice);

        return view('wali-santri.invoice-receipt', [
            'invoice' => $invoice,
            'payments' => $invoice->payments,
        ]);
    }

    public function riwayatAbsensi(Request $request, Santri $santri): View
    {
        $santri = $this->resolveLinkedSantri($request, $santri);
        $currentUser = $request->user();

        $dateFrom = $request->input('date_from', now()->subMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $records = AttendanceRecord::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $santri->id)
            ->whereHas('session', function ($query) use ($dateFrom, $dateTo): void {
                $query->whereDate('session_date', '>=', $dateFrom)
                    ->whereDate('session_date', '<=', $dateTo);
            })
            ->with(['session.activity'])
            ->orderByDesc('recorded_at')
            ->paginate(20);

        return view('wali-santri.absensi', [
            'santri' => $santri,
            'records' => $records,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function riwayatPelanggaran(Request $request, Santri $santri): View
    {
        $santri = $this->resolveLinkedSantri($request, $santri);
        $currentUser = $request->user();

        $records = Pelanggaran::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $santri->id)
            ->with(['kategori', 'pencatat'])
            ->orderByDesc('tanggal')
            ->paginate(20);

        $totalPoin = Pelanggaran::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $santri->id)
            ->sum('poin');

        return view('wali-santri.pelanggaran', [
            'santri' => $santri,
            'records' => $records,
            'totalPoin' => $totalPoin,
        ]);
    }

    public function riwayatTahfidz(Request $request, Santri $santri): View
    {
        $santri = $this->resolveLinkedSantri($request, $santri);
        $currentUser = $request->user();

        $totalAyat = TahfidzRecord::query()
            ->visibleTo($currentUser)
            ->whereIn('tahfidz_session_id', TahfidzSession::query()
                ->where('santri_id', $santri->id)
                ->select('id')
            )
            ->selectRaw('COALESCE(SUM(verse_end - verse_start + 1), 0) as total')
            ->value('total');

        $sessions = TahfidzSession::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $santri->id)
            ->where('status', TahfidzSession::STATUS_COMPLETED)
            ->with(['musyrif', 'records.surah'])
            ->orderByDesc('session_date')
            ->paginate(20);

        return view('wali-santri.tahfidz', [
            'santri' => $santri,
            'sessions' => $sessions,
            'totalAyat' => (int) $totalAyat,
            'totalSesi' => $sessions->total(),
        ]);
    }

    public function riwayatAkademik(Request $request, Santri $santri): View
    {
        $santri = $this->resolveLinkedSantri($request, $santri);
        $currentUser = $request->user();

        $semesters = NilaiSantri::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $santri->id)
            ->distinct()
            ->orderBy('semester', 'desc')
            ->pluck('semester');

        $nilais = NilaiSantri::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $santri->id)
            ->with(['mataPelajaran', 'inputBy'])
            ->orderByDesc('semester')
            ->get();

        return view('wali-santri.akademik', [
            'santri' => $santri,
            'nilais' => $nilais,
            'semesters' => $semesters,
        ]);
    }

    public function raporSantri(Request $request, Santri $santri): View
    {
        $santri = $this->resolveLinkedSantri($request, $santri);
        $currentUser = $request->user();
        $semester = $request->input('semester');

        if (! $semester) {
            $semester = NilaiSantri::query()
                ->visibleTo($currentUser)
                ->where('santri_id', $santri->id)
                ->orderBy('semester', 'desc')
                ->value('semester');
        }

        $nilais = NilaiSantri::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $santri->id)
            ->where('semester', $semester)
            ->with(['mataPelajaran', 'inputBy'])
            ->get();

        $tahfidzStats = TahfidzRecord::query()
            ->visibleTo($currentUser)
            ->whereIn('tahfidz_session_id', TahfidzSession::query()
                ->where('santri_id', $santri->id)
                ->select('id')
            )
            ->selectRaw('COALESCE(SUM(verse_end - verse_start + 1), 0) as total_ayat')
            ->selectRaw('COUNT(*) as total_record')
            ->first();

        $totalPoinPelanggaran = Pelanggaran::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $santri->id)
            ->sum('poin');

        $semesters = NilaiSantri::query()
            ->visibleTo($currentUser)
            ->where('santri_id', $santri->id)
            ->distinct()
            ->orderBy('semester', 'desc')
            ->pluck('semester');

        return view('wali-santri.rapor-santri', [
            'santri' => $santri,
            'semester' => $semester,
            'semesters' => $semesters,
            'nilais' => $nilais,
            'tahfidzStats' => $tahfidzStats,
            'totalPoinPelanggaran' => $totalPoinPelanggaran,
        ]);
    }

    public function profilSantri(Request $request, Santri $santri): View
    {
        $santri = $this->resolveLinkedSantri($request, $santri);
        $santri->loadMissing(['room', 'guardians']);

        return view('wali-santri.profil-santri', [
            'santri' => $santri,
        ]);
    }

    protected function resolveLinkedSantri(Request $request, Santri $santri): Santri
    {
        $currentUser = $request->user();
        $santriIds = $currentUser
            ->guardianSantris()
            ->pluck('santris.id');

        abort_if($santriIds->isEmpty(), 404);
        abort_unless($santriIds->contains($santri->id), 404);

        return $santri->loadMissing('room');
    }

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
