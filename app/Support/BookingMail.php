<?php

namespace App\Support;

use App\Models\Booking;

/**
 * Everything the booking emails show, as named values.
 *
 * The same names are used as merge tags (*|NAME|*) in the Mailchimp Transactional
 * templates "magnolia-booking-guest" and "magnolia-booking-admin", so the hotel can
 * restyle the emails in Mailchimp. The Blade views render the same layout from the
 * same values, and are used when the Mailchimp templates are switched off.
 *
 * Every value is already HTML-escaped (Mailchimp inserts merge values as they are).
 */
class BookingMail
{
    public const GUEST_TEMPLATE = 'magnolia-booking-guest';

    public const ADMIN_TEMPLATE = 'magnolia-booking-admin';

    /** @return array<string, string> */
    public static function vars(Booking $b): array
    {
        $e = fn ($v) => e((string) $v);
        $c = config('hotel.contact');
        $in = self::time(config('hotel.policies.check_in'));
        $out = self::time(config('hotel.policies.check_out'));
        $guests = $b->adults.' '.($b->adults === 1 ? 'adult' : 'adults')
            .($b->children ? ', '.$b->children.' '.($b->children === 1 ? 'child' : 'children') : '');
        $ages = $b->children ? implode(', ', array_map(fn ($a) => (int) $a === 0 ? 'under 1' : (string) $a, $b->children_ages ?? [])) : '';
        $nights = $b->nights.' '.($b->nights === 1 ? 'night' : 'nights');

        $row = fn (string $label, string $amount) => '<tr><td style="padding:10px 0;border-top:1px solid #E6D8C3;font-family:Arial,Helvetica,sans-serif;font-size:15px;color:#42271F">'.$label
            .'</td><td align="right" style="padding:10px 0;border-top:1px solid #E6D8C3;font-family:Arial,Helvetica,sans-serif;font-size:15px;color:#42271F;white-space:nowrap">'.$amount.'</td></tr>';
        $lines = '';
        foreach ($b->items ?? [] as $l) {
            $total = (int) ($l['stay_total'] ?? ($l['subtotal'] ?? 0) * $b->nights);
            $lines .= $row((int) $l['qty'].' &times; '.$e($l['name']), 'GEL '.number_format($total));
        }
        foreach ($b->extras ?? [] as $x) {
            $lines .= $row($e($x['name']), 'GEL '.number_format((int) $x['amount']));
        }

        $address = $c['address'].', '.($c['city'] ?? 'Tbilisi').', Georgia';
        $guestAddress = collect([$b->address, $b->postal_code, $b->city])->filter()->unique()->implode(', ');

        return [
            'HOTEL' => $e(config('hotel.name')),
            'REF' => $e($b->reference),
            'GUEST_FIRST' => $e($b->first_name),
            'GUEST_NAME' => $e($b->guestName()),
            'GUEST_EMAIL' => $e($b->email),
            'GUEST_PHONE' => $e($b->phone),
            'GUEST_PHONE_TEL' => $e(preg_replace('/[^0-9+]/', '', (string) $b->phone)),
            'GUEST_COUNTRY' => $e($b->country),
            'GUEST_ADDRESS' => $e($guestAddress ?: '—'),
            'ARRIVAL' => $e($b->arrival ?: 'Not given'),
            'NOTES' => $b->notes ? nl2br($e($b->notes)) : '—',
            'CHECKIN_DAY' => $e($b->checkin->format('D j M')),
            'CHECKIN_SUB' => $e($b->checkin->format('Y').($in ? ' · from '.$in : '')),
            'CHECKOUT_DAY' => $e($b->checkout->format('D j M')),
            'CHECKOUT_SUB' => $e($b->checkout->format('Y').($out ? ' · until '.$out : '')),
            'NIGHTS' => $e($nights),
            'GUESTS' => $e($guests),
            'CHILD_AGES' => $ages ? $e(' (ages '.$ages.')') : '',
            'ROOMS' => $e($b->roomsSummary()),
            'LINES_HTML' => $lines,
            'RATE' => $e($b->rateName()),
            'TOTAL' => $e('GEL '.number_format((int) $b->total)),
            'CALENDAR_URL' => $e(self::calendarLink($b)),
            'ADDRESS' => $e($address),
            'DIRECTIONS_URL' => $e(config('hotel.google.directions') ?: ($c['google_maps'] ?? url('/contact'))),
            'WHATSAPP_URL' => $e('https://wa.me/'.$c['whatsapp']),
            'PHONE' => $e($c['phone_display']),
            'PHONE_TEL' => $e($c['phone_tel']),
            'SITE_URL' => $e(url('/')),
            'MARK_URL' => $e(asset('assets/img/email/magnolia-mark.png')),
            'ADMIN_URL' => $e(url('/admin')),
            'BOOKED_AT' => $e($b->created_at?->setTimezone('Asia/Tbilisi')->format('j M Y, H:i') ?? ''),
        ];
    }

    /** Mailchimp merge-tag version of vars(): every value becomes *|NAME|*. Used to build the templates. */
    public static function tags(): array
    {
        $keys = array_keys(self::vars(self::sample()));

        return array_combine($keys, array_map(fn ($k) => '*|'.$k.'|*', $keys));
    }

    /** Merge vars in the format Mandrill's X-MC-MergeVars header expects. */
    public static function mergeVarsHeader(Booking $b): string
    {
        return json_encode(self::vars($b), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}';
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
        return $b->checkin->format('M Y') === $b->checkout->format('M Y')
            ? $b->checkin->format('j').'–'.$b->checkout->format('j M')
            : $b->checkin->format('j M').' – '.$b->checkout->format('j M');
    }

    /** An unsaved example booking, for previews and for listing the merge tags. */
    public static function sample(): Booking
    {
        $b = new Booking([
            'reference' => 'MG-SAMPLE', 'rate' => 'room', 'nights' => 2, 'adults' => 2, 'children' => 1, 'children_ages' => [6],
            'items' => [['room' => 'double', 'name' => 'Double Room', 'qty' => 1, 'stay_total' => 258], ['room' => 'triple', 'name' => 'Triple Room', 'qty' => 1, 'stay_total' => 380]],
            'extras' => [['name' => 'Airport transfer', 'amount' => 60, 'price' => 60, 'unit' => 'stay']],
            'total' => 698, 'first_name' => 'Nino', 'last_name' => 'Beridze', 'email' => 'nino@example.com', 'phone' => '+995 555 12 34 56',
            'country' => 'Georgia', 'arrival' => 'Evening', 'notes' => 'A baby cot, please.',
        ]);
        $b->checkin = now()->addDays(14)->startOfDay();
        $b->checkout = now()->addDays(16)->startOfDay();
        $b->created_at = now();

        return $b;
    }
}
