<?php

namespace App\Notifications;

use App\Models\Pelanggaran;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewPelanggaranNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Pelanggaran $pelanggaran
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $santri = $this->pelanggaran->santri;
        $kategori = $this->pelanggaran->kategori;

        return [
            'title' => 'Pelanggaran Santri',
            'message' => ($santri?->full_name ?? 'Santri')
                .' mendapatkan pelanggaran '
                .($kategori?->nama ?? '-')
                .' ('.$this->pelanggaran->poin.' poin)'
                .'.',
            'icon' => 'ti-alert-triangle',
            'url' => route('wali-santri.pelanggaran', $santri, false),
            'pelanggaran_id' => $this->pelanggaran->id,
            'santri_id' => $this->pelanggaran->santri_id,
            'poin' => $this->pelanggaran->poin,
            'tanggal' => $this->pelanggaran->tanggal?->toISOString(),
        ];
    }
}
