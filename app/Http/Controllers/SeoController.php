<?php

namespace App\Http\Controllers;

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
            ? "User-agent: *\nAllow: /\nDisallow: /bookings\n\nSitemap: ".route('sitemap')."\n"
            : "User-agent: *\nDisallow: /\n";

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /** Plain-language summary for AI assistants and answer engines (llmstxt.org). */
    public function llms(): Response
    {
        $c = config('hotel.contact');
        $p = config('hotel.policies');
        $lines = [
            '# '.config('hotel.name'),
            '',
            'Also known as: '.config('hotel.short_name').', Hotel Magnolia. Part of Calora Group.',
            '',
            '> A small boutique hotel in a restored brick house at '.$c['address'].', Old Town, Tbilisi, Georgia. Rooftop terrace with glass domes, free parking, 24-hour front desk, buffet breakfast, and a 127-year-old magnolia tree in the courtyard.',
            '',
            '## Key facts',
            '- Address: '.$c['address'].', '.$c['neighbourhood'].', Tbilisi, Georgia',
            '- Phone and WhatsApp: '.$c['phone_display'],
            '- Google Maps: '.$c['google_maps'],
            '- Distances: Freedom Square 950 m, Freedom Square metro 750 m, Mtatsminda Pantheon 550 m, Narikala Fortress 2.2 km, Tbilisi International Airport 15 km',
            '- Booking: direct on '.route('rooms').' — no prepayment, pay at the hotel, free cancellation, price match',
            '- Parking: free for guests',
            '- Rating: '.config('hotel.rating.score').'/10 ('.config('hotel.rating.label').') on '.config('hotel.rating.source'),
        ];
        foreach (['check_in' => 'Check-in', 'check_out' => 'Check-out', 'breakfast' => 'Breakfast'] as $k => $label) {
            if (! str_contains($p[$k], '[')) {
                $lines[] = '- '.$label.': '.$p[$k];
            }
        }

        $lines[] = '';
        $lines[] = '## Rooms (per night, taxes included)';
        foreach (config('hotel.rooms') as $r) {
            $lines[] = '- '.$r['name'].': '.$r['size'].', '.strtolower($r['bed']).', up to '.$r['cap'].' '.($r['cap'] === 1 ? 'guest' : 'guests')
                .', '.strtolower($r['extra']).'. From GEL '.($r['cap'] === 1 ? $r['p1'][0] : $r['p2'][0])
                .' (GEL '.($r['cap'] === 1 ? $r['p1'][1] : $r['p2'][1]).' with breakfast).';
        }

        $lines[] = '';
        $lines[] = '## Pages';
        $lines[] = '- [Rooms and booking]('.route('rooms').'): room details, prices and direct booking';
        $lines[] = '- [Our hotel and gallery]('.route('our-hotel').'): the building, rooftop, courtyard magnolia, photos';
        $lines[] = '- [Explore Tbilisi]('.route('explore').'): what is nearby and how to get here';
        $lines[] = '- [Contact and FAQ]('.route('contact').'): contacts, policies and answers to common questions';

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
