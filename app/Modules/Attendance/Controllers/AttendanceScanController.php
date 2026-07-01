<?php

namespace App\Modules\Attendance\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Santri;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AttendanceScanController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    public function index(Request $request): View
    {
        $currentUser = $request->user();

        $todaySessions = AttendanceSession::query()
            ->visibleTo($currentUser)
            ->with('activity')
            ->whereDate('session_date', now())
            ->where('status', '!=', AttendanceSession::STATUS_COMPLETED)
            ->orderBy('session_date')
            ->get();

        return view('attendance.scan.index', [
            'todaySessions' => $todaySessions,
            'selectedSession' => $todaySessions->first(),
            'statusOptions' => AttendanceRecord::statusOptions(),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'barcode' => ['required', 'string', 'max:20'],
        ]);

        $currentUser = $request->user();

        $santri = Santri::query()
            ->visibleTo($currentUser)
            ->with('room')
            ->active()
            ->where('barcode', $request->barcode)
            ->first();

        if (! $santri) {
            return response()->json([
                'found' => false,
                'message' => 'Santri tidak ditemukan. Periksa kembali barcode.',
            ]);
        }

        return response()->json([
            'found' => true,
            'santri' => [
                'id' => $santri->id,
                'uuid' => $santri->uuid,
                'nis' => $santri->nis,
                'full_name' => $santri->full_name,
                'gender' => $santri->gender,
                'gender_label' => $santri->genderLabel(),
                'room' => $santri->displayRoomName(),
                'photo_url' => $santri->photoUrl(),
            ],
        ]);
    }

    public function searchByName(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $currentUser = $request->user();
        $query = $request->q;

        $santris = Santri::query()
            ->visibleTo($currentUser)
            ->with('room')
            ->active()
            ->where(function ($q) use ($query): void {
                $q->where('full_name', 'like', "%{$query}%")
                    ->orWhere('nis', 'like', "%{$query}%")
                    ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->orderBy('full_name')
            ->limit(20)
            ->get()
            ->map(fn (Santri $s) => [
                'id' => $s->id,
                'uuid' => $s->uuid,
                'nis' => $s->nis,
                'full_name' => $s->full_name,
                'gender' => $s->gender,
                'gender_label' => $s->genderLabel(),
                'room' => $s->displayRoomName(),
                'photo_url' => $s->photoUrl(),
            ]);

        return response()->json([
            'santris' => $santris,
        ]);
    }

    public function record(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'santri_id' => ['required', 'exists:santris,id'],
            'session_id' => ['required', 'exists:attendance_sessions,id'],
            'status' => ['required', 'string', 'in:present,permission,sick,absent,late'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $currentUser = $request->user();

        $session = AttendanceSession::query()
            ->visibleTo($currentUser)
            ->findOrFail($validated['session_id']);

        if ($session->status === AttendanceSession::STATUS_COMPLETED) {
            return back()->with('error', 'Sesi absensi sudah selesai, tidak bisa menambahkan data.');
        }

        $santri = Santri::query()
            ->visibleTo($currentUser)
            ->findOrFail($validated['santri_id']);

        $recordedAt = now();

        DB::transaction(function () use ($session, $santri, $validated, $currentUser, $recordedAt): void {
            AttendanceRecord::query()->updateOrCreate(
                [
                    'tenant_id' => $session->tenant_id,
                    'attendance_session_id' => $session->id,
                    'santri_id' => $santri->id,
                ],
                [
                    'status' => $validated['status'],
                    'notes' => $validated['notes'] ?? null,
                    'recorded_by' => $currentUser?->id,
                    'recorded_at' => $recordedAt,
                ]
            );
        });

        $this->activityLogger->log(
            action: 'attendance_scan_recorded',
            actor: $currentUser,
            target: $session,
            description: "Absen scan untuk {$santri->full_name} via barcode.",
            properties: [
                'session_id' => $session->id,
                'santri_id' => $santri->id,
                'status' => $validated['status'],
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('success', "Absen {$santri->full_name} berhasil dicatat.");
    }

    public function barcodeImage(Santri $santri)
    {
        $qrCode = QrCode::format('svg')
            ->size(300)
            ->errorCorrection('M')
            ->generate($santri->barcode);

        return response($qrCode, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function card(Request $request, Santri $santri): View
    {
        return view('attendance.scan.card', [
            'santri' => $santri,
        ]);
    }
}
