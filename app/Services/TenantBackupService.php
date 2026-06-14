<?php

namespace App\Services;

use App\Models\Backup;
use App\Models\Tenant;
use App\Models\User;
use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    public function generateBackupSql(Tenant $tenant, ?Closure $onProgress = null): array
    {
        $allTables = $this->getTenantTables();
        $totalTables = count($allTables);

        $sql = "-- Mondok Qu Database Backup\n";
        $sql .= "-- Tenant: {$tenant->name} (ID: {$tenant->id})\n";
        $sql .= '-- Generated: '.now()->toDateTimeString()."\n";
        $sql .= '-- Database: '.DB::connection()->getDatabaseName()."\n\n";
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

            $columnList = '`'.implode('`, `', $columns).'`';

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

                    $values[] = '('.implode(', ', $escapedValues).')';
                }

                $sql .= "INSERT INTO `{$table}` ({$columnList}) VALUES\n";
                $sql .= implode(",\n", $values).";\n\n";
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

    public function storeBackup(Backup $backup, ?Closure $onProgress = null): Backup
    {
        ini_set('memory_limit', '-1');

        DB::table('backups')->where('id', $backup->id)->update([
            'status' => Backup::STATUS_PROCESSING,
            'progress' => 0,
        ]);

        $tenant = Tenant::find($backup->tenant_id);
        if (! $tenant) {
            DB::table('backups')->where('id', $backup->id)->update([
                'status' => Backup::STATUS_FAILED,
                'error_message' => 'Tenant tidak ditemukan.',
            ]);
            throw new \RuntimeException('Tenant tidak ditemukan: '.$backup->tenant_id);
        }

        try {
            $result = $this->generateBackupSql($tenant, function (int $progress, ?string $table) use ($backup, $onProgress) {
                DB::table('backups')->where('id', $backup->id)->update([
                    'progress' => min(max($progress, 0), 100),
                    'current_table' => $table,
                ]);

                if ($onProgress) {
                    $onProgress($progress, $table);
                }
            });
            $content = $result['content'];
            $tablesCount = $result['tables_count'];
            $totalRows = $result['total_rows'];

            $date = now()->format('Y-m-d_H-i-s');
            $filename = "backup_{$tenant->id}_{$date}.sql.gz";

            $directory = "backups/tenant_{$tenant->id}";
            Storage::disk(config('backups.disk', 'local'))->makeDirectory($directory);

            error_log('[Backup] SQL content size: '.strlen($content).' bytes, tables: '.$tablesCount.', rows: '.$totalRows);

            $compressed = gzencode($content, 9);
            unset($content);

            if ($compressed === false) {
                throw new \RuntimeException('Gagal mengompres file backup (gzencode gagal)');
            }

            $saved = Storage::disk(config('backups.disk', 'local'))->put(
                "{$directory}/{$filename}",
                $compressed
            );

            if (! $saved) {
                throw new \RuntimeException('Gagal menyimpan file backup ke storage');
            }

            $size = strlen($compressed);
            unset($compressed);

            DB::table('backups')->where('id', $backup->id)->update([
                'status' => Backup::STATUS_COMPLETED,
                'filename' => $filename,
                'size_bytes' => $size,
                'tables_count' => $tablesCount,
                'total_rows' => $totalRows,
                'progress' => 100,
                'completed_at' => now(),
                'error_message' => null,
            ]);

            return $backup->fresh();
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            error_log('[Backup] storeBackup failed: '.$msg);

            DB::table('backups')->where('id', $backup->id)->update([
                'status' => Backup::STATUS_FAILED,
                'error_message' => $msg,
            ]);

            throw $e;
        }
    }

    public function storeUploadedBackup(UploadedFile $file, Tenant $tenant, User $creator): Backup
    {
        $compressed = file_get_contents($file->getRealPath());
        $ext = $file->getClientOriginalExtension() === 'gz' ? '.sql.gz' : '.sql';

        $date = now()->format('Y-m-d_H-i-s');
        $filename = "upload_{$tenant->id}_{$date}{$ext}";
        $directory = "backups/tenant_{$tenant->id}";

        Storage::disk(config('backups.disk', 'local'))->makeDirectory($directory);
        Storage::disk(config('backups.disk', 'local'))->put(
            "{$directory}/{$filename}",
            $compressed
        );

        $backup = Backup::query()->create([
            'tenant_id' => $tenant->id,
            'created_by' => $creator->id,
            'filename' => $filename,
            'disk' => config('backups.disk', 'local'),
            'size_bytes' => strlen($compressed),
            'type' => Backup::TYPE_UPLOADED,
        ]);

        $backup->markCompleted(strlen($compressed), 0, 0);

        return $backup->fresh();
    }

    public function restoreBackup(Backup $backup, ?Closure $onProgress = null): array
    {
        if (! $backup->fileExists()) {
            throw new \RuntimeException("File backup tidak ditemukan: {$backup->filename}");
        }

        $compressed = Storage::disk($backup->disk)->get($backup->getFilePath());
        $content = gzdecode($compressed);

        $statements = $this->parseSqlStatements($content);

        $insertStatements = [];
        $setupStatements = [];
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement === '') {
                continue;
            }

            if (str_starts_with($statement, 'INSERT INTO')) {
                $insertStatements[] = $statement;
            } elseif (
                str_starts_with($statement, 'SET FOREIGN_KEY_CHECKS') ||
                str_starts_with($statement, 'SET SQL_MODE')
            ) {
                $setupStatements[] = $statement;
            }
        }

        $total = count($insertStatements);
        $restored = 0;

        Log::info('Starting restore', [
            'backup_id' => $backup->id,
            'statements_total' => count($statements),
            'insert_statements' => $total,
            'setup_statements' => count($setupStatements),
        ]);

        foreach ($setupStatements as $statement) {
            DB::unprepared($statement);
        }

        foreach ($insertStatements as $index => $statement) {
            preg_match("/INSERT INTO `(\w+)`/", $statement, $matches);
            if (! isset($matches[1])) {
                continue;
            }

            $table = $matches[1];

            if ($onProgress) {
                $progress = $total > 0 ? (int) round(($index + 1) / $total * 100) : 100;
                $onProgress($progress, $table);
            }

            try {
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
            } catch (\Throwable $e) {
                Log::error('Restore failed at statement', [
                    'backup_id' => $backup->id,
                    'index' => $index,
                    'table' => $table,
                    'statement' => substr($statement, 0, 200),
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }

        DB::unprepared('SET FOREIGN_KEY_CHECKS = 1');

        Log::info('Restore completed', [
            'backup_id' => $backup->id,
            'statements_executed' => $restored,
        ]);

        return [
            'success' => true,
            'statements_executed' => $restored,
        ];
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
