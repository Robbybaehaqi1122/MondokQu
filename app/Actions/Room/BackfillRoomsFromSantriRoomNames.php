<?php

namespace App\Actions\Room;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BackfillRoomsFromSantriRoomNames
{
    /**
     * Create room masters from legacy santri.room_name values and link santri.room_id.
     *
     * @return array{rooms_created: int, rooms_reused: int, santris_linked: int}
     */
    public function handle(?int $tenantId = null): array
    {
        return DB::transaction(function () use ($tenantId): array {
            $roomIdsByKey = [];
            $created = 0;
            $reused = 0;

            $legacyRoomNames = DB::table('santris')
                ->select(['tenant_id', 'room_name'])
                ->whereNull('room_id')
                ->whereNotNull('tenant_id')
                ->whereNotNull('room_name')
                ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                ->orderBy('tenant_id')
                ->get()
                ->map(fn (object $row): array => [
                    'tenant_id' => (int) $row->tenant_id,
                    'name' => trim((string) $row->room_name),
                ])
                ->filter(fn (array $room): bool => $room['tenant_id'] > 0 && $room['name'] !== '')
                ->unique(fn (array $room): string => $this->roomKey($room['tenant_id'], $room['name']))
                ->values();

            foreach ($legacyRoomNames as $legacyRoom) {
                $roomKey = $this->roomKey($legacyRoom['tenant_id'], $legacyRoom['name']);
                $room = DB::table('rooms')
                    ->where('tenant_id', $legacyRoom['tenant_id'])
                    ->where('name', $legacyRoom['name'])
                    ->first(['id']);

                if ($room) {
                    $roomIdsByKey[$roomKey] = (int) $room->id;
                    $reused++;

                    continue;
                }

                $roomIdsByKey[$roomKey] = (int) DB::table('rooms')->insertGetId([
                    'tenant_id' => $legacyRoom['tenant_id'],
                    'name' => $legacyRoom['name'],
                    'capacity' => null,
                    'status' => 'active',
                    'description' => null,
                    'created_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $created++;
            }

            $linked = 0;

            DB::table('santris')
                ->select(['id', 'tenant_id', 'room_name'])
                ->whereNull('room_id')
                ->whereNotNull('tenant_id')
                ->whereNotNull('room_name')
                ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                ->orderBy('id')
                ->chunkById(500, function (Collection $santris) use ($roomIdsByKey, &$linked): void {
                    foreach ($santris as $santri) {
                        $roomName = trim((string) $santri->room_name);

                        if ($roomName === '') {
                            continue;
                        }

                        $roomId = $roomIdsByKey[$this->roomKey((int) $santri->tenant_id, $roomName)] ?? null;

                        if (! $roomId) {
                            continue;
                        }

                        $linked += DB::table('santris')
                            ->where('id', $santri->id)
                            ->whereNull('room_id')
                            ->update(['room_id' => $roomId]);
                    }
                }, 'id');

            return [
                'rooms_created' => $created,
                'rooms_reused' => $reused,
                'santris_linked' => $linked,
            ];
        });
    }

    protected function roomKey(int $tenantId, string $roomName): string
    {
        return $tenantId.'|'.Str::lower($roomName);
    }
}
