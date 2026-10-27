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

    public function test_guest_confirmation_explicitly_says_request_is_not_confirmed(): void
    {
        Mail::fake();
        config(['hotel.bookings_email' => 'frontdesk@example.net']);

        $this->postJson(route('bookings.store'), $this->payload())->assertCreated();

        $mail = new BookingRequestAcknowledged(Booking::firstOrFail());
        $html = $mail->render();
        $this->assertStringContainsString('not a confirmed reservation yet', $html);
        $this->assertStringContainsString('check room availability', $html);
        $this->assertStringContainsString('GEL', $html);
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
}
