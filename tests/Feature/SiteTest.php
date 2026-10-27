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

        $res = $this->postJson(route('bookings.store'), $this->payload(['items' => [['room' => 'deluxe', 'qty' => 1]], 'rate' => 'bf', 'nights' => 3]));

        $res->assertCreated()->assertJsonPath('total', 166 * 3)->assertJsonPath('status', 'requested');
        $this->assertDatabaseHas('bookings', ['room' => 'deluxe', 'total' => 498, 'email' => 'guest@example.com']);
        Mail::assertSent(BookingReceived::class);
    }

    public function test_single_guest_gets_single_occupancy_price(): void
    {
        Mail::fake();

        $this->postJson(route('bookings.store'), $this->payload(['items' => [['room' => 'double', 'qty' => 1]], 'adults' => 1, 'nights' => 2]))
            ->assertCreated()
            ->assertJsonPath('total', 106 * 2);
    }

    public function test_room_capacity_is_enforced(): void
    {
        $this->postJson(route('bookings.store'), $this->payload(['adults' => 3]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('items');

        // Children count towards beds too.
        $this->postJson(route('bookings.store'), $this->payload(['children' => 1, 'children_ages' => [6]]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('items');

        $this->assertSame(0, Booking::count());
    }

    public function test_big_group_can_book_several_rooms_of_several_types(): void
    {
        Mail::fake();

        $res = $this->postJson(route('bookings.store'), $this->payload([
            'items' => [['room' => 'double', 'qty' => 2], ['room' => 'triple', 'qty' => 1]],
            'adults' => 5,
            'children' => 2,
            'nights' => 2,
        ]));

        // 2 × 129 + 190 = 448 per night.
        $res->assertCreated()->assertJsonPath('nightly', 448)->assertJsonPath('total', 896);

        $booking = Booking::first();
        $this->assertSame(3, $booking->rooms_count);
        $this->assertSame([], $booking->children_ages);
        $this->assertSame('double', $booking->room);
        $this->assertSame('2 × Double Room, 1 × Triple Room', $booking->roomsSummary());
    }

    public function test_cannot_book_more_rooms_than_available(): void
    {
        $stock = config('hotel.rooms.triple.stock');

        $this->postJson(route('bookings.store'), $this->payload([
            'items' => [['room' => 'triple', 'qty' => $stock + 1]],
            'adults' => 3 * ($stock + 1) > 12 ? 12 : 3 * ($stock + 1),
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('items.0.qty');
    }

    public function test_children_can_be_added_without_ages(): void
    {
        Mail::fake();
        $payload = $this->payload([
            'items' => [['room' => 'triple', 'qty' => 1]],
            'adults' => 2,
            'children' => 1,
        ]);
        unset($payload['children_ages']);
        $this->postJson(route('bookings.store'), $payload)->assertCreated()->assertJsonPath('status', 'requested');
        $this->assertSame([], Booking::first()->children_ages);
    }

    public function test_extra_bed_request_is_recorded_without_unapproved_supplement(): void
    {
        Mail::fake();
        $res = $this->postJson(route('bookings.store'), $this->payload([
            'items' => [['room' => 'double', 'qty' => 1, 'extra_beds' => 1]],
            'adults' => 3,
        ]));
        $res->assertCreated()->assertJsonPath('total', 258)
            ->assertJsonPath('extra_beds_requested', 1)
            ->assertJsonPath('extra_bed_supplement_pending', true);
        $this->assertSame(1, Booking::first()->items[0]['extra_beds']);
    }

    public function test_each_selected_room_requires_at_least_one_guest(): void
    {
        $this->postJson(route('bookings.store'), $this->payload([
            'items' => [['room' => 'double', 'qty' => 2]],
            'adults' => 1,
        ]))->assertStatus(422)->assertJsonValidationErrors('items');
    }

    public function test_only_one_extra_bed_per_room_is_allowed(): void
    {
        $this->postJson(route('bookings.store'), $this->payload([
            'items' => [['room' => 'double', 'qty' => 1, 'extra_beds' => 2]],
            'adults' => 3,
        ]))->assertStatus(422)->assertJsonValidationErrors('items.0.extra_beds');
    }

    public function test_guest_details_and_privacy_consent_are_required(): void
    {
        $this->postJson(route('bookings.store'), $this->payload([
            'first_name' => '', 'last_name' => '', 'phone' => 'call me', 'privacy' => false,
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name', 'phone', 'privacy']);

        $this->assertSame(0, Booking::count());
    }

    public function test_country_is_optional_for_a_booking_request(): void
    {
        Mail::fake();

        $payload = $this->payload();
        unset($payload['country']);

        $this->postJson(route('bookings.store'), $payload)
            ->assertCreated()
            ->assertJsonPath('status', 'requested');

        $this->assertDatabaseHas('bookings', ['country' => 'Not specified', 'status' => 'requested']);
    }

    public function test_booking_stores_guest_details(): void
    {
        Mail::fake();

        $this->postJson(route('bookings.store'), $this->payload())->assertCreated();

        $booking = Booking::first();
        $this->assertSame('Nino Beridze', $booking->guestName());
        $this->assertSame('Georgia', $booking->country);
        $this->assertNotNull($booking->privacy_accepted_at);
        $this->assertSame('12 Rustaveli Ave, Tbilisi, Georgia', $booking->address);
        $this->assertSame('0108', $booking->postal_code);
        $this->get(route('privacy'))->assertOk()->assertSee('What we collect');
    }

    public function test_google_maps_loads_only_with_a_key(): void
    {
        config(['hotel.google.maps_key' => '']);
        $this->get(route('contact'))->assertDontSee('MAGNOLIA_MAPS', false)->assertSee('output=embed', false);

        config(['hotel.google.maps_key' => 'test-key']);
        $this->get(route('contact'))->assertSee('MAGNOLIA_MAPS', false)->assertSee('data-hotel-map', false);
    }

    public function test_invalid_input_is_rejected(): void
    {
        $this->postJson(route('bookings.store'), $this->payload(['email' => 'nope', 'checkin' => '2000-01-01', 'items' => [['room' => 'penthouse', 'qty' => 1]]]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'checkin', 'items.0.room']);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'items' => [['room' => 'double', 'qty' => 1]],
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
            'address' => '12 Rustaveli Ave, Tbilisi, Georgia',
            'city' => 'Tbilisi',
            'postal_code' => '0108',
            'arrival' => 'Evening',
            'notes' => null,
            'privacy' => true,
        ], $override);
    }
}
