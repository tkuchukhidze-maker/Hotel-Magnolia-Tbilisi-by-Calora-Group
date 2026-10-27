<?php

namespace App\Mail;

use App\Support\BookingMail;
use App\Support\GuestOffers;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

/** A marketing offer to one past guest, with an unsubscribe link. */
class GuestOfferEmail extends Mailable
{
    use Queueable, SerializesModels;

    public array $v;

    public function __construct(public array $offer, public string $email, public string $firstName = '')
    {
        $this->v = GuestOffers::vars($offer, $email, $firstName);
        $unsub = GuestOffers::unsubscribeUrl($email);

        $this->withSymfonyMessage(function (Email $message) use ($unsub) {
            $h = $message->getHeaders();
            $h->addTextHeader('List-Unsubscribe', '<'.$unsub.'>');
            $h->addTextHeader('X-MC-Tags', 'guest-offer');
            $h->addTextHeader('X-MC-Track', 'opens,clicks_htmlonly');
        });
    }

    public function envelope(): Envelope
    {
        $reply = BookingMail::replyTo();

        return new Envelope(
            subject: $this->offer['subject'],
            replyTo: $reply ? [new Address($reply, 'Hotel Magnolia Tbilisi')] : [],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.offer', text: 'mail.offer-text', with: ['v' => $this->v]);
    }
}
