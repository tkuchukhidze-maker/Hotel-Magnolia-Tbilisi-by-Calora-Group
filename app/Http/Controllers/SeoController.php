<?php

namespace App\Http\Controllers;

use App\Support\HotelFacts;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    /** Public pages, most important first. */
    private const PAGES = [
        'home' => ['weekly', '1.0'],
        'rooms' => ['weekly', '0.9'],
        'our-hotel' => ['monthly', '0.7'],
        'explore' => ['monthly', '0.6'],
        'contact' => ['monthly', '0.6'],
    ];

    public function sitemap(): Response
    {
        $lastmod = date('Y-m-d', filemtime(resource_path('views/pages')));
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<!-- Markdown versions of these pages for AI assistants: '.url('/sitemap.md').' · summary: '.route('llms').' -->'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";

        $images = [
            'home' => ['hero-facade', 'magnolia-courtyard', 'lobby'],
            'rooms' => ['room-double', 'room-deluxe', 'room-twin'],
            'offer' => ['rooftop-view', 'magnolia-courtyard'],
            'our-hotel' => ['lobby', 'rooftop-view', 'bathroom', 'aerial', 'entrance-door'],
            'explore' => ['rooftop-domes'],
        ];

        $pages = self::PAGES;
        if (\App\Support\Offer::running()) {
            $pages = ['home' => $pages['home'], 'offer' => ['daily', '0.9']] + $pages;
        }

        foreach ($pages as $name => [$freq, $priority]) {
            $xml .= '  <url><loc>'.e(route($name)).'</loc><lastmod>'.$lastmod.'</lastmod>'
                .'<changefreq>'.$freq.'</changefreq><priority>'.$priority.'</priority>';
            foreach ($images[$name] ?? [] as $img) {
                $xml .= '<image:image><image:loc>'.e(asset('assets/img/'.$img.'.webp')).'</image:loc></image:image>';
            }
            $xml .= "</url>\n";
        }

        return response($xml.'</urlset>'."\n", 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        // Only the live site should be indexed; staging/local answer "Disallow: /".
        $body = app()->environment('production')
            ? "# Plain-text summary for AI assistants: ".route('llms')."\n"
                ."# Markdown versions of every page: ".url('/sitemap.md')."\n"
                ."User-agent: *\nAllow: /\nDisallow: /bookings\n\nSitemap: ".route('sitemap')."\n"
            : "User-agent: *\nDisallow: /\n";

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /** Plain-language summary for AI assistants and answer engines (llmstxt.org). */
    public function llms(): Response
    {
        $c = config('hotel.contact');
        $r = config('hotel.rating');
        $from = HotelFacts::fromPrices();
        $lines = [
            '# '.config('hotel.name'),
            '',
            '> A small boutique hotel in a restored brick house at '.$c['address'].', '.$c['neighbourhood'].', Tbilisi, Georgia, below Mount Mtatsminda. Rooftop terrace with glass domes, free parking, 24-hour front desk, buffet breakfast, and a 127-year-old magnolia tree in the courtyard.',
            '',
            'Also known as: '.config('hotel.short_name').', Hotel Magnolia, Magnolia Hotel Tbilisi. Part of Calora Group.',
            'Every page has a plain Markdown version; start with '.url('/sitemap.md').' and quote the HTML page as the source.',
            '',
            '## Key facts',
            '- Address: '.$c['address'].', '.$c['neighbourhood'].', Tbilisi, Georgia',
            '- Phone and WhatsApp (24 hours): '.$c['phone_display'],
            '- Google Maps: '.$c['google_maps'],
            '- Prices: from GEL '.$from['one'].' a night for one guest, GEL '.$from['two'].' for two, taxes included',
            '- Booking: direct on '.route('rooms').' — no prepayment, pay at the hotel, '.strtolower(HotelFacts::cancellation()).', price match',
            '- Parking: '.config('hotel.arrival.parking.summary'),
        ];
        if (\App\Support\Offer::running()) {
            $promo = \App\Support\Offer::forViews(\App\Support\RoomCatalog::all());
            $lines[] = '- Book-direct offer: −'.$promo['discount'].'% on every room until '.$promo['ends_long'].' ('.route('offer').')';
        }
        foreach (config('hotel.nearby.see') as [$place, $m]) {
            if (in_array($place, ['Freedom Square', 'Mtatsminda Pantheon', 'Narikala Fortress'], true)) {
                $lines[] = '- '.$place.': '.HotelFacts::distance($m).(HotelFacts::isWalkable($m) ? ', about '.HotelFacts::walkMinutes($m).' min walk' : '');
            }
        }
        $lines[] = '- Freedom Square metro: 750 m · Tbilisi International Airport: '.HotelFacts::distance(config('hotel.arrival.airport_m'));
        if (HotelFacts::filled($r['count'])) {
            $lines[] = '- Rating: '.$r['score'].'/10 ('.$r['label'].') on '.$r['source'].', '.$r['count'].' reviews';
        }
        foreach (HotelFacts::policies() as $label => $value) {
            if (! in_array($label, ['Parking', 'Cancellation'], true)) {
                $lines[] = '- '.$label.': '.$value;
            }
        }

        $lines[] = '';
        $lines[] = '## Rooms (per night, taxes included)';
        foreach (\App\Support\RoomCatalog::all() as $room) {
            $lines[] = '- '.$room['name'].': '.$room['size'].', '.strtolower($room['bed']).', up to '.$room['cap'].' '.($room['cap'] === 1 ? 'guest' : 'guests')
                .', '.strtolower($room['extra']).'. From GEL '.($room['cap'] === 1 ? $room['p1'][0] : $room['p2'][0])
                .' (GEL '.($room['cap'] === 1 ? $room['p1'][1] : $room['p2'][1]).' with breakfast).';
        }

        $lines[] = '';
        $lines[] = '## Pages';
        foreach (MarkdownController::PAGES as $route => [$path, $title]) {
            $lines[] = '- ['.$title.']('.route($route).'): Markdown version '.url($path);
        }

        $lines[] = '';
        $lines[] = '## FAQ';
        foreach (config('hotel.faq') as [$q, $a]) {
            $lines[] = '';
            $lines[] = '### '.$q;
            $lines[] = $a;
        }

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
