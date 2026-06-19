<?php

namespace App\Modules\PpdbQu\Controllers;

use App\Http\Controllers\Controller;
use App\Notifications\PpdbNewRegistrationNotification;
use App\Notifications\PpdbStatusChangedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PpdbQuNotificationController extends Controller
{
    protected function ppdbNotificationTypes(): array
    {
        return [
            PpdbNewRegistrationNotification::class,
            PpdbStatusChangedNotification::class,
        ];
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->whereIn('type', $this->ppdbNotificationTypes())
            ->latest()
            ->paginate(15);

        $unreadCount = $user->unreadNotifications()
            ->whereIn('type', $this->ppdbNotificationTypes())
            ->count();

        return view('modules.ppdb-qu.notifikasi.index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'pendingCount' => \App\Modules\PpdbQu\Models\PpdbPendaftaran::withoutTenantScope()
                ->where('tenant_id', $user->tenant_id)
                ->where('status', 'menunggu')
                ->count(),
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = $request->user()
            ->unreadNotifications()
            ->whereIn('type', $this->ppdbNotificationTypes())
            ->count();

        $pendingCount = \App\Modules\PpdbQu\Models\PpdbPendaftaran::withoutTenantScope()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('status', 'menunggu')
            ->count();

        return response()->json([
            'unread_count' => $count,
            'pending_count' => $pendingCount,
            'total' => $count + $pendingCount,
        ]);
    }
}
