<?php

namespace App\Notifications;

use App\Modules\PpdbQu\Models\PpdbPendaftaran;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PpdbStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public PpdbPendaftaran $pendaftaran,
        public string $oldStatus,
        public string $newStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Status PPDB Diperbarui',
            'message' => 'Status '.$this->pendaftaran->nomor_pendaftaran
                .' ('.$this->pendaftaran->nama_lengkap
                .') berubah dari '.$this->oldStatus
                .' menjadi '.$this->newStatus
                .'.',
            'icon' => 'ti-arrows-diff',
            'url' => route('ppdb.pendaftaran.show', $this->pendaftaran, absolute: false),
            'pendaftaran_id' => $this->pendaftaran->id,
            'tenant_id' => $this->pendaftaran->tenant_id,
        ];
    }
}
