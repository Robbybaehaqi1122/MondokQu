<?php

namespace App\Enums;

enum ExportFormat: string
{
    case CSV = 'csv';
    case XLSX = 'xlsx';
    case PDF = 'pdf';

    public function label(): string
    {
        return match ($this) {
            self::CSV => 'CSV',
            self::XLSX => 'Excel (.xlsx)',
            self::PDF => 'PDF',
        };
    }

    public function mimeType(): string
    {
        return match ($this) {
            self::CSV => 'text/csv; charset=UTF-8',
            self::XLSX => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            self::PDF => 'application/pdf',
        };
    }

    public function extension(): string
    {
        return $this->value;
    }

    public static function forSelect(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
