<?php

namespace App\Support;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sends every new booking to Zapier ("Webhooks by Zapier → Catch Hook") when
 * HOTEL_ZAPIER_HOOK_URL is set. Flat, readable fields so they can be inserted
 * into the Zap's AI prompt with "/". A failure never affects the booking.
 */
class BookingWebhook
{
    public static function payload(Booking $b): array
    {
        $plain = fn ($v) => html_entity_decode((string) $v, ENT_QUOTES);
        $v = BookingMail::vars($b);

        return [
            'reference' => $b->reference,
            'first_name' => $b->first_name,
            'last_name' => $b->last_name,
            'full_name' => $b->guestName(),
            'email' => $b->email,
            'phone' => $b->phone,
            'country' => $b->country,
            'address' => collect([$b->address, $b->postal_code, $b->city])->filter()->unique()->implode(', '),
            'checkin' => $b->checkin->toDateString(),
            'checkin_pretty' => $b->checkin->format('D j M Y'),
            'checkout' => $b->checkout->toDateString(),
            'checkout_pretty' => $b->checkout->format('D j M Y'),
            'check_in_time' => BookingMail::time(config('hotel.policies.check_in')) ?? '14:00',
            'check_out_time' => BookingMail::time(config('hotel.policies.check_out')) ?? '12:00',
            'nights' => $b->nights,
            'adults' => $b->adults,
            'children' => $b->children,
            'children_ages' => implode(', ', $b->children_ages ?? []),
            'rooms' => $b->roomsSummary(),
            'rate' => $b->rateName(),
            'extras' => collect($b->extras ?? [])->map(fn ($x) => $x['name'].' (GEL '.$x['amount'].')')->implode(', '),
            'total' => (int) $b->total,
            'total_text' => 'GEL '.number_format((int) $b->total),
            'arrival' => (string) $b->arrival,
            'notes' => (string) $b->notes,
            'hotel_email' => (string) config('hotel.bookings_email'),
            'calendar_url' => $plain($v['CALENDAR_URL']),
            'directions_url' => $plain($v['DIRECTIONS_URL']),
            'admin_url' => url('/admin'),
            'booked_at' => now('Asia/Tbilisi')->format('Y-m-d H:i'),
        ];
    }

    public static function send(Booking $b): void
    {
        $url = (string) config('hotel.zapier_hook_url');
        if ($url === '' || ! str_starts_with($url, 'https://hooks.zapier.com/')) {
            return;
        }
        try {
            Http::asJson()->timeout(6)->post($url, self::payload($b))->throw();
        } catch (Throwable $e) {
            Log::error('Zapier booking webhook failed', ['reference' => $b->reference, 'error' => $e->getMessage()]);
        }
    }
}
