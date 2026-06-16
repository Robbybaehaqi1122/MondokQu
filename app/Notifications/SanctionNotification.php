<?php

namespace App\Notifications;

use App\Models\SanctionLog;
use App\Models\Santri;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SanctionNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Santri $santri,
        protected SanctionLog $sanctionLog,
        protected string $sanctionName,
        protected string $sanctionType,
        protected int $totalPoints,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => "Sanksi: {$this->sanctionName}",
            'message' => "Santri {$this->santri->full_name} mencapai {$this->totalPoints} poin pelanggaran dan mendapatkan sanksi: {$this->sanctionName}.",
            'icon' => 'ti-gavel',
            'url' => route('santri.show', $this->santri),
            'santri_id' => $this->santri->id,
            'sanction_log_id' => $this->sanctionLog->id,
            'sanction_type' => $this->sanctionType,
            'total_points' => $this->totalPoints,
        ];
    }
}
