<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class RoomCatalog
{
    /**
     * Resolve public room tariffs from the database when staff have overridden them.
     * Keep the static config as the initial default and never mutate config globally.
     */
    public static function all(): array
    {
        $rooms = config('hotel.rooms', []);
        $rates = DB::table('room_rates')->get()->keyBy('room_type');

        foreach ($rooms as $type => &$room) {
            $override = $rates->get($type);
            if (! $override) {
                continue;
            }
            $room['p1'] = [(int) $override->one_room, (int) $override->one_breakfast];
            $room['p2'] = [(int) $override->two_room, (int) $override->two_breakfast];
            // Former prices become indicative only: never display a fictional markdown
            // after a manager updates the live prices.
            $room['was1'] = $room['p1'];
            $room['was2'] = $room['p2'];
            $room['w1'] = $room['p1'][0];
            $room['w2'] = $room['p2'][0];
            $room['rates_overridden'] = true;
        }
        unset($room);

        return $rooms;
    }
}
