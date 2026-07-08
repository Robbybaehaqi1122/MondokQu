<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceDetailExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected ?User $currentUser,
        protected Builder $query,
    ) {}

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Kegiatan',
            'Jam Kegiatan',
            'Nama Santri',
            'NIS',
            'Kamar',
            'Status',
            'Catatan',
            'Diinput Pada',
            'Diinput Oleh',
        ];
    }

    public function map($record): array
    {
        return [
            $record->session?->session_date?->translatedFormat('d M Y') ?? '-',
            $record->session?->activity?->name ?? '-',
            $record->session?->activity?->timeRangeLabel() ?? '-',
            $record->santri?->full_name ?? '-',
            $record->santri?->nis ?? '-',
            $record->santri?->displayRoomName() ?? '-',
            $record->statusLabel(),
            $record->notes ?: '-',
            $record->recorded_at?->translatedFormat('d M Y H:i') ?? '-',
            $record->recorder?->name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function filename(): string
    {
        return 'detail-absensi-'.now()->format('Ymd-His');
    }
}
