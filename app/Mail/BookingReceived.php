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

/** Email to the hotel: who is coming, when, which rooms, the total and how to reach the guest. */
class BookingReceived extends Mailable
{
    use Queueable, SerializesModels, UsesMailchimpTemplate;

    public function __construct(public Booking $booking)
    {
        $this->useMailchimpTemplate(BookingMail::ADMIN_TEMPLATE);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New booking: '.$this->booking->guestName().' · '.BookingMail::shortDates($this->booking).' · '.$this->booking->roomsSummary(),
            replyTo: [new Address($this->booking->email, $this->booking->guestName())],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.booking-admin-received',
            text: 'mail.booking-received',
            with: ['v' => BookingMail::vars($this->booking)],
        );
    }
}
