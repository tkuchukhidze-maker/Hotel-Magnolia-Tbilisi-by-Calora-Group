# Magnolia Hotel Tbilisi — website (Laravel)

Website for **Magnolia Hotel Tbilisi** (Calora Group): a small hotel in an old brick house in Old Tbilisi, with a 127-year-old magnolia in the courtyard.

Brand idea: **A little different. A lot more personal.**

## Pages and routes

| Route | Name | View |
| --- | --- | --- |
| `GET /` | `home` | `pages/home.blade.php` — hero, booking bar, trust strip, the magnolia, room preview, book direct, reviews |
| `GET /rooms` | `rooms` | `pages/rooms.blade.php` — guests/nights stepper, room list, inline booking (rate → details → confirmation) |
| `GET /our-hotel` | `our-hotel` | `pages/our-hotel.blade.php` — story, courtyard, filterable gallery, extras |
| `GET /explore` | `explore` | `pages/explore.blade.php` — nearby places, getting here |
| `GET /contact` | `contact` | `pages/contact.blade.php` — contacts, good to know, FAQ |
| `POST /bookings` | `bookings.store` | JSON — saves a booking request, emails the hotel |

## Where things live

```
config/hotel.php                    contacts, offer, policies, rating, ROOMS AND PRICES
app/Http/Controllers/PageController.php
app/Http/Controllers/BookingController.php   validation + server-side price calculation
app/Models/Booking.php
app/Mail/BookingReceived.php         email to the hotel for each new booking
database/migrations/..._create_bookings_table.php
resources/views/layouts/app.blade.php
resources/views/partials/            header, footers, SVG sprite (magnolia motifs)
resources/views/pages/               the five pages
public/assets/css/styles.css         design tokens, components, mobile rules
public/assets/js/main.js             nav drawer, header, mobile booking bar, gallery filter
public/assets/js/rooms.js            booking flow (posts to /bookings)
public/assets/img/                   photos (WebP), favicon
tests/Feature/SiteTest.php           pages + booking tests
```

Most content edits (phone, prices, policies, offer dates) are in `config/hotel.php` or `.env` — no template changes needed.

## Setup

Requires PHP 8.2+ and Composer.

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite        # or set DB_* for MySQL/PostgreSQL in .env
php artisan migrate
php artisan serve                     # http://127.0.0.1:8000
php artisan test                      # run the tests
```

### .env settings for the hotel

```
APP_NAME="Magnolia Hotel Tbilisi"
APP_URL=https://your-domain.ge

HOTEL_PHONE_DISPLAY="+995 ..."
HOTEL_PHONE_TEL=+995...
HOTEL_WHATSAPP=995...
HOTEL_EMAIL=hello@your-domain.ge
HOTEL_EMAIL_DISPLAY=hello@your-domain.ge
HOTEL_ADDRESS="Street, number"
HOTEL_BOOKINGS_EMAIL=reservations@your-domain.ge

MAIL_MAILER=smtp      # until set, booking emails go to storage/logs/laravel.log
```

## Bookings

`POST /bookings` validates the request, checks room capacity, **recalculates the price on the server** from `config/hotel.php` (never trusts the browser), saves a `Booking` with a reference like `MG-7KQ2ZD`, and emails the hotel. It is rate-limited (10 requests/minute). Bookings are stored with status `requested` — staff confirm them with the guest and enter them in the PMS.

Availability is not checked yet: connect a PMS / channel manager before relying on "Only N left" labels or instant confirmation.

## Brand tokens

| Token | Hex | Use |
| --- | --- | --- |
| Terracotta | `#A94729` | primary buttons, eyebrows, accents |
| Warm Ivory | `#F5EBDD` | main background |
| Soft Gold | `#B99559` | details on dark backgrounds only |
| Dusty Blue | `#7793A5` | map, decorative blocks |
| Dark Cocoa | `#42271F` | text, dark sections, footer |

Fonts: Cormorant Garamond (headings) + Manrope (text).

## Before launch — to fill in

Search the views and `config/hotel.php` for `[` to find every placeholder: address and contacts, check-in/out times, cancellation deadline, payment methods, breakfast hours, children/pets policy, airport transfer, offer end date, price-match terms, real guest reviews and review count, Triple/Single photos, Google Map embed, building history.
