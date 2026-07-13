<?php

namespace App\Modules\Akademik\Controllers\Concerns;

trait HasSemesterOptions
{
    protected function availableSemesters(): array
    {
        $year = now()->year;
        $nextYear = $year + 1;

        return [
            "{$year}/{$nextYear} Ganjil",
            "{$year}/{$nextYear} Genap",
            (($year - 1)."/{$year} Ganjil"),
            (($year - 1)."/{$year} Genap"),
        ];
    }
}
