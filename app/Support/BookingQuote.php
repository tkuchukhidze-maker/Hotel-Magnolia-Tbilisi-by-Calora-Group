<?php

namespace App\Support;

/**
 * Prices a booking from config/hotel.php. The browser shows the same maths,
 * but this is the number that is stored and emailed.
 *
 * Rule: a room is priced at its 1-guest rate only when the whole booking is
 * one room for one guest; otherwise every room uses its standard (2-guest) rate.
 */
class BookingQuote
{
    /** @var array<int, array{room:string,name:string,qty:int,cap:int,nightly:int,subtotal:int}> */
    public array $lines = [];

    public int $capacity = 0;

    public int $rooms = 0;

    public int $nightly = 0;

    public int $total = 0;

    /**
     * @param  array<int, array{room:string, qty:int}>  $items
     */
    public function __construct(array $items, int $guests, string $rate, int $nights)
    {
        $config = RoomCatalog::all();
        $this->rooms = array_sum(array_column($items, 'qty'));
        $single = $this->rooms === 1 && $guests === 1;

        foreach ($items as $item) {
            $r = $config[$item['room']];
            $prices = $single ? $r['p1'] : $r['p2'];
            $perRoom = $rate === 'bf' ? $prices[1] : $prices[0];
            $sub = $perRoom * $item['qty'];

            $this->lines[] = [
                'room' => $item['room'],
                'name' => $r['name'],
                'qty' => $item['qty'],
                'cap' => $r['cap'],
                'nightly' => $perRoom,
                'subtotal' => $sub,
            ];
            $this->capacity += $r['cap'] * $item['qty'];
            $this->nightly += $sub;
        }

        $this->total = $this->nightly * $nights;
    }
}
