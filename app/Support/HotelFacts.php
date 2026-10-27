<?php

namespace App\Support;

/**
 * Small helpers so every page, the JSON-LD, /llms.txt and the Markdown
 * versions state the same facts from config/hotel.php, and nothing still
 * marked as a [placeholder] reaches guests or search engines.
 */
class HotelFacts
{
    /** Roughly 80 m a minute on foot; Tbilisi's old town is hilly, so we say "about". */
    private const METRES_PER_MINUTE = 80;

    public static function filled(mixed $value): bool
    {
        if (is_string($value)) {
            return trim($value) !== '' && ! str_contains($value, '[');
        }

        return $value !== null && $value !== [];
    }

    /** "950 m" or "1.6 km". */
    public static function distance(int $metres): string
    {
        if ($metres < 1000) {
            return $metres.' m';
        }

        return rtrim(rtrim(number_format($metres / 1000, 1, '.', ''), '0'), '.').' km';
    }

    /** Whole minutes on foot, at least 1. */
    public static function walkMinutes(int $metres): int
    {
        return max(1, (int) round($metres / self::METRES_PER_MINUTE));
    }

    /** Distances up to this far are shown with a walking time. */
    public static function isWalkable(int $metres): bool
    {
        return $metres <= 2500;
    }

    /**
     * Lowest current price for one guest and for two guests, plus the
     * matching price before the discount, from the live room catalogue.
     *
     * @return array{one:int, one_was:int, two:int, two_was:int}
     */
    public static function fromPrices(?array $rooms = null): array
    {
        $rooms = collect($rooms ?? RoomCatalog::all());
        $one = $rooms->sortBy(fn ($r) => $r['p1'][0])->first();
        $two = $rooms->filter(fn ($r) => $r['cap'] >= 2)->sortBy(fn ($r) => $r['p2'][0])->first();

        return [
            'one' => (int) $one['p1'][0],
            'one_was' => (int) ($one['w1'] ?? $one['p1'][0]),
            'two' => (int) $two['p2'][0],
            'two_was' => (int) ($two['w2'] ?? $two['p2'][0]),
        ];
    }

    /** Policies that are ready to show, label => value. */
    public static function policies(): array
    {
        $p = config('hotel.policies');
        $labels = [
            'check_in' => 'Check-in', 'check_out' => 'Check-out', 'cancellation' => 'Cancellation',
            'payment' => 'Payment', 'breakfast' => 'Breakfast', 'parking' => 'Parking',
            'children' => 'Children', 'pets' => 'Pets',
        ];

        $out = [];
        foreach ($labels as $key => $label) {
            if (self::filled($p[$key] ?? null)) {
                $out[$label] = $p[$key];
            }
        }

        return $out;
    }

    /** Parking details that are ready to show. */
    public static function parking(): array
    {
        return array_values(array_filter(
            config('hotel.arrival.parking', []),
            fn ($v) => self::filled($v)
        ));
    }

    /** Cancellation wording that never shows an unfilled deadline. */
    public static function cancellation(): string
    {
        $value = config('hotel.policies.cancellation');

        return self::filled($value) ? $value : 'Free cancellation';
    }

    /**
     * Every config value under hotel.* that still contains a [placeholder].
     *
     * @return array<string,string> dotted key => value
     */
    public static function placeholders(): array
    {
        $found = [];
        $walk = function ($value, string $path) use (&$walk, &$found) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $walk($v, $path === '' ? (string) $k : $path.'.'.$k);
                }
            } elseif (is_string($value) && str_contains($value, '[')) {
                $found['hotel.'.$path] = $value;
            }
        };
        $walk(config('hotel'), '');

        return $found;
    }
}
