<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

class AttendanceService
{
    /**
     * @param  array<int, string>  $issueStatuses
     */
    public function attentionSantris(Builder $query, array $issueStatuses, string $alias = 'attention_santris', int $limit = 8): Collection
    {
        return $query
            ->reorder()
            ->whereIn('attendance_records.status', $issueStatuses)
            ->join("santris as {$alias}", function (JoinClause $join) use ($alias): void {
                $join
                    ->on('attendance_records.santri_id', '=', "{$alias}.id")
                    ->on('attendance_records.tenant_id', '=', "{$alias}.tenant_id");
            })
            ->select("{$alias}.id", "{$alias}.full_name", "{$alias}.nis")
            ->selectRaw('COUNT(*) as issue_total')
            ->selectRaw('SUM(CASE WHEN attendance_records.status = ? THEN 1 ELSE 0 END) as permission_count', [AttendanceRecord::STATUS_PERMISSION])
            ->selectRaw('SUM(CASE WHEN attendance_records.status = ? THEN 1 ELSE 0 END) as sick_count', [AttendanceRecord::STATUS_SICK])
            ->selectRaw('SUM(CASE WHEN attendance_records.status = ? THEN 1 ELSE 0 END) as absent_count', [AttendanceRecord::STATUS_ABSENT])
            ->selectRaw('SUM(CASE WHEN attendance_records.status = ? THEN 1 ELSE 0 END) as late_count', [AttendanceRecord::STATUS_LATE])
            ->groupBy("{$alias}.id", "{$alias}.full_name", "{$alias}.nis")
            ->orderByDesc('issue_total')
            ->orderByDesc('absent_count')
            ->limit($limit)
            ->get();
    }
}
