<?php

namespace App\Imports;

use App\Models\Room;
use App\Models\Santri;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Throwable;

class SantriImport implements WithHeadingRow, SkipsEmptyRows
{
    use Importable;

    protected int $tenantId;

    protected int $createdBy;

    protected array $processed = [];

    protected Collection $errors;

    protected Collection $validRows;

    protected array $roomCache = [];

    public function __construct(int $tenantId, int $createdBy)
    {
        $this->tenantId = $tenantId;
        $this->createdBy = $createdBy;
        $this->errors = collect();
        $this->validRows = collect();
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function preview(Collection $rows): array
    {
        $this->processed = [];
        $this->errors = collect();
        $this->validRows = collect();

        foreach ($rows as $index => $row) {
            $this->processRow($row, $index, false);
        }

        return [
            'valid_rows' => $this->validRows,
            'error_rows' => $this->errors,
            'total' => count($rows),
            'valid_count' => $this->validRows->count(),
            'error_count' => $this->errors->count(),
        ];
    }

    public function import(Collection $rows): array
    {
        $this->processed = [];
        $this->errors = collect();
        $this->validRows = collect();

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                $this->processRow($row, $index, true);
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();

            throw $e;
        }

        return [
            'success_rows' => $this->validRows->count(),
            'failed_rows' => $this->errors->count(),
            'errors' => $this->errors,
            'total' => count($rows),
        ];
    }

    protected function processRow(Collection|array $row, int $index, bool $save): void
    {
        $row = $row instanceof Collection ? $row : collect($row);
        $mapped = $this->mapColumns($row);
        $rowNumber = $index + 2;

        $validation = $this->validateRow($mapped);

        if ($validation->fails()) {
            $this->errors->push([
                'row' => $rowNumber,
                'data' => $mapped,
                'errors' => $validation->errors()->all(),
            ]);

            return;
        }

        if ($save) {
            $santri = $this->createSantri($mapped);
            $this->validRows->push([
                'row' => $rowNumber,
                'data' => $mapped,
                'santri_id' => $santri->id,
            ]);
        } else {
            $this->validRows->push([
                'row' => $rowNumber,
                'data' => $mapped,
            ]);
        }
    }

    protected function mapColumns(Collection $row): array
    {
        $row = $row->mapWithKeys(fn ($value, $key) => [strtolower(trim((string) $key)) => $value]);

        $gender = $row['jenis_kelamin'] ?? $row['gender'] ?? '';
        $gender = match (strtolower(trim((string) $gender))) {
            'laki-laki', 'laki', 'male', 'l' => Santri::GENDER_MALE,
            'perempuan', 'female', 'p', 'cewek' => Santri::GENDER_FEMALE,
            default => $gender,
        };

        $status = $row['status'] ?? 'active';
        $status = match (strtolower(trim((string) $status))) {
            'aktif', 'active', 'a' => Santri::STATUS_ACTIVE,
            'cuti', 'leave', 'c' => Santri::STATUS_LEAVE,
            'keluar', 'exited', 'k' => Santri::STATUS_EXITED,
            'alumni', 'lulus', 'al' => Santri::STATUS_ALUMNI,
            default => Santri::STATUS_ACTIVE,
        };

        $birthDate = $row['tanggal_lahir'] ?? $row['birth_date'] ?? '';
        $entryDate = $row['tanggal_masuk'] ?? $row['entry_date'] ?? date('Y-m-d');
        $entryYear = $row['angkatan'] ?? $row['entry_year'] ?? date('Y');
        $guardianPhone = $row['no_telp_wali'] ?? $row['guardian_phone_number'] ?? '';

        $roomName = $row['kamar'] ?? $row['room'] ?? $row['room_name'] ?? '';

        return [
            'nis' => (string) ($row['nis'] ?? ''),
            'nisn' => (string) ($row['nisn'] ?? ''),
            'full_name' => $row['nama'] ?? $row['full_name'] ?? '',
            'gender' => $gender,
            'birth_place' => $row['tempat_lahir'] ?? $row['birth_place'] ?? '',
            'birth_date' => $birthDate,
            'address' => $row['alamat'] ?? $row['address'] ?? '',
            'father_name' => $row['nama_ayah'] ?? $row['father_name'] ?? '',
            'mother_name' => $row['nama_ibu'] ?? $row['mother_name'] ?? '',
            'guardian_name' => $row['nama_wali'] ?? $row['guardian_name'] ?? $row['father_name'] ?? '',
            'guardian_phone_number' => $guardianPhone,
            'emergency_contact' => $row['kontak_darurat'] ?? $row['emergency_contact'] ?? $guardianPhone,
            'entry_date' => $entryDate,
            'entry_year' => (int) $entryYear,
            'status' => $status,
            'room_name' => $roomName,
            'notes' => $row['catatan'] ?? $row['notes'] ?? '',
        ];
    }

    protected function validateRow(array $data): \Illuminate\Validation\Validator
    {
        $rules = [
            'nis' => ['required', 'string', 'max:50'],
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'string'],
            'birth_place' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date'],
            'address' => ['required', 'string'],
            'father_name' => ['required', 'string', 'max:255'],
            'mother_name' => ['required', 'string', 'max:255'],
            'entry_date' => ['required', 'date'],
            'entry_year' => ['required', 'integer', 'min:1900', 'max:'.now()->year],
            'status' => ['required', 'string'],
        ];

        return Validator::make($data, $rules, [
            'nis.required' => 'NIS wajib diisi.',
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'gender.required' => 'Jenis kelamin wajib diisi.',
            'birth_place.required' => 'Tempat lahir wajib diisi.',
            'birth_date.required' => 'Tanggal lahir wajib diisi.',
            'birth_date.date' => 'Tanggal lahir tidak valid (format: YYYY-MM-DD).',
            'address.required' => 'Alamat wajib diisi.',
            'father_name.required' => 'Nama ayah wajib diisi.',
            'mother_name.required' => 'Nama ibu wajib diisi.',
            'entry_date.required' => 'Tanggal masuk wajib diisi.',
            'entry_date.date' => 'Tanggal masuk tidak valid (format: YYYY-MM-DD).',
            'entry_year.required' => 'Angkatan / tahun masuk wajib diisi.',
            'entry_year.integer' => 'Angkatan / tahun masuk harus berupa angka.',
            'entry_year.min' => 'Angkatan tidak valid.',
            'entry_year.max' => 'Angkatan tidak boleh melebihi tahun ini.',
        ]);
    }

    protected function createSantri(array $data): Santri
    {
        $roomId = null;
        if (filled($data['room_name'] ?? '')) {
            $room = $this->resolveRoom($data['room_name']);
            if ($room) {
                $roomId = $room->id;
            }
        }

        $santri = Santri::query()->create([
            'tenant_id' => $this->tenantId,
            'nis' => $data['nis'],
            'nisn' => $data['nisn'] ?: null,
            'full_name' => $data['full_name'],
            'gender' => $data['gender'],
            'birth_place' => $data['birth_place'],
            'birth_date' => $data['birth_date'],
            'address' => $data['address'],
            'father_name' => $data['father_name'],
            'mother_name' => $data['mother_name'],
            'guardian_name' => $data['guardian_name'] ?: null,
            'guardian_phone_number' => $data['guardian_phone_number'] ?: null,
            'emergency_contact' => $data['emergency_contact'] ?: '-',
            'entry_date' => $data['entry_date'],
            'entry_year' => (int) $data['entry_year'],
            'room_id' => $roomId,
            'notes' => $data['notes'] ?: null,
            'status' => $data['status'],
            'created_by' => $this->createdBy,
        ]);

        return $santri;
    }

    protected function resolveRoom(string $name): ?Room
    {
        $name = trim(preg_replace('/\s+/', ' ', $name));

        if (isset($this->roomCache[$name])) {
            return $this->roomCache[$name];
        }

        $room = Room::query()
            ->withoutTenantScope()
            ->where('tenant_id', $this->tenantId)
            ->where('name', $name)
            ->first();

        $this->roomCache[$name] = $room;

        return $room;
    }

    public function getErrors(): Collection
    {
        return $this->errors;
    }

    public function getValidRows(): Collection
    {
        return $this->validRows;
    }
}
