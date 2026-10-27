<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

/** Email delivery check: shows how mail is configured and sends a test message. */
class AdminMailController extends Controller
{
    private function guard(Request $request): void
    {
        abort_unless($request->session()->get('magnolia_admin') === true, 403);
    }

    public static function status(): array
    {
        $mailer = (string) config('mail.default');
        $host = (string) config('mail.mailers.smtp.host');

        return [
            'mailer' => $mailer,
            'host' => $host,
            'from' => (string) config('mail.from.address'),
            'mandrill' => $mailer === 'smtp' && str_contains($host, 'mandrillapp.com'),
            'sends' => ! in_array($mailer, ['log', 'array'], true),
            'to' => (string) config('hotel.bookings_email'),
            // Only shapes, never the secret itself: helps spot a placeholder or a stray space.
            'user_set' => trim((string) config('mail.mailers.smtp.username')) !== '',
            'key_check' => self::keyCheck((string) config('mail.mailers.smtp.password')),
            'port' => (string) config('mail.mailers.smtp.port'),
            'scheme' => (string) (config('mail.mailers.smtp.scheme') ?? ''),
        ];
    }

    private static function keyCheck(string $key): string
    {
        if ($key === '') return 'missing';
        if (str_starts_with($key, 'YOUR_')) return 'still the placeholder';
        if ($key !== trim($key) || str_contains($key, '"') || str_contains($key, "'")) return 'has spaces or quotes around it';
        if (! str_starts_with($key, 'md-')) return 'does not start with md-';

        return 'looks like a Mandrill key ('.strlen($key).' characters)';
    }

    /** Post an example booking to the Zapier Catch Hook so the Zap can be set up and tested. */
    public function zapierSample(Request $request)
    {
        $this->guard($request);
        if (! config('hotel.zapier_hook_url')) {
            return back()->withErrors(['mail' => 'Zapier is not connected: set HOTEL_ZAPIER_HOOK_URL in Laravel Cloud and deploy.']);
        }
        $sample = \App\Support\BookingMail::sample();
        $sample->email = (string) ($request->input('to') ?: config('hotel.bookings_email'));

        $error = \App\Support\BookingWebhook::send($sample);

        return $error === null
            ? back()->with('success', 'Example booking sent to Zapier. In the Zap, click "Find new records" on step 1.')
            : back()->withErrors(['mail' => 'Zapier did not get the example booking: '.str($error)->limit(300)]);
    }

    public function test(Request $request)
    {
        $this->guard($request);
        $to = $request->validate(['to' => ['nullable', 'email', 'max:160']])['to'] ?? config('hotel.bookings_email');
        $s = self::status();

        if (! $s['sends']) {
            return back()->withErrors(['mail' => 'Email is switched off (MAIL_MAILER='.$s['mailer'].'). Messages are only written to the log. Set the Mailchimp Transactional settings in Laravel Cloud first.']);
        }

        try {
            Mail::raw(
                "This is a test from the Magnolia Management panel.\n\nIf you can read this, booking emails from the website are delivered.\n\nMailer: {$s['mailer']} ({$s['host']})\nFrom: {$s['from']}\nSent: ".now()->toDayDateTimeString(),
                fn ($m) => $m->to($to)->subject('Test email · '.config('hotel.name'))
            );
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['mail' => 'Sending failed: '.str($e->getMessage())->limit(300)]);
        }

        return back()->with('success', 'Test email sent to '.$to.'. Check the inbox (and spam). In Mailchimp Transactional it appears under Outbound → Activity.');
    }
}
