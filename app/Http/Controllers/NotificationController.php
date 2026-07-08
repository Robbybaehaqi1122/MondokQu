<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(15);

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function show(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        abort_unless($this->belongsToUser($notification, $request->user()), 404);

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return redirect($this->safeRedirectUrl($notification->data['url'] ?? null));
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi sudah ditandai dibaca.');
    }

    protected function belongsToUser(DatabaseNotification $notification, ?User $user): bool
    {
        return $user
            && $notification->notifiable_type === User::class
            && (int) $notification->notifiable_id === (int) $user->id;
    }

    protected function safeRedirectUrl(mixed $url): string
    {
        if (! is_string($url) || ! str_starts_with($url, '/') || str_starts_with($url, '//')) {
            return route('dashboard', absolute: false);
        }

        return url($url);
    }
}
