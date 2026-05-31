<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BatchInvoiceNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $count,
        public string $title,
        public string $periodLabel,
        public int $amount,
        public string $dueDate
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Tagihan Bulanan Dibuat',
            'message' => "Sebanyak {$this->count} tagihan {$this->title} periode {$this->periodLabel} telah diterbitkan. Besaran Rp ".number_format($this->amount / 100, 0, ',', '.').' per santri.',
            'icon' => 'ti-receipt',
            'url' => route('wali-santri.dashboard', [], false),
            'count' => $this->count,
            'period_title' => $this->title,
            'amount' => $this->amount,
        ];
    }
}
