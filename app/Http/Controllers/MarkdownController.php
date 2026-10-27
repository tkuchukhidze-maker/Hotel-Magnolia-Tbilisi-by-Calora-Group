<?php

namespace App\Http\Controllers;

use App\Support\HotelFacts as F;
use App\Support\Offer;
use App\Support\RoomCatalog;
use Illuminate\Http\Response;

/**
 * Plain Markdown versions of the public pages for AI assistants and answer
 * engines. Built from the same config as the HTML, so facts always match,
 * and anything still a [placeholder] is left out. Each response points back
 * to its HTML page as the canonical URL.
 */
class MarkdownController extends Controller
{
    /** route name => [markdown path, title] */
    public const PAGES = [
        'home' => ['/index.md', 'Hotel Magnolia Tbilisi — boutique hotel in Old Tbilisi'],
        'rooms' => ['/rooms.md', 'Rooms and prices'],
        'our-hotel' => ['/our-hotel.md', 'The hotel, rooftop terrace and the 127-year-old magnolia'],
        'explore' => ['/explore.md', 'Location, walking distances and getting here'],
        'contact' => ['/contact.md', 'Contact, policies and FAQ'],
    ];

    public static function pathFor(?string $route): ?string
    {
        return $route && isset(self::PAGES[$route]) ? self::PAGES[$route][0] : null;
    }

    public function home(): Response
    {
        $c = config('hotel.contact');
        $from = F::fromPrices();
        $md = [
            '# '.config('hotel.name'),
            '',
            'A small boutique hotel in a restored brick house at '.$c['address'].', '.$c['neighbourhood'].', Tbilisi, Georgia, on a quiet street below Mount Mtatsminda.',
            '',
            '## A boutique hotel in Old Tbilisi',
            '- Five room types, 15–30 m², for 1–3 guests. From GEL '.$from['one'].' a night for one guest and GEL '.$from['two'].' for two, taxes included.',
            '- Free parking for every guest, rare in the old town.',
            '- Rooftop terrace with glass domes facing Mount Mtatsminda and the TV tower.',
            '- A 127-year-old magnolia tree in the courtyard.',
            '- 24-hour front desk, free Wi-Fi, air conditioning, buffet breakfast.',
            '- Freedom Square 950 m (about '.F::walkMinutes(950).' minutes on foot), Freedom Square metro 750 m.',
            '',
            '## Why book direct',
            '- No prepayment: you pay at the hotel.',
            '- '.F::cancellation().'.',
            '- Price match: if you find a lower price for the same room and dates, the hotel matches it.',
        ];
        if (Offer::running()) {
            $promo = Offer::forViews(RoomCatalog::all());
            $md[] = '- '.$promo['label'].': −'.$promo['discount'].'% on every room booked direct by '.$promo['ends_long'].'. Details: '.route('offer');
        }

        $reviews = config('hotel.reviews', []);
        if ($reviews) {
            $md[] = '';
            $md[] = '## Guest reviews';
            foreach ($reviews as $r) {
                $md[] = '> '.$r['quote'];
                $md[] = '> — '.$r['name'].', '.$r['country'].' ('.$r['source'].', '.$r['date'].')';
                $md[] = '';
            }
        }

        return $this->respond('home', $md);
    }

    public function rooms(): Response
    {
        $md = [
            '# Rooms and prices — '.config('hotel.short_name'),
            '',
            'Prices are per room per night in GEL, taxes included'.(Offer::running() ? ', with the current −'.Offer::discount().'% book-direct offer applied' : '').'. Book on '.route('rooms').'.',
            '',
            '## Room types and nightly prices',
        ];
        foreach (RoomCatalog::all() as $id => $r) {
            [$room, $bf] = $r['cap'] === 1 ? $r['p1'] : $r['p2'];
            $md[] = '';
            $md[] = '### '.$r['name'];
            $md[] = '- Sleeps '.$r['cap'].', '.$r['size'].', '.strtolower($r['bed']).', '.strtolower($r['extra']).'.';
            $md[] = '- GEL '.$room.' a night, GEL '.$bf.' with breakfast'.($r['cap'] > 1 ? ' (GEL '.$r['p1'][0].' for one guest)' : '').'.';
            $md[] = '- '.$r['blurb'];
            $md[] = '- Link: '.route('rooms', ['room' => $id]);
        }
        $md[] = '';
        $md[] = 'All rooms: air conditioning, private bathroom, flat-screen TV, free Wi-Fi. Pay at the hotel; no prepayment.';

        return $this->respond('rooms', $md);
    }

    public function ourHotel(): Response
    {
        $md = [
            '# Our hotel — '.config('hotel.short_name'),
            '',
            '## Rooftop terrace and the 127-year-old magnolia',
            'A small hotel in a restored brick house in the historic heart of Tbilisi: thick walls, a carved door and wrought iron, with a light new roof of glass domes and a terrace facing Mount Mtatsminda. In the courtyard grows a 127-year-old magnolia.',
        ];
        if (F::filled(config('hotel.history'))) {
            $md[] = '';
            $md[] = config('hotel.history');
        }
        $md[] = '';
        $md[] = '## At a glance';
        $md[] = '- Buffet breakfast every morning, from GEL 11 per room.';
        $md[] = '- Free parking, free Wi-Fi, air conditioning in every room.';
        $md[] = '- 24-hour front desk: '.config('hotel.contact.phone_display').'.';
        $md[] = '- Family rooms: Triple Room with a balcony.';
        if (F::filled(config('hotel.arrival.transfer'))) {
            $md[] = '- Airport transfer: '.config('hotel.arrival.transfer');
        }

        return $this->respond('our-hotel', $md);
    }

    public function explore(): Response
    {
        $n = config('hotel.nearby');
        $a = config('hotel.arrival');
        $c = config('hotel.contact');
        $md = [
            '# Location and getting here — '.config('hotel.short_name'),
            '',
            'Address: '.$c['address'].', '.$c['neighbourhood'].', Tbilisi, Georgia. Google Maps: '.$c['google_maps'],
            '',
            '## Walking distances from the hotel',
            'Walking times are approximate; some old-town streets are steep.',
        ];
        foreach (['see' => 'See', 'eat' => 'Eat and drink', 'transport' => 'Metro'] as $key => $label) {
            $md[] = '';
            $md[] = '### '.$label;
            foreach ($n[$key] as [$place, $m]) {
                $md[] = '- '.$place.': '.F::distance($m).(F::isWalkable($m) ? ', about '.F::walkMinutes($m).' min walk' : '');
            }
        }
        $md[] = '';
        $md[] = '## Arriving by car and free parking';
        $md[] = $a['parking']['summary'];
        foreach (['where', 'spaces', 'reserve'] as $k) {
            if (F::filled($a['parking'][$k] ?? null)) {
                $md[] = '- '.$a['parking'][$k];
            }
        }
        $md[] = 'Directions: '.config('hotel.google.directions');
        $md[] = '';
        $md[] = '## Getting here from Tbilisi Airport';
        $md[] = 'Tbilisi International Airport is '.F::distance($a['airport_m']).' from the hotel.'
            .(F::filled($a['transfer']) ? ' '.$a['transfer'] : ' For a ride, message the hotel on WhatsApp ('.$c['phone_display'].') before you fly.');

        return $this->respond('explore', $md);
    }

    public function contact(): Response
    {
        $c = config('hotel.contact');
        $md = [
            '# Contact and FAQ — '.config('hotel.short_name'),
            '',
            '- Phone and WhatsApp, 24 hours: '.$c['phone_display'],
        ];
        if (F::filled($c['email_display'])) {
            $md[] = '- Email: '.$c['email_display'];
        }
        $md[] = '- Address: '.$c['address'].', '.$c['neighbourhood'].', Tbilisi, Georgia';
        $md[] = '';
        $md[] = '## Good to know before you come';
        foreach (F::policies() as $label => $value) {
            $md[] = '- '.$label.': '.$value;
        }
        $md[] = '';
        $md[] = '## Questions guests ask';
        foreach (config('hotel.faq') as [$q, $a]) {
            $md[] = '';
            $md[] = '### '.$q;
            $md[] = $a;
        }

        return $this->respond('contact', $md);
    }

    /** Map of every Markdown page, for AI assistants (see also /llms.txt). */
    public function sitemap(): Response
    {
        $md = [
            '# '.config('hotel.name').' — Markdown site map',
            '',
            'Plain-text versions of every public page. The HTML page is the canonical version of each.',
            '',
        ];
        foreach (self::PAGES as $route => [$path, $title]) {
            $md[] = '- ['.$title.']('.url($path).') — HTML: '.route($route);
        }
        $md[] = '';
        $md[] = 'Summary for AI assistants: '.route('llms');

        return $this->markdown($md, url('/sitemap.md'));
    }

    private function respond(string $route, array $lines): Response
    {
        $lines[] = '';
        $lines[] = '---';
        $lines[] = 'Canonical page: '.route($route).' · Book: '.route('rooms').' · All pages: '.url('/sitemap.md');

        return $this->markdown($lines, route($route));
    }

    private function markdown(array $lines, string $canonical): Response
    {
        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/markdown; charset=UTF-8',
            'Link' => '<'.$canonical.'>; rel="canonical"',
        ]);
    }
}
