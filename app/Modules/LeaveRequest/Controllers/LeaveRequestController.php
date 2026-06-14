<?php

namespace App\Modules\LeaveRequest\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\Santri;
use App\Modules\LeaveRequest\Requests\StoreLeaveRequestRequest;
use App\Modules\LeaveRequest\Requests\UpdateLeaveRequestRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    /**
     * Display the leave request management panel.
     */
    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $search = trim((string) $request->string('q'));
        $selectedStatus = trim((string) $request->string('status'));
        $selectedSantriId = trim((string) $request->string('santri'));

        $baseQuery = LeaveRequest::query()
            ->visibleTo($currentUser)
            ->with(['santri' => fn ($q) => $q->select('id', 'full_name', 'nis')]);

        $leaveRequests = (clone $baseQuery)
            ->when($selectedSantriId !== '', fn ($query) => $query->where('santri_id', (int) $selectedSantriId))
            ->when($search !== '', fn ($query) => $query->whereHas('santri', fn ($q) => $q->where('full_name', 'like', "%{$search}%")
                ->orWhere('nis', 'like', "%{$search}%")
            ))
            ->when($selectedStatus !== '', fn ($query) => $query->where('status', $selectedStatus))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $santris = Santri::query()
            ->visibleTo($currentUser)
            ->where('status', Santri::STATUS_ACTIVE)
            ->orderBy('full_name')
            ->limit(500)
            ->get();

        return view('pengurus.leave_requests.index', [
            'filters' => [
                'q' => $search,
                'status' => $selectedStatus,
                'santri' => $selectedSantriId,
            ],
            'leaveRequests' => $leaveRequests,
            'stats' => [
                'pending' => (clone $baseQuery)->where('status', LeaveRequest::STATUS_PENDING)->count(),
                'approved' => (clone $baseQuery)->where('status', LeaveRequest::STATUS_APPROVED)->count(),
                'rejected' => (clone $baseQuery)->where('status', LeaveRequest::STATUS_REJECTED)->count(),
                'completed' => (clone $baseQuery)->where('status', LeaveRequest::STATUS_COMPLETED)->count(),
            ],
            'statusOptions' => $this->statusOptions(),
            'santris' => $santris,
        ]);
    }

    /**
     * Store a newly created leave request.
     */
    public function store(StoreLeaveRequestRequest $request): RedirectResponse
    {
        $this->authorize('create', LeaveRequest::class);

        $validated = $request->validated();
        $currentUser = $request->user();

        $leaveRequest = LeaveRequest::query()->create([
            'tenant_id' => $currentUser->tenant_id,
            'santri_id' => $validated['santri_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'status' => LeaveRequest::STATUS_PENDING,
            'created_by' => $currentUser->id,
        ]);

        $this->activityLogger->log(
            action: 'leave_request_created',
            actor: $currentUser,
            target: $leaveRequest,
            description: 'Pengajuan izin santri dibuat.',
            properties: [
                'target_name' => $leaveRequest->santri->full_name ?? '',
                'start_date' => $leaveRequest->start_date,
                'end_date' => $leaveRequest->end_date,
                'status' => $leaveRequest->status,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('pengurus.izin.index')
            ->with('success', 'Pengajuan izin berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified leave request.
     */
    public function edit(LeaveRequest $leaveRequest): View|RedirectResponse
    {
        $this->authorize('update', $leaveRequest);

        $currentUser = request()->user();
        $leaveRequest = LeaveRequest::query()
            ->visibleTo($currentUser)
            ->with(['santri' => fn ($q) => $q->select('id', 'full_name', 'nis')])
            ->findOrFail($leaveRequest->id);

        // Only pending requests can be edited
        if ($leaveRequest->status !== LeaveRequest::STATUS_PENDING) {
            return redirect()
                ->route('pengurus.izin.index')
                ->with('error', 'Hanya pengajuan yang masih menunggu yang dapat diedit.');
        }

        $santris = Santri::query()
            ->visibleTo($currentUser)
            ->where('status', Santri::STATUS_ACTIVE)
            ->orderBy('full_name')
            ->limit(500)
            ->get();

        return view('pengurus.leave_requests.edit', [
            'leaveRequest' => $leaveRequest,
            'santris' => $santris,
        ]);
    }

    /**
     * Update the specified leave request.
     */
    public function update(UpdateLeaveRequestRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('update', $leaveRequest);

        $currentUser = $request->user();
        $leaveRequest = LeaveRequest::query()
            ->visibleTo($currentUser)
            ->findOrFail($leaveRequest->id);

        // Only pending requests can be updated
        if ($leaveRequest->status !== LeaveRequest::STATUS_PENDING) {
            return redirect()
                ->route('pengurus.izin.index')
                ->with('error', 'Hanya pengajuan yang masih menunggu yang dapat diperbarui.');
        }

        $validated = $request->validated();

        $previousValues = $leaveRequest->only(['santri_id', 'start_date', 'end_date', 'reason']);

        $leaveRequest->update([
            'santri_id' => $validated['santri_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
        ]);

        $this->activityLogger->log(
            action: 'leave_request_updated',
            actor: $currentUser,
            target: $leaveRequest,
            description: 'Pengajuan izin santri diperbarui.',
            properties: [
                'target_name' => $leaveRequest->santri->full_name ?? '',
                'before' => $previousValues,
                'after' => $leaveRequest->fresh()?->only(['santri_id', 'start_date', 'end_date', 'reason']),
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('pengurus.izin.index')
            ->with('success', 'Pengajuan izin berhasil diperbarui.');
    }

    /**
     * Approve the specified leave request.
     */
    public function approve(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('approve', $leaveRequest);

        $currentUser = $request->user();
        $leaveRequest = LeaveRequest::query()
            ->visibleTo($currentUser)
            ->findOrFail($leaveRequest->id);

        if ($leaveRequest->status !== LeaveRequest::STATUS_PENDING) {
            return redirect()
                ->route('pengurus.izin.index')
                ->with('error', 'Hanya pengajuan yang masih menunggu yang dapat disetujui.');
        }

        $leaveRequest->update([
            'status' => LeaveRequest::STATUS_APPROVED,
            'approved_by' => $currentUser->id,
            'approved_at' => now(),
        ]);

        $this->activityLogger->log(
            action: 'leave_request_approved',
            actor: $currentUser,
            target: $leaveRequest,
            description: 'Pengajuan izin santri disetujui.',
            properties: [
                'target_name' => $leaveRequest->santri->full_name ?? '',
                'start_date' => $leaveRequest->start_date,
                'end_date' => $leaveRequest->end_date,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('pengurus.izin.index')
            ->with('success', 'Pengajuan izin berhasil disetujui.');
    }

    /**
     * Reject the specified leave request.
     */
    public function reject(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('reject', $leaveRequest);

        $currentUser = $request->user();
        $leaveRequest = LeaveRequest::query()
            ->visibleTo($currentUser)
            ->findOrFail($leaveRequest->id);

        if ($leaveRequest->status !== LeaveRequest::STATUS_PENDING) {
            return redirect()
                ->route('pengurus.izin.index')
                ->with('error', 'Hanya pengajuan yang masih menunggu yang dapat ditolak.');
        }

        $leaveRequest->update([
            'status' => LeaveRequest::STATUS_REJECTED,
            'approved_by' => $currentUser->id,
            'approved_at' => now(),
        ]);

        $this->activityLogger->log(
            action: 'leave_request_rejected',
            actor: $currentUser,
            target: $leaveRequest,
            description: 'Pengajuan izin santri ditolak.',
            properties: [
                'target_name' => $leaveRequest->santri->full_name ?? '',
                'start_date' => $leaveRequest->start_date,
                'end_date' => $leaveRequest->end_date,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('pengurus.izin.index')
            ->with('success', 'Pengajuan izin berhasil ditolak.');
    }

    /**
     * Mark the leave request as completed.
     */
    public function complete(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('complete', $leaveRequest);

        $currentUser = $request->user();
        $leaveRequest = LeaveRequest::query()
            ->visibleTo($currentUser)
            ->findOrFail($leaveRequest->id);

        if ($leaveRequest->status !== LeaveRequest::STATUS_APPROVED) {
            return redirect()
                ->route('pengurus.izin.index')
                ->with('error', 'Hanya pengajuan yang disetujui yang dapat ditandai selesai.');
        }

        $leaveRequest->update([
            'status' => LeaveRequest::STATUS_COMPLETED,
        ]);

        $this->activityLogger->log(
            action: 'leave_request_completed',
            actor: $currentUser,
            target: $leaveRequest,
            description: 'Pengajuan izin santri ditandai selesai.',
            properties: [
                'target_name' => $leaveRequest->santri->full_name ?? '',
                'start_date' => $leaveRequest->start_date,
                'end_date' => $leaveRequest->end_date,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('pengurus.izin.index')
            ->with('success', 'Pengajuan izin ditandai selesai.');
    }

    /**
     * Delete the leave request.
     */
    public function destroy(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('delete', $leaveRequest);

        $currentUser = $request->user();
        $leaveRequest = LeaveRequest::query()
            ->visibleTo($currentUser)
            ->findOrFail($leaveRequest->id);

        // Only pending or rejected can be deleted (to keep history)
        if (! in_array($leaveRequest->status, [LeaveRequest::STATUS_PENDING, LeaveRequest::STATUS_REJECTED])) {
            return redirect()
                ->route('pengurus.izin.index')
                ->with('error', 'Hanya pengajuan yang menunggu atau ditolak yang dapat dihapus.');
        }

        $this->activityLogger->log(
            action: 'leave_request_deleted',
            actor: $currentUser,
            target: $leaveRequest,
            description: 'Pengajuan izin santri dihapus.',
            properties: [
                'target_name' => $leaveRequest->santri->full_name ?? '',
                'start_date' => $leaveRequest->start_date,
                'end_date' => $leaveRequest->end_date,
                'status' => $leaveRequest->status,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        $leaveRequest->delete();

        return redirect()
            ->route('pengurus.izin.index')
            ->with('success', 'Pengajuan izin berhasil dihapus.');
    }

    protected function statusOptions(): array
    {
        return [
            ['value' => LeaveRequest::STATUS_PENDING, 'label' => 'Menunggu'],
            ['value' => LeaveRequest::STATUS_APPROVED, 'label' => 'Disetujui'],
            ['value' => LeaveRequest::STATUS_REJECTED, 'label' => 'Ditolak'],
            ['value' => LeaveRequest::STATUS_COMPLETED, 'label' => 'Selesai'],
        ];
    }
}
