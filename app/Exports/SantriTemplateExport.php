<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SantriTemplateExport implements FromArray, WithHeadings, WithStyles
{
    protected array $rows;

    public function __construct()
    {
        $this->rows = [
            [
                'Ali bin Abi Thalib',
                '2026001',
                '1234567890',
                'Jakarta',
                '2010-06-17',
                'laki-laki',
                'Jl. Contoh No. 123, Jakarta',
                'Abu Thalib',
                'Fatimah',
                '081234567890',
                'Al-Ghazali',
                'aktif',
            ],
            [
                'Aisyah binti Abu Bakar',
                '2026002',
                '1234567891',
                'Bandung',
                '2011-08-22',
                'perempuan',
                'Jl. Contoh No. 456, Bandung',
                'Abu Bakar',
                'Ummi Rahmah',
                '081234567891',
                'Al-Farabi',
                'aktif',
            ],
        ];
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'nama',
            'nis',
            'nisn',
            'tempat_lahir',
            'tanggal_lahir',
            'jenis_kelamin',
            'alamat',
            'nama_ayah',
            'nama_ibu',
            'no_telp_wali',
            'kamar',
            'status',
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
        return 'template-import-santri.xlsx';
    }
}
