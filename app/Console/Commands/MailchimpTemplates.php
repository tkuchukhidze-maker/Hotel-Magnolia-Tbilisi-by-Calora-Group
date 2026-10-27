<?php

namespace App\Console\Commands;

use App\Support\BookingMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Builds the two booking email templates with Mailchimp merge tags and uploads them
 * to Mailchimp Transactional (Mandrill) as "magnolia-booking-guest" / "magnolia-booking-admin".
 *
 *   php artisan magnolia:mailchimp-templates          # upload (create or update) and publish
 *   php artisan magnolia:mailchimp-templates --dry    # only write the HTML to storage/app/mail-templates
 *
 * Uses the Mandrill API key from MAIL_PASSWORD (or MANDRILL_API_KEY). Then set
 * HOTEL_MAILCHIMP_TEMPLATES=true so booking emails are rendered by Mailchimp.
 */
class MailchimpTemplates extends Command
{
    protected $signature = 'magnolia:mailchimp-templates {--dry : Only write the HTML files}';

    protected $description = 'Create or update the Magnolia booking email templates in Mailchimp Transactional';

    public function handle(): int
    {
        $tags = BookingMail::tags();
        $templates = [
            BookingMail::GUEST_TEMPLATE => ['mail.booking-guest-received', "You're booked: *|CHECKIN_DAY|* – *|CHECKOUT_DAY|* at Hotel Magnolia Tbilisi", 'Hotel Magnolia Tbilisi'],
            BookingMail::ADMIN_TEMPLATE => ['mail.booking-admin-received', 'New booking: *|GUEST_NAME|* · *|CHECKIN_DAY|* – *|CHECKOUT_DAY|*', 'Magnolia bookings'],
        ];

        $dir = storage_path('app/mail-templates');
        @mkdir($dir, 0775, true);
        $key = (string) (env('MANDRILL_API_KEY') ?: config('mail.mailers.smtp.password'));

        foreach ($templates as $name => [$view, $subject, $fromName]) {
            $html = view($view, ['v' => $tags])->render();
            file_put_contents($dir.'/'.$name.'.html', $html);
            $this->line("Built {$name} (".strlen($html).' bytes) → storage/app/mail-templates/'.$name.'.html');

            if ($this->option('dry')) {
                continue;
            }
            if ($key === '' || str_starts_with($key, 'YOUR_')) {
                $this->error('No Mandrill API key. Put it in MAIL_PASSWORD (or MANDRILL_API_KEY) and run again.');

                return self::FAILURE;
            }

            $payload = [
                'key' => $key, 'name' => $name, 'subject' => $subject,
                'from_email' => config('mail.from.address'), 'from_name' => $fromName,
                'code' => $html, 'publish' => true, 'labels' => ['magnolia', 'booking'],
            ];
            $exists = Http::asJson()->post('https://mandrillapp.com/api/1.0/templates/info.json', ['key' => $key, 'name' => $name])->successful();
            $res = Http::asJson()->timeout(20)->post('https://mandrillapp.com/api/1.0/templates/'.($exists ? 'update' : 'add').'.json', $payload);

            if (! $res->successful()) {
                $this->error("Mailchimp refused {$name}: ".($res->json('message') ?? $res->body()));

                return self::FAILURE;
            }
            $this->info(($exists ? 'Updated' : 'Created')." and published {$name} in Mailchimp Transactional.");
        }

        if (! $this->option('dry')) {
            $this->line('Next: set HOTEL_MAILCHIMP_TEMPLATES=true in the environment and deploy.');
        }

        return self::SUCCESS;
    }
}
