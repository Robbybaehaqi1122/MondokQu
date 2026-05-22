<?php

namespace App\Notifications;

use App\Models\DataExport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DataExportCompletedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public DataExport $dataExport
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
        return [
            'title' => 'Export selesai',
            'message' => $this->dataExport->name.' siap diunduh.',
            'icon' => 'ti-file-download',
            'url' => route('exports.download', $this->dataExport, false),
            'export_id' => $this->dataExport->id,
            'export_type' => $this->dataExport->type,
            'filename' => $this->dataExport->filename,
            'row_count' => $this->dataExport->row_count,
            'completed_at' => $this->dataExport->completed_at?->toISOString(),
        ];
    }
}
