<?php

namespace App\Exports;

use App\Models\DataExport;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SantriCsvExport
{
    /**
     * Download filtered santri data as CSV.
     */
    public function download(?User $currentUser, string $search, string $status, string $gender): StreamedResponse
    {
        return response()->streamDownload(
            fn () => $this->write($currentUser, $search, $status, $gender),
            $this->filename(),
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    /**
     * Store filtered santri data as CSV for a background export.
     *
     * @return array{0: string, 1: string, 2: int}
     */
    public function store(DataExport $export, User $currentUser, array $filters): array
    {
        $search = (string) ($filters['q'] ?? '');
        $status = (string) ($filters['status'] ?? '');
        $gender = (string) ($filters['gender'] ?? '');
        $filename = $export->filename ?: $this->filename();
        $path = 'exports/'.$export->id.'/'.$filename;
        $disk = Storage::disk($export->disk);

        $disk->makeDirectory(dirname($path));

        $handle = fopen($disk->path($path), 'w');

        if ($handle === false) {
            throw new \RuntimeException('File export tidak dapat dibuat.');
        }

        $this->writeToHandle($handle, $this->query($currentUser, $search, $status, $gender));
        fclose($handle);

        return [$path, $filename, $this->rowCount($currentUser, $search, $status, $gender)];
    }

    public function filename(): string
    {
        return 'data-santri-'.now()->format('Ymd-His').'.csv';
    }

    public function rowCount(?User $currentUser, string $search, string $status, string $gender): int
    {
        return (clone $this->filteredQuery($currentUser, $search, $status, $gender))->count();
    }

    protected function write(?User $currentUser, string $search, string $status, string $gender): void
    {
        $handle = fopen('php://output', 'w');

        if ($handle === false) {
            return;
        }

        $this->writeToHandle($handle, $this->query($currentUser, $search, $status, $gender));
        fclose($handle);
    }

    protected function query(?User $currentUser, string $search, string $status, string $gender): Builder
    {
        return $this->filteredQuery($currentUser, $search, $status, $gender)
            ->with(['guardians', 'room'])
            ->orderBy('full_name');
    }

    protected function filteredQuery(?User $currentUser, string $search, string $status, string $gender): Builder
    {
        return Santri::query()
            ->withoutTenantScope()
            ->visibleTo($currentUser)
            ->withFilters($search, $status, $gender);
    }

    protected function writeToHandle($handle, Builder $query): void
    {
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, [
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
        ]);

        $query->chunk(500, function (Collection $santris) use ($handle): void {
            foreach ($santris as $santri) {
                fputcsv($handle, [
                    $santri->nis,
                    $santri->full_name,
                    $santri->genderLabel(),
                    $santri->statusLabel(),
                    $santri->birth_place,
                    $santri->birth_date?->toDateString(),
                    $santri->address,
                    $santri->father_name,
                    $santri->mother_name,
                    $santri->guardian_name,
                    $santri->guardian_phone_number,
                    $santri->guardians->pluck('name')->implode('; '),
                    $santri->emergency_contact,
                    $santri->entry_date?->toDateString(),
                    $santri->entry_year,
                    $santri->displayRoomName(''),
                    $santri->notes,
                ]);
            }
        });
    }
}
