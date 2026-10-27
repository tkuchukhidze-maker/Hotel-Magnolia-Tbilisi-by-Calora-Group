<?php

namespace App\Mail\Concerns;

use App\Support\BookingMail;
use Symfony\Component\Mime\Email;

/**
 * When HOTEL_MAILCHIMP_TEMPLATES=true, ask Mailchimp Transactional (over SMTP) to
 * render its stored template with our values instead of the HTML we send.
 * The HTML from our own view stays in the message as the fallback.
 */
trait UsesMailchimpTemplate
{
    protected function useMailchimpTemplate(string $template): void
    {
        if (! config('hotel.mailchimp_templates')) {
            return;
        }

        $this->withSymfonyMessage(function (Email $message) use ($template) {
            $headers = $message->getHeaders();
            $headers->addTextHeader('X-MC-Template', $template);
            $headers->addTextHeader('X-MC-MergeLanguage', 'mailchimp');
            $headers->addTextHeader('X-MC-MergeVars', BookingMail::mergeVarsHeader($this->booking));
        });
    }
}
