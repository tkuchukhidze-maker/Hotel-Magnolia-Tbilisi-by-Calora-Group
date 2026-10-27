<?php

namespace Tests\Feature;

use App\Mail\BookingReceived;
use App\Mail\BookingRequestAcknowledged;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BookingEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        return [
            'items' => [['room' => 'double', 'qty' => 1]],
            'rate' => 'room',
            'checkin' => now()->addDays(10)->toDateString(),
            'nights' => 2,
            'adults' => 2,
            'children' => 0,
            'children_ages' => [],
            'first_name' => 'Nino',
            'last_name' => 'Beridze',
            'email' => 'nino@example.net',
            'phone' => '+995555123456',
            'country' => 'Georgia',
            'privacy' => true,
        ];
    }

    public function test_booking_sends_guest_acknowledgement_and_separate_manager_alert(): void
    {
        Mail::fake();
        config([
            'hotel.bookings_email' => 'frontdesk@example.net',
            'hotel.contact.email' => 'contact@example.net',
        ]);

        $response = $this->postJson(route('bookings.store'), $this->payload())
            ->assertCreated();

        $reference = $response->json('reference');
        $this->assertNotEmpty($reference);
        $this->assertDatabaseHas('bookings', [
            'reference' => $reference,
            'email' => 'nino@example.net',
            'status' => 'requested',
        ]);

        Mail::assertSent(BookingRequestAcknowledged::class, fn ($mail) =>
            $mail->hasTo('nino@example.net') && $mail->booking->reference === $reference
        );
        Mail::assertSent(BookingReceived::class, fn ($mail) =>
            $mail->hasTo('frontdesk@example.net') && $mail->booking->reference === $reference
        );
        Mail::assertSentCount(2);
    }

    public function test_default_manager_email_is_the_episodegroup_inbox(): void
    {
        Mail::fake();

        $this->assertSame('K.tsitlidze@episodegroup.ge', config('hotel.bookings_email'));

        $this->postJson(route('bookings.store'), $this->payload())->assertCreated();

        Mail::assertSent(BookingReceived::class, fn ($mail) =>
            $mail->hasTo('K.tsitlidze@episodegroup.ge')
        );
    }

    public function test_guest_email_shows_the_booking_in_the_magnolia_design(): void
    {
        Mail::fake();
        config(['hotel.bookings_email' => 'frontdesk@example.net']);

        $this->postJson(route('bookings.store'), $this->payload())->assertCreated();
        $booking = Booking::firstOrFail();

        $html = (new BookingRequestAcknowledged($booking))->render();
        $this->assertStringContainsString('Congratulations, Nino!', $html);
        $this->assertStringContainsString('CHECK-IN', $html);
        $this->assertStringContainsString($booking->checkin->format('D j M'), $html);
        $this->assertStringContainsString($booking->checkout->format('D j M'), $html);
        $this->assertStringContainsString($booking->reference, $html);
        $this->assertStringContainsString('GEL', $html);

        $admin = (new BookingReceived($booking))->render();
        $this->assertStringContainsString('Nino Beridze is coming', $admin);
        $this->assertStringContainsString('+995555123456', $admin);
    }

    public function test_guest_input_is_escaped_in_the_emails(): void
    {
        Mail::fake();
        config(['hotel.bookings_email' => 'frontdesk@example.net']);
        $this->postJson(route('bookings.store'), ['first_name' => '<b>Nino</b>', 'notes' => '<script>x</script>'] + $this->payload())->assertCreated();

        $admin = (new BookingReceived(Booking::firstOrFail()))->render();
        $this->assertStringNotContainsString('<script>x</script>', $admin);
        $this->assertStringContainsString('&lt;b&gt;Nino&lt;/b&gt;', $admin);
    }

    public function test_mailchimp_template_headers_are_added_when_switched_on(): void
    {
        Mail::fake();
        config(['hotel.bookings_email' => 'frontdesk@example.net', 'hotel.mailchimp_templates' => true]);
        $this->postJson(route('bookings.store'), $this->payload())->assertCreated();

        $message = (new BookingRequestAcknowledged(Booking::firstOrFail()));
        $headers = [];
        foreach ($message->callbacks as $cb) {
            $email = new \Symfony\Component\Mime\Email();
            $cb($email);
            foreach (['X-MC-Template', 'X-MC-MergeVars'] as $h) {
                if ($email->getHeaders()->has($h)) $headers[$h] = $email->getHeaders()->get($h)->getBodyAsString();
            }
        }
        $this->assertSame('magnolia-booking-guest', $headers['X-MC-Template'] ?? null);
        $this->assertSame('Nino', json_decode($headers['X-MC-MergeVars'], true)['GUEST_FIRST']);
    }

    public function test_an_invalid_manager_address_does_not_prevent_guest_email(): void
    {
        Mail::fake();
        config(['hotel.bookings_email' => '']);

        $this->postJson(route('bookings.store'), $this->payload())->assertCreated();
        Mail::assertSent(BookingRequestAcknowledged::class, 1);
        Mail::assertNotSent(BookingReceived::class);
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_mailchimp_templates_are_built_with_merge_tags(): void
    {
        $this->artisan('magnolia:mailchimp-templates', ['--dry' => true])->assertSuccessful();

        $guest = file_get_contents(storage_path('app/mail-templates/magnolia-booking-guest.html'));
        $this->assertStringContainsString('*|GUEST_FIRST|*', $guest);
        $this->assertStringContainsString('*|CHECKIN_DAY|*', $guest);
        $this->assertStringContainsString('*|LINES_HTML|*', $guest);
        $admin = file_get_contents(storage_path('app/mail-templates/magnolia-booking-admin.html'));
        $this->assertStringContainsString('*|GUEST_PHONE|*', $admin);
    }
}
