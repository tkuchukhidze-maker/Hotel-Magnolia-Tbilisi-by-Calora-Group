<?php

namespace App\Support;

use App\Models\Booking;

/** Everything the booking emails show, worked out once so both emails stay consistent. */
class BookingMail
{
    public static function data(Booking $b): array
    {
        $policy = fn (string $key) => self::time(config('hotel.policies.'.$key));
        $rooms = collect($b->items ?? [])->map(fn ($l) => [
            'qty' => (int) $l['qty'],
            'name' => $l['name'],
            'total' => (int) ($l['stay_total'] ?? ($l['subtotal'] ?? 0) * $b->nights),
        ])->all();
        $c = config('hotel.contact');
        $guests = $b->adults.' '.($b->adults === 1 ? 'adult' : 'adults')
            .($b->children ? ', '.$b->children.' '.($b->children === 1 ? 'child' : 'children') : '');

        return [
            'hotel' => config('hotel.name'),
            'checkin' => $b->checkin,
            'checkout' => $b->checkout,
            'checkinTime' => $policy('check_in'),
            'checkoutTime' => $policy('check_out'),
            'nights' => $b->nights,
            'nightsLabel' => $b->nights.' '.($b->nights === 1 ? 'night' : 'nights'),
            'guests' => $guests,
            'childrenAges' => $b->children ? implode(', ', array_map(fn ($a) => $a === 0 ? 'under 1' : (string) $a, $b->children_ages ?? [])) : null,
            'rooms' => $rooms,
            'roomsShort' => $b->roomsSummary(),
            'rate' => $b->rateName(),
            'extras' => $b->extras ?? [],
            'total' => (int) $b->total,
            'address' => $c['address'].', '.($c['city'] ?? 'Tbilisi').', Georgia',
            'maps' => $c['google_maps'] ?? null,
            'directions' => config('hotel.google.directions'),
            'phone' => $c['phone_display'],
            'phoneTel' => $c['phone_tel'],
            'whatsapp' => 'https://wa.me/'.$c['whatsapp'],
            'site' => url('/'),
            'calendar' => self::calendarLink($b),
            'replyTo' => self::replyTo(),
        ];
    }

    /** "From [14:00]" → "14:00"; placeholders are dropped. */
    public static function time(?string $text): ?string
    {
        if (! $text || str_contains($text, '[')) {
            return null;
        }

        return preg_match('/(\d{1,2}:\d{2})/', $text, $m) ? $m[1] : null;
    }

    /** Where guests' replies should go: the hotel inbox, else the bookings inbox. */
    public static function replyTo(): ?string
    {
        foreach ([config('hotel.contact.email'), config('hotel.bookings_email')] as $a) {
            if ($a && filter_var($a, FILTER_VALIDATE_EMAIL) && ! str_ends_with(strtolower($a), '@example.com')) {
                return $a;
            }
        }

        return null;
    }

    /** "Add to Google Calendar" link for the stay. */
    public static function calendarLink(Booking $b): string
    {
        return 'https://calendar.google.com/calendar/render?'.http_build_query([
            'action' => 'TEMPLATE',
            'text' => 'Stay at '.config('hotel.short_name'),
            'dates' => $b->checkin->format('Ymd').'/'.$b->checkout->format('Ymd'),
            'details' => 'Booking '.$b->reference.' · '.$b->roomsSummary(),
            'location' => config('hotel.contact.address').', Tbilisi, Georgia',
        ]);
    }

    /** "12–14 Nov" or "30 Nov – 2 Dec" for subjects. */
    public static function shortDates(Booking $b): string
    {
        return $b->checkin->format('M') === $b->checkout->format('M')
            ? $b->checkin->format('j').'–'.$b->checkout->format('j M')
            : $b->checkin->format('j M').' – '.$b->checkout->format('j M');
    }
}
