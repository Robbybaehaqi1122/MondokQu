<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    /**
     * Persist an activity entry.
     */
    public function log(
        string $action,
        ?User $actor = null,
        ?Model $target = null,
        ?string $description = null,
        array $properties = [],
        ?string $ipAddress = null
    ): ActivityLog {
        return ActivityLog::query()->create([
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name ?? ($properties['actor_name'] ?? 'System'),
            'action' => $action,
            'description' => $description,
            'target_type' => $target ? $target::class : null,
            'target_id' => $target?->getKey(),
            'target_name' => $this->resolveTargetName($target, $properties),
            'ip_address' => $ipAddress ?? request()?->ip(),
            'properties' => $properties ?: null,
        ]);
    }

    /**
     * Resolve a human-friendly target name.
     */
    protected function resolveTargetName(?Model $target, array $properties): ?string
    {
        if (isset($properties['target_name'])) {
            return (string) $properties['target_name'];
        }

        if (! $target) {
            return null;
        }

        if ($target instanceof User) {
            return $target->name.' (@'.$target->username.')';
        }

        return $target->getAttribute('name')
            ?? class_basename($target).' #'.$target->getKey();
    }
}
