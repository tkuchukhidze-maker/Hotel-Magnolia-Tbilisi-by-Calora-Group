<?php

namespace Tests\Feature;

use App\Support\RoomPricing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminToolsTest extends TestCase
{
    use RefreshDatabase;

    private function admin()
    {
        return $this->withSession(['magnolia_admin' => true]);
    }

    /* ---------- Pricing calendar: bulk edit ---------- */

    public function test_calendar_page_has_selectable_days(): void
    {
        $this->admin()->get(route('admin.pricing.index', ['room' => 'double', 'plan' => 'room', 'month' => '2027-08']))
            ->assertOk()->assertSee('Pricing calendar')
            ->assertSee('data-date="2027-08-31"', false)
            ->assertSee(route('admin.pricing.bulk'), false);
    }

    public function test_bulk_set_price_for_picked_nights_rooms_and_plans(): void
    {
        $this->admin()->post(route('admin.pricing.bulk'), [
            'dates' => ['2027-08-07', '2027-08-08'],
            'rooms' => ['double', 'deluxe'],
            'plans' => ['room', 'bf'],
            'mode' => 'set',
            'one_guest' => 150,
            'two_guests' => 200,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseCount('room_day_prices', 8);
        $this->get(route('booking-prices.preview', ['checkin' => '2027-08-07', 'nights' => 2]))
            ->assertOk()->assertJsonPath('rooms.double.room.two', 400)->assertJsonPath('rooms.deluxe.bf.one', 300);
    }

    public function test_bulk_set_can_change_only_one_occupancy(): void
    {
        $this->admin()->post(route('admin.pricing.bulk'), [
            'dates' => ['2027-08-07'], 'rooms' => ['double'], 'plans' => ['room'], 'mode' => 'set', 'two_guests' => 180,
        ])->assertSessionHas('success');

        $p = RoomPricing::resolveDates('double', 'room', ['2027-08-07'])['2027-08-07'];
        $this->assertSame(180, $p['two']);
        $this->assertSame(config('hotel.rooms.double.p1.0'), $p['one']); // unchanged
        $this->assertSame('day', $p['source']);
    }

    public function test_bulk_raise_by_percent_and_lower_by_amount(): void
    {
        $base = config('hotel.rooms.double.p2.0'); // 129 while the offer runs

        $this->admin()->post(route('admin.pricing.bulk'), [
            'dates' => ['2027-08-07'], 'rooms' => ['double'], 'plans' => ['room'], 'mode' => 'percent', 'value' => 10,
        ])->assertSessionHas('success');
        $this->assertSame((int) round($base * 1.1), RoomPricing::resolveDates('double', 'room', ['2027-08-07'])['2027-08-07']['two']);

        $this->admin()->post(route('admin.pricing.bulk'), [
            'dates' => ['2027-08-07'], 'rooms' => ['double'], 'plans' => ['room'], 'mode' => 'amount', 'value' => -20,
        ])->assertSessionHas('success');
        $this->assertSame((int) round($base * 1.1) - 20, RoomPricing::resolveDates('double', 'room', ['2027-08-07'])['2027-08-07']['two']);
    }

    public function test_bulk_range_with_weekdays_and_reset(): void
    {
        // Every Saturday in March 2027 (6, 13, 20, 27).
        $this->admin()->post(route('admin.pricing.bulk'), [
            'from' => '2027-03-01', 'to' => '2027-03-31', 'weekdays' => [6],
            'rooms' => ['twin'], 'plans' => ['room'], 'mode' => 'set', 'one_guest' => 140, 'two_guests' => 170,
        ])->assertSessionHas('success');
        $this->assertSame(4, DB::table('room_day_prices')->where('room_type', 'twin')->count());

        $this->admin()->post(route('admin.pricing.bulk'), [
            'dates' => ['2027-03-06', '2027-03-13'], 'rooms' => ['twin'], 'plans' => ['room'], 'mode' => 'reset',
        ])->assertSessionHas('success');
        $this->assertSame(2, DB::table('room_day_prices')->where('room_type', 'twin')->count());
    }

    public function test_bulk_needs_dates_and_admin(): void
    {
        $this->post(route('admin.pricing.bulk'), ['rooms' => ['double'], 'plans' => ['room'], 'mode' => 'set', 'two_guests' => 99])
            ->assertForbidden();
        $this->admin()->post(route('admin.pricing.bulk'), ['rooms' => ['double'], 'plans' => ['room'], 'mode' => 'set', 'two_guests' => 99])
            ->assertSessionHasErrors('dates');
        $this->admin()->post(route('admin.pricing.bulk'), ['dates' => ['2027-01-01'], 'rooms' => ['double'], 'plans' => ['room'], 'mode' => 'percent', 'value' => -100])
            ->assertSessionHasErrors('value');
    }

    /* ---------- Photos ---------- */

    public function test_replace_a_room_photo_and_restore_it(): void
    {
        Storage::fake('public');

        $this->admin()->post(route('admin.media.slot', 'room.double'), [
            'photo' => UploadedFile::fake()->image('double.jpg', 1600, 1000),
            'alt' => 'New double room photo',
        ])->assertRedirect()->assertSessionHas('success');

        $row = DB::table('site_photos')->where('slot', 'room.double')->first();
        $this->assertNotNull($row);
        Storage::disk('public')->assertExists($row->path);
        $this->get(route('rooms'))->assertOk()->assertSee('New double room photo')->assertSee('/storage/'.$row->path, false);

        $this->admin()->delete(route('admin.media.slot.reset', 'room.double'))->assertRedirect();
        Storage::disk('public')->assertMissing($row->path);
        $this->get(route('rooms'))->assertSee('assets/img/room-double.webp', false);
    }

    public function test_gallery_upload_edit_reorder_hide_and_delete(): void
    {
        Storage::fake('public');
        $this->admin()->get(route('admin.media.index'))->assertOk()->assertSee('Gallery');

        $this->admin()->post(route('admin.media.gallery.add'), [
            'photos' => [UploadedFile::fake()->image('terrace.jpg', 1200, 800), UploadedFile::fake()->image('room.png', 800, 600)],
            'category' => 'rooms',
        ])->assertSessionHas('success');
        $new = DB::table('site_photos')->whereNull('slot')->orderByDesc('id')->first();

        $this->admin()->post(route('admin.media.gallery.update', $new->id), [
            'alt' => 'Sunset from the terrace', 'caption' => 'Golden hour.', 'categories' => ['spaces', 'details'], 'wide' => 1, 'is_visible' => 1,
        ])->assertSessionHas('success');
        $this->admin()->post(route('admin.media.gallery.move', $new->id), ['dir' => 'first'])->assertSessionHas('success');

        $first = DB::table('site_photos')->whereNull('slot')->orderBy('sort')->first();
        $this->assertSame($new->id, $first->id);
        $this->get(route('our-hotel'))->assertSee('Sunset from the terrace')->assertSee('Golden hour.');

        $this->admin()->post(route('admin.media.gallery.update', $new->id), ['alt' => 'Sunset from the terrace', 'is_visible' => 0])->assertSessionHas('success');
        $this->get(route('our-hotel'))->assertDontSee('Sunset from the terrace');

        $this->admin()->delete(route('admin.media.gallery.delete', $new->id))->assertSessionHas('success');
        Storage::disk('public')->assertMissing($new->path);
        $this->assertDatabaseMissing('site_photos', ['id' => $new->id]);
    }

    /* ---------- Services ---------- */

    public function test_amenities_are_managed_and_shown(): void
    {
        $this->get(route('our-hotel'))->assertSee('Free parking');

        $this->admin()->post(route('admin.services.store'), [
            'kind' => 'amenity', 'name' => 'Rooftop yoga mats', 'description' => 'Borrow one any time.', 'icon' => 'leaf', 'is_active' => 1,
        ])->assertRedirect();
        $this->get(route('our-hotel'))->assertSee('Rooftop yoga mats');

        $id = DB::table('hotel_services')->where('name', 'Free parking')->value('id');
        $this->admin()->patch(route('admin.services.toggle', $id))->assertSessionHas('success');
        $this->get(route('our-hotel'))->assertDontSee('Rare in the old town');
    }

    public function test_paid_extra_is_offered_and_charged_on_the_server(): void
    {
        Mail::fake();
        $this->admin()->post(route('admin.services.store'), [
            'kind' => 'extra', 'name' => 'Airport transfer', 'description' => 'We meet you at arrivals.', 'icon' => 'car',
            'price' => 60, 'price_unit' => 'stay', 'is_active' => 1,
        ])->assertRedirect();
        $this->admin()->post(route('admin.services.store'), [
            'kind' => 'extra', 'name' => 'Late check-out', 'icon' => 'clock', 'price' => 20, 'price_unit' => 'room_night', 'is_active' => 0,
        ]);
        $transfer = DB::table('hotel_services')->where('name', 'Airport transfer')->value('id');
        $late = DB::table('hotel_services')->where('name', 'Late check-out')->value('id');

        $this->get(route('rooms'))->assertSee('Airport transfer')->assertDontSee('Late check-out');

        $payload = [
            'items' => [['room' => 'double', 'qty' => 1]], 'rate' => 'room', 'checkin' => now()->addDays(10)->toDateString(),
            'nights' => 2, 'adults' => 2, 'children' => 0, 'children_ages' => [],
            'first_name' => 'Nino', 'last_name' => 'Beridze', 'email' => 'guest@example.com', 'phone' => '+995 555 00 00 00',
            'country' => 'Georgia', 'privacy' => true,
        ];
        $rooms = $this->postJson(route('bookings.store'), $payload)->assertCreated()->json('total');

        $this->postJson(route('bookings.store'), $payload + ['extras' => [$transfer]])
            ->assertCreated()->assertJsonPath('extras_total', 60)->assertJsonPath('total', $rooms + 60);
        $this->assertSame('Airport transfer', \App\Models\Booking::latest('id')->first()->extras[0]['name']);

        // A hidden extra can't be booked.
        $this->postJson(route('bookings.store'), $payload + ['extras' => [$late]])
            ->assertStatus(422)->assertJsonValidationErrors('extras');
    }

    public function test_extra_needs_a_price(): void
    {
        $this->admin()->post(route('admin.services.store'), ['kind' => 'extra', 'name' => 'Tour', 'icon' => 'map'])
            ->assertSessionHasErrors('price');
    }

    /* ---------- Email delivery check ---------- */

    public function test_mail_test_reports_when_email_is_switched_off_and_sends_when_configured(): void
    {
        config(['mail.default' => 'log']);
        $this->admin()->get(route('admin.dashboard'))->assertOk()->assertSee('Not sending');
        $this->admin()->post(route('admin.mail.test'), ['to' => 'manager@example.com'])->assertSessionHasErrors('mail');

        Mail::fake();
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => 'smtp.mandrillapp.com']);
        $this->admin()->get(route('admin.dashboard'))->assertSee('Mailchimp Transactional');
        $this->admin()->post(route('admin.mail.test'), ['to' => 'manager@example.com'])->assertSessionHas('success');
    }
}
