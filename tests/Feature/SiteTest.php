<?php

namespace Tests\Feature;

use App\Mail\BookingReceived;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_pages_load(): void
    {
        foreach (['home' => 'Make yourself at home', 'rooms' => 'Find your kind of', 'our-hotel' => 'A little different', 'explore' => 'getting lost in', 'contact' => 'Rather talk'] as $route => $text) {
            $this->get(route($route))->assertOk()->assertSee($text, false);
        }
    }

    public function test_seo_files_are_served(): void
    {
        $this->get('/sitemap.xml')->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('rooms'), false);

        $this->get('/robots.txt')->assertOk()->assertSee('User-agent: *', false);

        $this->get('/llms.txt')->assertOk()
            ->assertSee('5 Kirile Ukleba St', false)
            ->assertSee('Deluxe Room', false);
    }

    public function test_pages_have_structured_data_and_contact_details(): void
    {
        $this->get(route('home'))->assertSee('"@type":"Hotel"', false)->assertSee('5 Kirile Ukleba St', false);
        $this->get(route('rooms'))->assertSee('"@type":"HotelRoom"', false)->assertSee('Double or Twin', false);
        $this->get(route('contact'))->assertSee('"@type":"FAQPage"', false)->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('tel:+995533334422', false);
    }

    public function test_booking_is_saved_with_server_side_price(): void
    {
        Mail::fake();

        $res = $this->postJson(route('bookings.store'), $this->payload(['room' => 'deluxe', 'rate' => 'bf', 'nights' => 3]));

        $res->assertCreated()->assertJsonPath('total', 166 * 3);
        $this->assertDatabaseHas('bookings', ['room' => 'deluxe', 'total' => 498, 'email' => 'guest@example.com']);
        Mail::assertSent(BookingReceived::class);
    }

    public function test_single_guest_gets_single_occupancy_price(): void
    {
        Mail::fake();

        $this->postJson(route('bookings.store'), $this->payload(['room' => 'double', 'adults' => 1, 'nights' => 2]))
            ->assertCreated()
            ->assertJsonPath('total', 106 * 2);
    }

    public function test_room_capacity_is_enforced(): void
    {
        $this->postJson(route('bookings.store'), $this->payload(['room' => 'double', 'adults' => 3]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('room');

        $this->assertSame(0, Booking::count());
    }

    public function test_guest_details_and_privacy_consent_are_required(): void
    {
        $this->postJson(route('bookings.store'), $this->payload([
            'first_name' => '', 'last_name' => '', 'country' => '', 'phone' => 'call me', 'privacy' => false,
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name', 'country', 'phone', 'privacy']);

        $this->assertSame(0, Booking::count());
    }

    public function test_booking_stores_guest_details(): void
    {
        Mail::fake();

        $this->postJson(route('bookings.store'), $this->payload())->assertCreated();

        $booking = Booking::first();
        $this->assertSame('Nino Beridze', $booking->guestName());
        $this->assertSame('Georgia', $booking->country);
        $this->assertNotNull($booking->privacy_accepted_at);
        $this->get(route('privacy'))->assertOk()->assertSee('What we collect');
    }

    public function test_invalid_input_is_rejected(): void
    {
        $this->postJson(route('bookings.store'), $this->payload(['email' => 'nope', 'checkin' => '2000-01-01', 'room' => 'penthouse']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'checkin', 'room']);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'room' => 'double',
            'rate' => 'room',
            'checkin' => now()->addDays(10)->toDateString(),
            'nights' => 2,
            'adults' => 2,
            'children' => 0,
            'first_name' => 'Nino',
            'last_name' => 'Beridze',
            'email' => 'guest@example.com',
            'phone' => '+995 555 00 00 00',
            'country' => 'Georgia',
            'arrival' => 'Evening',
            'notes' => null,
            'privacy' => true,
        ], $override);
    }
}
