<?php

namespace App\Exports;

use App\Models\Santri;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SantriExcelExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnFormatting
{
    public function __construct(
        protected ?User $currentUser,
        protected string $search = '',
        protected string $status = '',
        protected string $gender = '',
    ) {}

    public function query(): Builder
    {
        return Santri::query()
            ->withoutTenantScope()
            ->visibleTo($this->currentUser)
            ->withFilters($this->search, $this->status, $this->gender)
            ->with(['guardians', 'room'])
            ->orderBy('full_name');
    }

    public function headings(): array
    {
        return [
            'NIS',
            'Nama Lengkap',
            'Jenis Kelamin',
            'Status',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Alamat',
            'Nama Ayah',
            'Nama Ibu',
            'Nama Wali',
            'Nomor HP Wali',
            'Akun Wali Portal',
            'Kontak Darurat',
            'Tanggal Masuk',
            'Angkatan',
            'Kamar',
            'Catatan',
        ];
    }

    public function map($santri): array
    {
        return [
            $santri->nis,
            $santri->full_name,
            $santri->genderLabel(),
            $santri->statusLabel(),
            $santri->birth_place,
            $santri->birth_date?->toDateString(),
            $santri->address,
            $santri->father_name,
            $santri->mother_name,
            $santri->displayGuardianName(''),
            $santri->displayGuardianPhone(''),
            $santri->guardians->pluck('name')->implode('; '),
            $santri->emergency_contact,
            $santri->entry_date?->toDateString(),
            $santri->entry_year,
            $santri->displayRoomName(''),
            $santri->notes,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => 'DD/MM/YYYY',
            'N' => 'DD/MM/YYYY',
        ];
    }

    public function filename(): string
    {
        return 'data-santri-'.now()->format('Ymd-His').'.xlsx';
    }
}
