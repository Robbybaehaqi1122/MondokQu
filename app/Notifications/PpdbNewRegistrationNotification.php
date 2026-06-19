<?php

namespace App\Notifications;

use App\Modules\PpdbQu\Models\PpdbPendaftaran;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PpdbNewRegistrationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public PpdbPendaftaran $pendaftaran
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Pendaftaran PPDB Baru',
            'message' => $this->pendaftaran->nama_lengkap
                .' ('.$this->pendaftaran->nomor_pendaftaran
                .') mendaftar melalui gelombang '
                .($this->pendaftaran->gelombang?->nama ?? '-')
                .'.',
            'icon' => 'ti-user-plus',
            'url' => route('ppdb.pendaftaran.show', $this->pendaftaran, absolute: false),
            'pendaftaran_id' => $this->pendaftaran->id,
            'tenant_id' => $this->pendaftaran->tenant_id,
        ];
    }
}
