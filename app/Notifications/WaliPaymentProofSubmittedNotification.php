<?php

namespace App\Notifications;

use App\Models\SantriPaymentConfirmation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WaliPaymentProofSubmittedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public SantriPaymentConfirmation $paymentConfirmation
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $invoice = $this->paymentConfirmation->invoice;
        $santri = $this->paymentConfirmation->santri;

        return [
            'title' => 'Bukti bayar wali masuk',
            'message' => 'Bukti bayar untuk '
                .($santri?->full_name ?? 'santri')
                .' pada tagihan '
                .($invoice?->invoice_number ?? '-')
                .' menunggu verifikasi.',
            'icon' => 'ti-receipt-2',
            'url' => route('santri.payments.invoices', [], false),
            'payment_confirmation_id' => $this->paymentConfirmation->id,
            'invoice_id' => $this->paymentConfirmation->santri_invoice_id,
            'santri_id' => $this->paymentConfirmation->santri_id,
            'amount' => (string) $this->paymentConfirmation->amount,
            'submitted_at' => $this->paymentConfirmation->created_at?->toISOString(),
        ];
    }
}
