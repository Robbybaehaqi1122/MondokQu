<?php

namespace App\Notifications;

use App\Models\TahfidzSession;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewTahfidzSessionNotification extends Notification
{
    use Queueable;

    public function __construct(
        public TahfidzSession $session
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $santri = $this->session->santri;

        $totalAyat = $this->session->records->sum(fn ($r) => ($r->verse_end - $r->verse_start + 1));

        return [
            'title' => 'Setoran Tahfidz',
            'message' => ($santri?->full_name ?? 'Santri')
                .' menyetorkan hafalan '
                .$totalAyat.' ayat pada '
                .($this->session->session_date?->translatedFormat('d M Y') ?? '-')
                .'.',
            'icon' => 'ti-book',
            'url' => route('wali-santri.tahfidz', $santri, false),
            'session_id' => $this->session->id,
            'santri_id' => $this->session->santri_id,
            'session_date' => $this->session->session_date?->toISOString(),
            'total_ayat' => $totalAyat,
        ];
    }
}
