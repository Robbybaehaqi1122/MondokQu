<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceRekapExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected ?User $currentUser,
        protected Collection $rekap,
        protected string $dateFrom,
        protected string $dateTo,
    ) {}

    public function collection(): Collection
    {
        return $this->rekap;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Santri',
            'NIS',
            'Kamar',
            'Hadir',
            'Sakit',
            'Izin',
            'Alpa',
            'Terlambat',
            'Total',
            '% Hadir',
        ];
    }

    public function map($row): array
    {
        static $i = 0;
        $i++;

        return [
            $i,
            $row['full_name'],
            $row['nis'] ?: '-',
            $row['room_name'],
            $row['present'],
            $row['sick'],
            $row['permission'],
            $row['absent'],
            $row['late'],
            $row['total'],
            $row['percentage'].'%',
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
        $period = str_replace(['/', '\\'], '-', "{$this->dateFrom}-{$this->dateTo}");

        return "rekap-absensi-{$period}";
    }
}
