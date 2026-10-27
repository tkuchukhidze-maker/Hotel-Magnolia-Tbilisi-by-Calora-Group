<?php

namespace App\Mail;

use App\Models\Booking;
use App\Support\BookingMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Email to the guest when the hotel cancels a booking: guest asked, no-show, or the hotel had to. */
class BookingCancelled extends Mailable
{
    use Queueable, SerializesModels;

    public const REASONS = [
        'guest' => 'Guest asked to cancel',
        'no_show' => 'Guest did not arrive (no-show)',
        'hotel' => 'Cancelled by the hotel',
    ];

    public function __construct(public Booking $booking) {}

    /** Wording for each reason. */
    public static function copy(Booking $b): array
    {
        $dates = $b->checkin->format('j F').' – '.$b->checkout->format('j F Y');
        $note = trim((string) $b->cancel_note);

        [$label, $title, $lead] = match ($b->cancel_reason) {
            'no_show' => [
                'WE MISSED YOU',
                'We were expecting you',
                'Your room was ready on '.$b->checkin->format('l, j F').', but you didn\'t arrive, so we have cancelled your booking ('.$dates.'). We hope everything is all right. If you would still like to come, we\'d be glad to find you new dates.',
            ],
            'hotel' => [
                'BOOKING CANCELLED',
                'Your booking has been cancelled',
                'We\'re sorry, but we had to cancel your booking for '.$dates.'. We would be happy to help you find other dates.',
            ],
            default => [
                'BOOKING CANCELLED',
                'Your booking is cancelled',
                'As you asked, we have cancelled your booking for '.$dates.'. We hope to welcome you another time.',
            ],
        };

        return [
            'label' => $label,
            'title' => $title,
            'lead' => $lead,
            'note' => $note,
            'note_html' => $note !== '' ? nl2br(e($note)) : '',
            'book_url' => route('rooms'),
        ];
    }

    public function envelope(): Envelope
    {
        $reply = BookingMail::replyTo();
        $subject = $this->booking->cancel_reason === 'no_show'
            ? 'We missed you: booking '.$this->booking->reference.' is cancelled'
            : 'Your booking '.$this->booking->reference.' is cancelled';

        return new Envelope(
            subject: $subject.' · Hotel Magnolia Tbilisi',
            replyTo: $reply ? [new Address($reply, 'Hotel Magnolia Tbilisi')] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.booking-cancelled',
            text: 'mail.booking-cancelled-text',
            with: ['v' => BookingMail::vars($this->booking), 'c' => self::copy($this->booking)],
        );
    }
}
