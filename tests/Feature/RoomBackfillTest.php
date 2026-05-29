<?php

use App\Actions\Room\BackfillRoomsFromSantriRoomNames;
use App\Models\Room;
use App\Models\Santri;
use App\Models\Tenant;

test('room backfill creates master rooms from legacy santri room names and links room ids')
    ->skip('Column room_name has been dropped');
