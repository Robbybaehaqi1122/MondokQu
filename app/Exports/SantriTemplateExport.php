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
                '081234567890',
                'SMA',
                'Wiraswasta',
                'Fatimah',
                '081234567891',
                'SMP',
                'Ibu Rumah Tangga',
                '081234567892',
                'Paman',
                'Jl. Wali No. 789, Jakarta',
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
                '081234567893',
                'S1',
                'Guru',
                'Ummi Rahmah',
                '081234567894',
                'D3',
                'Perawat',
                '081234567895',
                'Kakek',
                'Jl. Wali No. 100, Bandung',
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
            'no_telp_ayah',
            'pendidikan_ayah',
            'pekerjaan_ayah',
            'nama_ibu',
            'no_telp_ibu',
            'pendidikan_ibu',
            'pekerjaan_ibu',
            'no_telp_wali',
            'hubungan_wali',
            'alamat_wali',
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
