<?php

namespace App\Notifications;

use App\Models\KesehatanImunisasi;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ImunisasiTertundaNotification extends Notification
{
    use Queueable;

    public function __construct(
        public KesehatanImunisasi $imunisasi
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $santri = $this->imunisasi->santri;

        return [
            'title' => 'Imunisasi Tertunda',
            'message' => "Imunisasi \"{$this->imunisasi->jenis_imunisasi}\" untuk santri {$santri?->full_name ?? 'Unknown'} masih tertunda.",
            'icon' => 'ti-syringe',
            'url' => route('kesehatan.imunisasi.index', [], false),
            'imunisasi_id' => $this->imunisasi->id,
            'santri_id' => $this->imunisasi->santri_id,
            'jenis_imunisasi' => $this->imunisasi->jenis_imunisasi,
        ];
    }
}
