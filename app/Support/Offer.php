<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Throwable;

/**
 * The book-direct offer (config('hotel.offer')).
 *
 * One rule everywhere: the offer runs until `ends` (Tbilisi time). While it runs,
 * the discounted prices in config are charged and the countdown is shown. Once it
 * has ended, RoomCatalog charges the full price and nothing on the site claims a
 * discount. The countdown therefore always tells the truth.
 */
class Offer
{
    public static function endsAt(): ?CarbonImmutable
    {
        $c = config('hotel.offer', []);
        if (empty($c['ends']) || str_contains((string) $c['ends'], '[')) {
            return null;
        }

        try {
            return CarbonImmutable::parse($c['ends'], $c['timezone'] ?? 'Asia/Tbilisi');
        } catch (Throwable) {
            return null;
        }
    }

    public static function running(): bool
    {
        $ends = self::endsAt();

        return $ends !== null && $ends->isFuture() && (int) config('hotel.offer.discount') > 0;
    }

    public static function discount(): int
    {
        return (int) config('hotel.offer.discount', 0);
    }

    /** Full (pre-offer) price for an offer price, e.g. 129 at 25% → 172. */
    public static function fullPrice(int $offerPrice): int
    {
        $d = self::discount();

        return $d > 0 && $d < 100 ? (int) round($offerPrice / (1 - $d / 100)) : $offerPrice;
    }

    /**
     * Everything the views need, shared with every template as $promo.
     * `active` is false when the deadline has passed or a manager has set live prices.
     */
    public static function forViews(array $rooms): array
    {
        $ends = self::endsAt();
        $saving = collect($rooms)->contains(fn ($r) => ($r['w2'] ?? 0) > ($r['p2'][0] ?? 0));
        $tz = config('hotel.offer.timezone', 'Asia/Tbilisi');

        return [
            'active' => self::running() && $saving,
            'label' => config('hotel.offer.label'),
            'discount' => self::discount(),
            'ends_ms' => $ends?->getTimestampMs(),
            'ends_label' => $ends?->setTimezone($tz)->format('j F'),
            'ends_long' => $ends?->setTimezone($tz)->format('j F Y, H:i').' Tbilisi time',
            'now_ms' => now()->getTimestampMs(),
        ];
    }
}
