<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingRequestAcknowledged extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        $replyTo = config('hotel.contact.email');

        return new Envelope(
            subject: 'We received your request '.$this->booking->reference.' | Hotel Magnolia Tbilisi',
            replyTo: filter_var($replyTo, FILTER_VALIDATE_EMAIL) ? [new Address($replyTo, 'Hotel Magnolia Tbilisi')] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.booking-guest-received',
            text: 'mail.booking-guest-received-text',
        );
    }
}
