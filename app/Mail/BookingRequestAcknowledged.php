<?php

namespace App\Mail;

use App\Mail\Concerns\UsesMailchimpTemplate;
use App\Models\Booking;
use App\Support\BookingMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Email to the guest right after booking: dates, rooms, total and how to find us. */
class BookingRequestAcknowledged extends Mailable
{
    use Queueable, SerializesModels, UsesMailchimpTemplate;

    public function __construct(public Booking $booking)
    {
        $this->useMailchimpTemplate(BookingMail::GUEST_TEMPLATE);
    }

    public function envelope(): Envelope
    {
        $reply = BookingMail::replyTo();

        return new Envelope(
            subject: 'You\'re booked: '.BookingMail::shortDates($this->booking).' at Hotel Magnolia Tbilisi',
            replyTo: $reply ? [new Address($reply, 'Hotel Magnolia Tbilisi')] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.booking-guest-received',
            text: 'mail.booking-guest-received-text',
            with: ['v' => BookingMail::vars($this->booking)],
        );
    }
}
