<?php

namespace App\Notifications;

use App\Models\SantriInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewInvoiceNotification extends Notification
{
    use Queueable;

    public function __construct(
        public SantriInvoice $invoice
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $santri = $this->invoice->santri;

        return [
            'title' => 'Tagihan Baru',
            'message' => 'Tagihan baru untuk '
                .($santri?->full_name ?? 'Santri')
                .': '
                .$this->invoice->title
                .' sebesar Rp '.number_format($this->invoice->amount / 100, 0, ',', '.')
                .'.',
            'icon' => 'ti-receipt',
            'url' => route('wali-santri.invoices.show', $this->invoice, false),
            'invoice_id' => $this->invoice->id,
            'santri_id' => $this->invoice->santri_id,
            'amount' => $this->invoice->amount,
            'due_date' => $this->invoice->due_date?->toISOString(),
        ];
    }
}
