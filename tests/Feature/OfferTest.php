<?php

namespace Tests\Feature;

use App\Mail\BookingReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OfferTest extends TestCase
{
    use RefreshDatabase;

    private function endOfferIn(string $when): void
    {
        config(['hotel.offer.ends' => now('Asia/Tbilisi')->modify($when)->format('Y-m-d H:i:s')]);
    }

    public function test_offer_page_shows_a_live_countdown_and_savings(): void
    {
        $this->endOfferIn('+3 days');

        $this->get(route('offer'))->assertOk()
            ->assertSee('25% off every room.', false)
            ->assertSee('data-countdown', false)
            ->assertSee('role="timer"', false)
            ->assertSee('You save GEL 129', false) // Double: (172 − 129) × 3 nights
            ->assertSee('Book now &amp; save 25%', false);

        // The countdown starts at the real time left (3 days), not a made-up number.
        $this->get(route('offer'))->assertSee('<b data-cd="d">02</b>', false);
    }

    public function test_promo_bar_and_nav_link_appear_while_the_offer_runs(): void
    {
        $this->endOfferIn('+5 days');

        $this->get(route('home'))->assertOk()
            ->assertSee('promo-bar', false)
            ->assertSee('−25% Offer', false)
            ->assertSee('offer-band', false);
        $this->get('/sitemap.xml')->assertSee('https://magnoliatbilisi.com/offer', false);
    }

    public function test_after_the_deadline_prices_go_back_to_full_and_no_discount_is_claimed(): void
    {
        Mail::fake();
        $this->endOfferIn('-1 minute');

        $this->get(route('offer'))->assertOk()
            ->assertSee('has ended', false)
            ->assertDontSee('data-countdown', false);
        $this->get(route('home'))->assertDontSee('promo-bar', false)->assertDontSee('−25% Offer', false);
        $this->get(route('rooms'))->assertDontSee('<s>GEL', false)->assertSee('from GEL 142', false);
        $this->get('/sitemap.xml')->assertDontSee('https://magnoliatbilisi.com/offer', false);

        // The server charges the full price: Double for two = GEL 172 a night.
        $this->postJson(route('bookings.store'), [
            'items' => [['room' => 'double', 'qty' => 1]],
            'rate' => 'room',
            'checkin' => now()->addDays(10)->toDateString(),
            'nights' => 2,
            'adults' => 2,
            'children' => 0,
            'children_ages' => [],
            'first_name' => 'Nino',
            'last_name' => 'Beridze',
            'email' => 'guest@example.com',
            'phone' => '+995 555 00 00 00',
            'country' => 'Georgia',
            'privacy' => true,
        ])->assertCreated()->assertJsonPath('total', 172 * 2);
    }
}
