<?php

namespace App\Notifications;

use App\Models\KesehatanObat;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ObatExpiredNotification extends Notification
{
    use Queueable;

    public function __construct(
        public KesehatanObat $obat
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Obat Kedaluwarsa',
            'message' => "Obat \"{$this->obat->nama_obat}\" telah melewati tanggal kedaluwarsa ({$this->obat->expired_date?->translatedFormat('d M Y')}).",
            'icon' => 'ti-alert-triangle',
            'url' => route('kesehatan.obat.index', [], false),
            'obat_id' => $this->obat->id,
            'nama_obat' => $this->obat->nama_obat,
            'expired_date' => $this->obat->expired_date?->toISOString(),
        ];
    }
}
