<?php

namespace App\Services;

use App\Models\Backup;
use App\Models\Tenant;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TenantBackupService
{
    public function getTenantTables(): array
    {
        $tables = DB::select("
            SELECT DISTINCT TABLE_NAME
            FROM information_schema.COLUMNS
            WHERE COLUMN_NAME = 'tenant_id'
                AND TABLE_SCHEMA = ?
                AND TABLE_NAME NOT LIKE 'view_%'
            ORDER BY TABLE_NAME
        ", [DB::connection()->getDatabaseName()]);

        return array_map(fn ($row) => $row->TABLE_NAME, $tables);
    }

    public function getTableColumns(string $table): array
    {
        return DB::connection()->getSchemaBuilder()->getColumnListing($table);
    }

    public function generateBackupSql(Tenant $tenant, Closure $onProgress = null): array
    {
        $allTables = $this->getTenantTables();
        $totalTables = count($allTables);

        $sql = "-- Mondok Qu Database Backup\n";
        $sql .= "-- Tenant: {$tenant->name} (ID: {$tenant->id})\n";
        $sql .= "-- Generated: " . now()->toDateTimeString() . "\n";
        $sql .= "-- Database: " . DB::connection()->getDatabaseName() . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $sql .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n";
        $sql .= "START TRANSACTION;\n\n";

        $totalRows = 0;
        $processedWithData = 0;

        foreach ($allTables as $index => $table) {
            $stepProgress = $totalTables > 0 ? (int) round(($index / $totalTables) * 100) : 0;

            if ($onProgress) {
                $onProgress($stepProgress, $table);
            }

            $columns = $this->getTableColumns($table);

            $rows = DB::table($table)
                ->where('tenant_id', $tenant->id)
                ->get();

            if ($rows->isEmpty()) {
                continue;
            }

            $processedWithData++;

            $totalRows += $rows->count();

            $columnList = '`' . implode('`, `', $columns) . '`';

            $chunks = $rows->chunk(100);

            foreach ($chunks as $chunk) {
                $values = [];

                foreach ($chunk as $row) {
                    $escapedValues = [];

                    foreach ($columns as $col) {
                        $val = $row->$col;

                        if ($val === null) {
                            $escapedValues[] = 'NULL';
                        } elseif (is_int($val) || is_float($val)) {
                            $escapedValues[] = $val;
                        } else {
                            $escapedValues[] = DB::connection()->getPdo()->quote($val);
                        }
                    }

                    $values[] = '(' . implode(', ', $escapedValues) . ')';
                }

                $sql .= "INSERT INTO `{$table}` ({$columnList}) VALUES\n";
                $sql .= implode(",\n", $values) . ";\n\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        $sql .= "COMMIT;\n";

        if ($onProgress) {
            $onProgress(100, null);
        }

        return [
            'content' => $sql,
            'tables_count' => $totalTables,
            'total_rows' => $totalRows,
        ];
    }

    public function storeBackup(Tenant $tenant, User $user, string $type = Backup::TYPE_MANUAL): Backup
    {
        $backup = Backup::query()->create([
            'tenant_id' => $tenant->id,
            'created_by' => $user->id,
            'filename' => '',
            'disk' => config('backups.disk', 'local'),
            'type' => $type,
            'status' => Backup::STATUS_PROCESSING,
        ]);

        try {
            $result = $this->generateBackupSql($tenant, function (int $progress, ?string $table) use ($backup) {
                $backup->markProgress($progress, $table);
            });
            $content = $result['content'];
            $tablesCount = $result['tables_count'];
            $totalRows = $result['total_rows'];

            $compressed = gzencode($content, 9);

            $date = now()->format('Y-m-d_H-i-s');
            $filename = "backup_{$tenant->id}_{$date}.sql.gz";

            $directory = "backups/tenant_{$tenant->id}";

            Storage::disk(config('backups.disk', 'local'))->makeDirectory($directory);
            Storage::disk(config('backups.disk', 'local'))->put(
                "{$directory}/{$filename}",
                $compressed
            );

            $backup->update([
                'filename' => $filename,
                'size_bytes' => strlen($compressed),
                'tables_count' => $tablesCount,
                'total_rows' => $totalRows,
                'status' => Backup::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            return $backup->fresh();
        } catch (\Throwable $e) {
            $backup->markFailed($e->getMessage());

            throw $e;
        }
    }

    public function restoreBackup(Backup $backup): array
    {
        if (! $backup->fileExists()) {
            throw new \RuntimeException("File backup tidak ditemukan: {$backup->filename}");
        }

        $compressed = Storage::disk($backup->disk)->get($backup->getFilePath());
        $content = gzdecode($compressed);

        $statements = $this->parseSqlStatements($content);
        $restored = 0;

        DB::beginTransaction();

        try {
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if ($statement === '') {
                    continue;
                }

                if (str_starts_with($statement, 'INSERT INTO')) {
                    preg_match("/INSERT INTO `(\w+)`/", $statement, $matches);
                    if (isset($matches[1])) {
                        $table = $matches[1];

                        if ($table !== 'tenants') {
                            $tenantColumn = in_array('tenant_id', $this->getTableColumns($table))
                                ? 'tenant_id'
                                : null;

                            if ($tenantColumn) {
                                DB::table($table)
                                    ->where('tenant_id', $backup->tenant_id)
                                    ->delete();
                            }
                        }

                        DB::unprepared($statement);
                        $restored++;
                    }
                }
            }

            DB::commit();

            return [
                'success' => true,
                'statements_executed' => $restored,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    private function parseSqlStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = null;
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];

            if ($inString) {
                $current .= $char;

                if ($char === '\\' && $i + 1 < $length) {
                    $i++;
                    $current .= $sql[$i];
                    continue;
                }

                if ($char === $stringChar) {
                    $inString = false;
                }

                continue;
            }

            if ($char === "'" || $char === '"') {
                $inString = true;
                $stringChar = $char;
                $current .= $char;
                continue;
            }

            if ($char === ';') {
                $current = trim($current);
                if ($current !== '') {
                    $statements[] = $current;
                }
                $current = '';
                continue;
            }

            if ($char === '-' && $i + 1 < $length && $sql[$i + 1] === '-') {
                while ($i < $length && $sql[$i] !== "\n") {
                    $i++;
                }
                continue;
            }

            $current .= $char;
        }

        $current = trim($current);
        if ($current !== '') {
            $statements[] = $current;
        }

        return $statements;
    }

    public function pruneOldBackups(int $days = 30): int
    {
        return Backup::pruneOlderThan($days);
    }
}
