<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RoomCatalog
{
    /**
     * Resolve public room tariffs from the database when staff have overridden them.
     * Keep the static config as the initial default and never mutate config globally.
     */
    public static function all(bool $strict = false): array
    {
        $rooms = config('hotel.rooms', []);

        // Pricing overrides are optional for public page rendering. An unavailable
        // DB must not take down the entire hotel website during an outage or a
        // rolling deployment. Writes and server-side booking totals use strict=true.
        try {
            if (!Schema::hasTable('room_rates')) {
                return $rooms;
            }

            $rates = DB::table('room_rates')->get()->keyBy('room_type');
        } catch (\Throwable $exception) {
            if ($strict) {
                throw $exception;
            }

            report($exception);

            return $rooms;
        }

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
