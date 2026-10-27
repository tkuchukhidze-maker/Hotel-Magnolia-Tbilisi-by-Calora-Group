<?php

namespace Tests\Feature;

use App\Support\HotelFacts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoAeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_markdown_versions_are_served_with_canonical_link(): void
    {
        $pages = [
            '/index.md' => ['home', 'A boutique hotel in Old Tbilisi'],
            '/rooms.md' => ['rooms', 'Room types and nightly prices'],
            '/our-hotel.md' => ['our-hotel', '127-year-old magnolia'],
            '/explore.md' => ['explore', 'Arriving by car and free parking'],
            '/contact.md' => ['contact', 'Questions guests ask'],
        ];

        foreach ($pages as $path => [$route, $text]) {
            $this->get($path)->assertOk()
                ->assertHeader('Content-Type', 'text/markdown; charset=UTF-8')
                ->assertHeader('Link', '<'.route($route).'>; rel="canonical"')
                ->assertSee($text, false);

            $this->get(route($route))->assertSee('type="text/markdown" href="'.url($path).'"', false);
        }

        $this->get('/sitemap.md')->assertOk()->assertSee(url('/explore.md'), false);
    }

    public function test_placeholders_never_reach_public_pages(): void
    {
        config([
            'hotel.policies.check_in' => 'From [14:00]',
            'hotel.policies.cancellation' => 'Free until [deadline]',
            'hotel.offer.ends' => '[OFFER END DATE]',
            'hotel.contact.email_display' => '[EMAIL]',
            'hotel.history' => '[history]',
            'hotel.arrival.transfer' => '[confirm]',
            'hotel.rating.count' => '[N]',
            'hotel.reviews' => [],
        ]);

        foreach (['home', 'rooms', 'our-hotel', 'explore', 'contact'] as $route) {
            $html = $this->get(route($route))->assertOk()->getContent();
            $this->assertDoesNotMatchRegularExpression('/\[(14:00|deadline|OFFER END DATE|EMAIL|history|confirm|N)\]/', $html, $route);
        }

        foreach (['/index.md', '/rooms.md', '/our-hotel.md', '/explore.md', '/contact.md', '/llms.txt'] as $path) {
            // Markdown links "[title](url)" are fine; any other [bracket] is an unfilled placeholder.
            $this->assertDoesNotMatchRegularExpression('/\[[^\]]*\](?!\()/', $this->get($path)->getContent(), $path);
        }

        $this->get(route('home'))->assertDontSee('In their own words.');
    }

    public function test_filled_facts_are_shown_everywhere(): void
    {
        config([
            'hotel.policies.check_in' => 'From 14:00',
            'hotel.arrival.parking.spaces' => '6 spaces in the courtyard',
            'hotel.rating.count' => '42',
            'hotel.reviews' => [['quote' => 'Lovely stay.', 'name' => 'Ana', 'country' => 'Georgia', 'source' => 'Google', 'date' => 'October 2026']],
        ]);

        $this->get(route('contact'))->assertSee('From 14:00');
        $this->get(route('explore'))->assertSee('6 spaces in the courtyard')->assertSee('id="by-car"', false);
        $this->get('/explore.md')->assertSee('6 spaces in the courtyard');
        $this->get(route('home'))->assertSee('In their own words.')->assertSee('Lovely stay.')->assertSee('42 reviews');
        $this->get('/llms.txt')->assertSee('Check-in: From 14:00')->assertSee('42 reviews');
        $this->get(route('home'))->assertSee('"checkinTime":"14:00"', false);
    }

    public function test_explore_shows_walking_times(): void
    {
        $this->get(route('explore'))->assertOk()
            ->assertSee('id="walking-distances"', false)
            ->assertSee('id="from-the-airport"', false)
            ->assertSee('950 m · about 12 min walk');
    }

    public function test_discovery_hints_in_native_files(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        $this->get('/robots.txt')->assertSee('# Markdown versions of every page', false)->assertSee('Disallow: /bookings', false);
        $this->get('/sitemap.xml')->assertSee('<!-- Markdown versions', false);
        $this->get('/llms.txt')->assertSee(url('/sitemap.md'), false);
    }

    public function test_helpers(): void
    {
        $this->assertSame('950 m', HotelFacts::distance(950));
        $this->assertSame('1.6 km', HotelFacts::distance(1600));
        $this->assertSame('15 km', HotelFacts::distance(15000));
        $this->assertSame(12, HotelFacts::walkMinutes(950));
        $this->assertFalse(HotelFacts::filled('[Policy]'));
        $this->assertArrayHasKey('hotel.policies.pets', HotelFacts::placeholders());

        $from = HotelFacts::fromPrices(config('hotel.rooms'));
        $this->assertSame(106, $from['one']);
        $this->assertSame(129, $from['two']);
    }
}
