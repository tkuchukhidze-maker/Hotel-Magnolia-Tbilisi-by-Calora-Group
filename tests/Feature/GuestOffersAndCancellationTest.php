<?php

namespace Tests\Feature;

use App\Mail\BookingCancelled;
use App\Mail\GuestOfferEmail;
use App\Models\Booking;
use App\Support\GuestOffers;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GuestOffersAndCancellationTest extends TestCase
{
    use RefreshDatabase;

    private function admin()
    {
        return $this->withSession(['magnolia_admin' => true]);
    }

    private function booking(array $over = []): Booking
    {
        static $n = 0;
        $n++;

        return Booking::create(array_merge([
            'reference' => 'MG-T'.$n, 'items' => [['room' => 'double', 'name' => 'Double Room', 'qty' => 1, 'stay_total' => 258]],
            'rate' => 'room', 'checkin' => now()->addDays(5)->toDateString(), 'checkout' => now()->addDays(7)->toDateString(),
            'nights' => 2, 'adults' => 2, 'children' => 0, 'nightly_price' => 129, 'total' => 258, 'privacy_accepted_at' => now(),
            'first_name' => 'Nino', 'last_name' => 'Beridze', 'email' => "guest{$n}@example.com", 'phone' => '+995 555 00 00 00',
            'country' => 'Georgia', 'status' => 'requested',
        ], $over));
    }

    private function offer(array $over = []): array
    {
        return array_merge([
            'subject' => '25% off your next stay', 'headline' => 'Your next stay, 25% off',
            'body' => "Thank you for staying with us.\nCome back soon.", 'discount' => 25, 'code' => 'BACK25',
            'button_label' => 'See the offer', 'button_url' => 'https://magnoliatbilisi.com/offer',
            'valid_until' => '2099-11-30', 'audience' => 'all', 'extra' => '',
        ], $over);
    }

    /* ---------- Cancelling ---------- */

    public function test_cancel_on_guest_request_emails_the_guest(): void
    {
        Mail::fake();
        $b = $this->booking();

        $this->admin()->post(route('admin.bookings.cancel', $b), ['reason' => 'guest', 'note' => 'See you <soon>', 'notify' => 1])
            ->assertRedirect()->assertSessionHas('success');

        $b->refresh();
        $this->assertSame('cancelled', $b->status);
        $this->assertSame('guest', $b->cancel_reason);
        $this->assertNotNull($b->cancelled_at);
        $this->assertNotNull($b->cancel_emailed_at);

        Mail::assertSent(BookingCancelled::class, function (BookingCancelled $m) use ($b) {
            $html = $m->render();

            return $m->hasTo($b->email)
                && str_contains($m->envelope()->subject, $b->reference)
                && str_contains($html, 'Your booking is cancelled')
                && str_contains($html, 'See you &lt;soon&gt;')
                && str_contains($html, route('rooms'));
        });
    }

    public function test_no_show_sets_status_and_uses_no_show_wording(): void
    {
        Mail::fake();
        $b = $this->booking();

        $this->admin()->post(route('admin.bookings.cancel', $b), ['reason' => 'no_show', 'notify' => 1])->assertSessionHas('success');

        $this->assertSame('no_show', $b->fresh()->status);
        Mail::assertSent(BookingCancelled::class, fn ($m) => str_contains($m->envelope()->subject, 'We missed you')
            && str_contains($m->render(), 'We were expecting you'));
        $this->admin()->get(route('admin.dashboard'))->assertOk()->assertSee('Guest did not arrive (no-show)')->assertSee('✓ guest emailed');
    }

    public function test_cancel_without_email(): void
    {
        Mail::fake();
        $b = $this->booking();

        $this->admin()->post(route('admin.bookings.cancel', $b), ['reason' => 'hotel', 'notify' => 0])->assertSessionHas('success');

        $this->assertSame('cancelled', $b->fresh()->status);
        $this->assertNull($b->fresh()->cancel_emailed_at);
        Mail::assertNothingSent();
    }

    public function test_cancel_needs_admin_and_a_valid_reason(): void
    {
        $b = $this->booking();
        $this->post(route('admin.bookings.cancel', $b), ['reason' => 'guest'])->assertForbidden();
        $this->admin()->post(route('admin.bookings.cancel', $b), ['reason' => 'bored'])->assertSessionHasErrors('reason');
        $this->assertSame('requested', $b->fresh()->status);
    }

    public function test_dashboard_shows_cancel_form(): void
    {
        $b = $this->booking();
        $this->admin()->get(route('admin.dashboard'))->assertOk()
            ->assertSee(route('admin.bookings.cancel', $b), false)->assertSee('Cancel booking');
    }

    /* ---------- Offers ---------- */

    public function test_offers_page_counts_unique_guests(): void
    {
        $this->booking(['email' => 'a@example.com']);
        $this->booking(['email' => 'A@example.com']);
        $this->booking(['email' => 'b@example.com', 'status' => 'checked_out']);

        $this->admin()->get(route('admin.offers.index'))->assertOk()->assertSee('Offer emails')->assertSee('Everyone who has booked');
        $this->assertCount(2, GuestOffers::audience('all'));
        $this->assertSame(['b@example.com'], GuestOffers::audience('stayed')->keys()->all());
    }

    public function test_preview_renders_the_branded_email(): void
    {
        $this->admin()->post(route('admin.offers.preview'), $this->offer())->assertOk()
            ->assertSee('Your next stay, 25% off')->assertSee('25% off')->assertSee('BACK25')
            ->assertSee('Dear Nino,')->assertSee('Unsubscribe');
    }

    public function test_send_to_guests_skips_unsubscribed_and_records_recipients(): void
    {
        Mail::fake();
        $this->booking(['email' => 'one@example.com', 'first_name' => 'Ana']);
        $this->booking(['email' => 'two@example.com']);
        GuestOffers::unsubscribe('TWO@example.com');

        $this->admin()->post(route('admin.offers.send'), $this->offer(['extra' => "friend@example.com\nnot-an-email"]))
            ->assertSessionHasErrors('confirm');
        Mail::assertNothingSent();

        $this->admin()->post(route('admin.offers.send'), $this->offer(['extra' => "friend@example.com\nnot-an-email", 'confirm' => 1]))
            ->assertRedirect(route('admin.offers.index'))->assertSessionHas('success', 'Offer sent to 2 guests.');

        Mail::assertSent(GuestOfferEmail::class, 2);
        Mail::assertSent(GuestOfferEmail::class, fn ($m) => $m->hasTo('one@example.com') && str_contains($m->render(), 'Dear Ana,'));
        Mail::assertNotSent(GuestOfferEmail::class, fn ($m) => $m->hasTo('two@example.com'));

        $campaign = DB::table('offer_campaigns')->first();
        $this->assertSame(2, (int) $campaign->sent_count);
        $this->assertSame(2, DB::table('offer_recipients')->whereNotNull('sent_at')->count());
        $this->admin()->get(route('admin.offers.show', $campaign->id))->assertOk()->assertSee('friend@example.com');
    }

    public function test_test_send_goes_to_one_address(): void
    {
        Mail::fake();
        $this->booking();
        $this->admin()->post(route('admin.offers.test'), $this->offer(['test_to' => 'me@example.com']))->assertSessionHas('success');
        Mail::assertSent(GuestOfferEmail::class, 1);
        Mail::assertSent(GuestOfferEmail::class, fn ($m) => $m->hasTo('me@example.com'));
    }

    public function test_offers_need_admin(): void
    {
        $this->get(route('admin.offers.index'))->assertForbidden();
        $this->post(route('admin.offers.send'), $this->offer(['confirm' => 1]))->assertForbidden();
    }

    public function test_unsubscribe_link_asks_then_unsubscribes(): void
    {
        $url = GuestOffers::unsubscribeUrl('Guest@Example.com');
        $token = basename(parse_url($url, PHP_URL_PATH));

        $this->get($url)->assertOk()->assertSee('Stop offer emails?')->assertSee('guest@example.com');
        $this->assertSame(0, DB::table('email_unsubscribes')->count());

        $this->post(route('unsubscribe.store', $token))->assertOk()->assertSee("You're unsubscribed", false);
        $this->assertTrue(DB::table('email_unsubscribes')->where('email', 'guest@example.com')->exists());

        $this->get(route('unsubscribe.show', 'not-a-real-token'))->assertNotFound();
    }
}
