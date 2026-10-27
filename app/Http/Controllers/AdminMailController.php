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
        ];
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
