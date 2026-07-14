<?php

namespace App\Notifications;

use App\Models\KesehatanObat;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ObatStokHabisNotification extends Notification
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
            'title' => 'Stok Obat Habis',
            'message' => "Stok obat \"{$this->obat->nama_obat}\" sudah habis dan perlu segera ditambah.",
            'icon' => 'ti-pill-off',
            'url' => route('kesehatan.obat.index', [], false),
            'obat_id' => $this->obat->id,
            'nama_obat' => $this->obat->nama_obat,
        ];
    }
}
