<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Hotel services managed in the admin panel.
 *
 * - amenity: included things shown on the site (free parking, Wi‑Fi …)
 * - extra:   paid add-ons a guest can tick when booking (airport transfer …),
 *            priced on the server like the rooms.
 */
class Services
{
    public const UNITS = [
        'stay' => 'per booking',
        'night' => 'per night',
        'guest' => 'per guest',
        'guest_night' => 'per guest per night',
        'room_night' => 'per room per night',
    ];

    public const ICONS = [
        'leaf' => 'Leaf', 'coffee' => 'Coffee', 'parking' => 'Parking', 'wifi' => 'Wi‑Fi', 'bell' => 'Front desk',
        'family' => 'Family', 'snow' => 'Air conditioning', 'car' => 'Car / transfer', 'bed' => 'Bed', 'clock' => 'Clock',
        'star' => 'Star', 'gift' => 'Gift', 'paw' => 'Pets', 'wine' => 'Wine', 'map' => 'Tour',
    ];

    /** Built-in amenities, used until the database is available. */
    public const DEFAULT_AMENITIES = [
        ['Buffet breakfast', 'Every morning, served in the lobby.', 'coffee'],
        ['Free parking', 'Rare in the old town. Free for every guest.', 'parking'],
        ['Free Wi‑Fi', 'In every room and on the terrace.', 'wifi'],
        ['24-hour front desk', "Someone's always here.", 'bell'],
        ['Garden & terrace', 'Green below, the whole city above.', 'leaf'],
        ['Family rooms', 'Triples with a balcony.', 'family'],
        ['Air conditioning', 'In every room.', 'snow'],
    ];

    /** @return array<int, object> active services of one kind, in order */
    public static function active(string $kind): array
    {
        try {
            if (Schema::hasTable('hotel_services')) {
                return DB::table('hotel_services')->where('kind', $kind)->where('is_active', true)
                    ->when($kind === 'extra', fn ($q) => $q->whereNotNull('price')->whereNotNull('price_unit'))
                    ->orderBy('sort')->orderBy('id')->get()->all();
            }
        } catch (Throwable $e) {
            report($e);
        }

        return $kind === 'amenity'
            ? array_map(fn ($a) => (object) ['id' => null, 'name' => $a[0], 'description' => $a[1], 'icon' => $a[2]], self::DEFAULT_AMENITIES)
            : [];
    }

    /** Extras for the booking page (only what the browser needs). */
    public static function extrasForBooking(): array
    {
        return array_map(fn ($e) => [
            'id' => (int) $e->id, 'name' => $e->name, 'description' => $e->description,
            'price' => (int) $e->price, 'unit' => $e->price_unit, 'unitLabel' => self::UNITS[$e->price_unit] ?? '',
        ], self::active('extra'));
    }

    public static function amount(int $price, string $unit, int $nights, int $guests, int $rooms): int
    {
        return $price * match ($unit) {
            'night' => $nights,
            'guest' => $guests,
            'guest_night' => $guests * $nights,
            'room_night' => $rooms * $nights,
            default => 1,
        };
    }

    /**
     * Price the extras a guest picked. Unknown or switched-off extras are refused.
     *
     * @param  int[]  $ids
     * @return array{lines: array<int, array{id:int,name:string,price:int,unit:string,amount:int}>, total:int}
     */
    public static function quote(array $ids, int $nights, int $guests, int $rooms): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (! $ids) {
            return ['lines' => [], 'total' => 0];
        }
        $available = collect(self::active('extra'))->keyBy('id');
        $lines = [];
        foreach ($ids as $id) {
            $e = $available->get($id);
            if (! $e) {
                throw ValidationException::withMessages(['extras' => 'One of the extras you picked is no longer available. Please check your booking.']);
            }
            $lines[] = [
                'id' => (int) $e->id, 'name' => $e->name, 'price' => (int) $e->price, 'unit' => $e->price_unit,
                'amount' => self::amount((int) $e->price, $e->price_unit, $nights, $guests, $rooms),
            ];
        }

        return ['lines' => $lines, 'total' => array_sum(array_column($lines, 'amount'))];
    }

    /** Small inline SVG for an icon key (stroke icons, 24×24). */
    public static function icon(?string $key): string
    {
        $paths = [
            'leaf' => '<path d="M12 21V11"/><path d="M12 11c0-4 3-7 7-7 0 4-3 7-7 7z"/><path d="M12 14c0-3-2-5-6-5 0 3 2 5 6 5z"/>',
            'coffee' => '<path d="M4 9h13v5a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5z"/><path d="M17 11h1.5a2.5 2.5 0 0 1 0 5H17"/><path d="M8 3v3M12 3v3"/>',
            'parking' => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 17V8h4a3 3 0 0 1 0 6H9"/>',
            'wifi' => '<path d="M2 9a15 15 0 0 1 20 0"/><path d="M5 12.5a10 10 0 0 1 14 0"/><path d="M8.5 16a5 5 0 0 1 7 0"/><circle cx="12" cy="19" r="1"/>',
            'bell' => '<path d="M5 18h14M7 18V11a5 5 0 0 1 10 0v7"/><path d="M12 4v2M10 21h4"/>',
            'family' => '<circle cx="8" cy="7" r="3"/><circle cx="17" cy="9" r="2"/><path d="M2 21a6 6 0 0 1 12 0"/><path d="M14 21a4 4 0 0 1 8 0"/>',
            'snow' => '<path d="M12 2v20M4 6l16 12M20 6L4 18"/>',
            'car' => '<path d="M5 17h14v-5l-2-5H7l-2 5z"/><circle cx="8" cy="17" r="2"/><circle cx="16" cy="17" r="2"/>',
            'bed' => '<path d="M3 18V8M3 14h18v4M21 14v-2a3 3 0 0 0-3-3h-7v5"/>',
            'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'star' => '<path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/>',
            'gift' => '<rect x="3" y="8" width="18" height="13" rx="1"/><path d="M12 8v13M3 12h18"/><path d="M12 8c-2-4-6-4-6-1s6 1 6 1zM12 8c2-4 6-4 6-1s-6 1-6 1z"/>',
            'paw' => '<circle cx="7" cy="9" r="2"/><circle cx="12" cy="6" r="2"/><circle cx="17" cy="9" r="2"/><path d="M8 17c0-3 2-5 4-5s4 2 4 5-2 3-4 3-4 0-4-3z"/>',
            'wine' => '<path d="M8 3h8l-1 7a3 3 0 0 1-6 0z"/><path d="M12 13v7M8 21h8"/>',
            'map' => '<path d="M3 6l6-2 6 2 6-2v14l-6 2-6-2-6 2z"/><path d="M9 4v14M15 6v14"/>',
        ];

        return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
            .($paths[$key] ?? $paths['leaf']).'</svg>';
    }
}
