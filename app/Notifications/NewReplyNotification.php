<?php

namespace App\Notifications;

use App\Models\Communication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewReplyNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Communication $reply
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $parent = $this->reply->parent;
        $santri = $this->reply->santri;
        $isFromPondok = $this->reply->direction === 'incoming';

        $title = $isFromPondok
            ? 'Pesan Dibalas Pondok'
            : 'Pesan Baru dari Wali Santri';

        $message = $isFromPondok
            ? 'Pondok membalas pesan untuk '.($santri?->full_name ?? 'Santri').'.'
            : 'Pesan baru dari wali santri untuk '.($santri?->full_name ?? 'Santri').'.';

        return [
            'title' => $title,
            'message' => $message,
            'icon' => 'ti-message-reply',
            'url' => $isFromPondok
                ? route('wali-santri.komunikasi.show', $santri, false)
                : route('komunikasi.show', $santri, false),
            'communication_id' => $this->reply->id,
            'parent_id' => $parent?->id,
            'santri_id' => $santri?->id,
        ];
    }
}
