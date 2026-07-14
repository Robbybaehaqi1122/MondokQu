<?php

namespace App\Exports;

use App\Models\KesehatanImunisasi;
use App\Models\KesehatanObat;
use App\Models\KesehatanPemeriksaan;
use App\Models\Santri;
use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KesehatanLaporanExcelExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        protected ?User $currentUser,
        protected string $dateFrom = '',
        protected string $dateTo = '',
    ) {}

    public function sheets(): array
    {
        return [
            new PemeriksaanSheet($this->currentUser, $this->dateFrom, $this->dateTo),
            new KondisiKhususSheet($this->currentUser),
            new ImunisasiSheet($this->currentUser, $this->dateFrom, $this->dateTo),
            new ObatExpiredSheet($this->currentUser),
        ];
    }

    public function filename(): string
    {
        return 'laporan-kesehatan-'.now()->format('Ymd-His').'.xlsx';
    }
}

class PemeriksaanSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        protected ?User $currentUser,
        protected string $dateFrom,
        protected string $dateTo,
    ) {}

    public function title(): string
    {
        return 'Pemeriksaan';
    }

    public function collection()
    {
        return KesehatanPemeriksaan::query()
            ->visibleTo($this->currentUser)
            ->with(['santri', 'pencatat', 'rujukan'])
            ->whereBetween('tanggal_pemeriksaan', [$this->dateFrom, $this->dateTo])
            ->orderBy('tanggal_pemeriksaan', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return ['Tanggal', 'Santri', 'NIS', 'Keluhan', 'Diagnosis', 'Tindakan', 'Rujukan', 'Dicatat Oleh'];
    }

    public function map($pemeriksaan): array
    {
        return [
            $pemeriksaan->tanggal_pemeriksaan?->translatedFormat('d M Y'),
            $pemeriksaan->santri?->full_name ?? '-',
            $pemeriksaan->santri?->nis ?? '-',
            $pemeriksaan->keluhan,
            $pemeriksaan->diagnosis ?: '-',
            $pemeriksaan->tindakan ?: '-',
            $pemeriksaan->rujukan ? 'Ya ('.($pemeriksaan->rujukan->tempat_rujukan ?? '-').')' : 'Tidak',
            $pemeriksaan->pencatat?->name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

class KondisiKhususSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        protected ?User $currentUser,
    ) {}

    public function title(): string
    {
        return 'Kondisi Khusus';
    }

    public function collection()
    {
        return Santri::query()
            ->visibleTo($this->currentUser)
            ->whereHas('rekamMedis', function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('riwayat_penyakit')
                        ->where('riwayat_penyakit', '!=', '');
                })->orWhere(function ($q) {
                    $q->whereNotNull('alergi_obat')
                        ->where('alergi_obat', '!=', '');
                })->orWhere(function ($q) {
                    $q->whereNotNull('alergi_makanan')
                        ->where('alergi_makanan', '!=', '');
                });
            })
            ->with('rekamMedis')
            ->orderBy('full_name')
            ->get();
    }

    public function headings(): array
    {
        return ['Nama Santri', 'NIS', 'Golongan Darah', 'Riwayat Penyakit', 'Alergi Obat', 'Alergi Makanan'];
    }

    public function map($santri): array
    {
        return [
            $santri->full_name,
            $santri->nis,
            $santri->rekamMedis?->golongan_darah ?: '-',
            $santri->rekamMedis?->riwayat_penyakit ?: '-',
            $santri->rekamMedis?->alergi_obat ?: '-',
            $santri->rekamMedis?->alergi_makanan ?: '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

class ImunisasiSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        protected ?User $currentUser,
        protected string $dateFrom,
        protected string $dateTo,
    ) {}

    public function title(): string
    {
        return 'Imunisasi';
    }

    public function collection()
    {
        return KesehatanImunisasi::query()
            ->visibleTo($this->currentUser)
            ->with('santri')
            ->whereBetween('created_at', [$this->dateFrom, $this->dateTo])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return ['Santri', 'NIS', 'Jenis Imunisasi', 'Tanggal', 'Status', 'Catatan'];
    }

    public function map($imunisasi): array
    {
        return [
            $imunisasi->santri?->full_name ?? '-',
            $imunisasi->santri?->nis ?? '-',
            $imunisasi->jenis_imunisasi,
            $imunisasi->tanggal?->translatedFormat('d M Y') ?? '-',
            ucfirst($imunisasi->status),
            $imunisasi->catatan ?: '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

class ObatExpiredSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        protected ?User $currentUser,
    ) {}

    public function title(): string
    {
        return 'Obat Expired';
    }

    public function collection()
    {
        return KesehatanObat::query()
            ->visibleTo($this->currentUser)
            ->whereNotNull('expired_date')
            ->where('expired_date', '<=', now()->addMonth())
            ->orderBy('expired_date')
            ->get();
    }

    public function headings(): array
    {
        return ['Nama Obat', 'Jenis', 'Stok', 'Satuan', 'Tanggal Expired', 'Status'];
    }

    public function map($obat): array
    {
        return [
            $obat->nama_obat,
            $obat->jenis ?: '-',
            $obat->stok,
            $obat->satuan,
            $obat->expired_date?->translatedFormat('d M Y') ?? '-',
            $obat->expired_date?->isPast() ? 'Expired' : 'Akan Expired',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
