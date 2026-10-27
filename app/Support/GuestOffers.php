<?php

namespace App\Support;

use App\Models\Booking;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Offer emails to the hotel's own guests: who receives them, the values the
 * email shows, and unsubscribing.
 */
class GuestOffers
{
    public const AUDIENCES = [
        'all' => 'Everyone who has booked',
        'stayed' => 'Guests who have stayed with us',
        'none' => 'Only the addresses I add below',
    ];

    /**
     * Unique guest addresses for an audience, newest booking first, minus anyone who unsubscribed.
     *
     * @return Collection<string, array{email: string, first_name: string}>  keyed by lower-case email
     */
    public static function audience(string $audience, array $extra = []): Collection
    {
        $people = collect();

        if ($audience !== 'none') {
            $q = Booking::query()->whereNotNull('email')->where('email', '!=', '')->orderByDesc('id');
            if ($audience === 'stayed') {
                $q->where(fn ($w) => $w->where('status', 'checked_out')
                    ->orWhere(fn ($c) => $c->whereIn('status', ['confirmed', 'checked_in'])->whereDate('checkout', '<', now()->toDateString())));
            }
            foreach ($q->get(['email', 'first_name']) as $b) {
                $key = strtolower(trim($b->email));
                if (filter_var($key, FILTER_VALIDATE_EMAIL) && ! $people->has($key)) {
                    $people->put($key, ['email' => trim($b->email), 'first_name' => (string) $b->first_name]);
                }
            }
        }

        foreach ($extra as $email) {
            $key = strtolower(trim($email));
            if (filter_var($key, FILTER_VALIDATE_EMAIL) && ! $people->has($key)) {
                $people->put($key, ['email' => trim($email), 'first_name' => '']);
            }
        }

        $out = DB::table('email_unsubscribes')->pluck('email')->map(fn ($e) => strtolower($e))->flip();

        return $people->reject(fn ($p, $key) => $out->has($key));
    }

    /** "a@b.ge, c@d.ge\n e@f.ge" → ['a@b.ge', 'c@d.ge', 'e@f.ge'] (valid addresses only). */
    public static function parseList(?string $text): array
    {
        return collect(preg_split('/[\s,;]+/', (string) $text))
            ->map(fn ($e) => trim($e, " <>\"'"))
            ->filter(fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
            ->unique(fn ($e) => strtolower($e))
            ->values()->all();
    }

    public static function unsubscribeUrl(string $email): string
    {
        // URL-safe token (no "/" or "+") that only this site can read.
        $token = strtr(Crypt::encryptString(strtolower(trim($email))), '+/', '-_');

        return route('unsubscribe.show', ['token' => $token]);
    }

    public static function emailFromToken(string $token): ?string
    {
        try {
            $email = Crypt::decryptString(strtr($token, '-_', '+/'));
        } catch (Throwable) {
            return null;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    public static function unsubscribe(string $email): void
    {
        DB::table('email_unsubscribes')->updateOrInsert(
            ['email' => strtolower(trim($email))],
            ['updated_at' => now(), 'created_at' => now()],
        );
    }

    /**
     * Values for the offer email. Everything is HTML-escaped.
     *
     * @param  array{subject:string, headline:string, body:string, discount:?int, code:?string, button_label:string, button_url:string, valid_until:?string}  $offer
     */
    public static function vars(array $offer, string $email, string $firstName = ''): array
    {
        $e = fn ($v) => e((string) $v);
        $c = config('hotel.contact');
        $until = null;
        if (! empty($offer['valid_until'])) {
            try {
                $until = \Carbon\Carbon::parse($offer['valid_until'])->format('j F Y');
            } catch (Throwable) {
                $until = null;
            }
        }

        return [
            'HOTEL' => $e(config('hotel.name')),
            'GREETING' => $e($firstName !== '' ? 'Dear '.$firstName.',' : 'Hello,'),
            'HEADLINE' => $e($offer['headline']),
            'BODY_HTML' => nl2br($e(trim($offer['body']))),
            'DISCOUNT' => ! empty($offer['discount']) ? $e((int) $offer['discount'].'% off') : '',
            'CODE' => $e($offer['code'] ?? ''),
            'UNTIL' => $until ? $e('Book by '.$until) : '',
            'BUTTON_LABEL' => $e($offer['button_label']),
            'BUTTON_URL' => $e($offer['button_url']),
            'ADDRESS' => $e($c['address'].', '.($c['city'] ?? 'Tbilisi').', Georgia'),
            'PHONE' => $e($c['phone_display']),
            'PHONE_TEL' => $e($c['phone_tel']),
            'WHATSAPP_URL' => $e('https://wa.me/'.$c['whatsapp']),
            'SITE_URL' => $e(url('/')),
            'MARK_URL' => $e(asset('assets/img/email/magnolia-mark.png')),
            'UNSUB_URL' => $e(self::unsubscribeUrl($email)),
        ];
    }

    /** The form's starting values: the current site offer. */
    public static function defaults(): array
    {
        $running = Offer::running();
        $d = Offer::discount();

        return [
            'subject' => $running && $d ? $d.'% off your next stay at Hotel Magnolia Tbilisi' : 'Come back to Hotel Magnolia Tbilisi',
            'headline' => $running && $d ? 'Your next stay, '.$d.'% off' : 'We would love to see you again',
            'body' => "Thank you for staying with us. As one of our guests, you get our best price when you book directly on our website.\n\nCoffee under the 127-year-old magnolia is waiting for you.",
            'discount' => $running ? $d : null,
            'code' => '',
            'button_label' => 'See the offer',
            'button_url' => $running ? route('offer') : route('rooms'),
            'valid_until' => $running ? Offer::endsAt()?->setTimezone(config('hotel.offer.timezone', 'Asia/Tbilisi'))->toDateString() : null,
            'audience' => 'all',
        ];
    }
}
