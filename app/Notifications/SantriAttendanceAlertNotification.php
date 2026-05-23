<?php

namespace App\Notifications;

use App\Models\AttendanceRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SantriAttendanceAlertNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public AttendanceRecord $attendanceRecord
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $santri = $this->attendanceRecord->santri;
        $session = $this->attendanceRecord->session;

        return [
            'title' => 'Absensi santri: Alpa',
            'message' => ($santri?->full_name ?? 'Santri')
                .' tercatat Alpa pada '
                .($session?->activity?->name ?? 'sesi absensi')
                .' tanggal '
                .($session?->session_date?->translatedFormat('d M Y') ?? '-')
                .'.',
            'icon' => 'ti-user-x',
            'url' => route('wali-santri.dashboard', [], false),
            'attendance_record_id' => $this->attendanceRecord->id,
            'santri_id' => $this->attendanceRecord->santri_id,
            'status' => $this->attendanceRecord->status,
            'recorded_at' => $this->attendanceRecord->recorded_at?->toISOString(),
        ];
    }
}
