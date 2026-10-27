# Hotel Magnolia Tbilisi by Calora Group — website (Laravel)

Website for **Hotel Magnolia Tbilisi by Calora Group**: a small hotel in an old brick house in Old Tbilisi, with a 127-year-old magnolia in the courtyard.

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
APP_NAME="Hotel Magnolia Tbilisi by Calora Group"
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

## SEO & AEO

Everything is generated from `config/hotel.php`, so the markup always matches the page.

| What | Where |
| --- | --- |
| Unique title + description per page, canonical, Open Graph, Twitter card | `layouts/app.blade.php`, `@section('title'/'description'/'og_image')` in each page |
| `noindex` everywhere except `APP_ENV=production` | layout `robots` meta + `/robots.txt` |
| `/sitemap.xml` (with images), `/robots.txt` | `SeoController` |
| `/llms.txt` — plain-language summary + FAQ for AI assistants | `SeoController::llms` |
| JSON-LD: `Hotel` (home, rooms, contact), `HotelRoom` + `Offer` (rooms), `FAQPage` (contact), `BreadcrumbList` (inner pages), `WebSite` (home) | `app/Support/StructuredData.php` |
| Rooms rendered on the server (crawlers and AI bots see them without JS) | `pages/rooms.blade.php` |
| "At a glance" facts block, 11-question FAQ | `pages/our-hotel.blade.php`, `config('hotel.faq')` |
| Same name, address and phone on every page (footer) and a Google Maps link to the Business Profile | `partials/footer*.blade.php`, `HOTEL_GOOGLE_MAPS` |

After launch: add the site to Google Search Console and Bing Webmaster Tools, submit `/sitemap.xml`, put the website URL on the Google Business Profile, and check the JSON-LD with Google's Rich Results Test.

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

## Google Maps

With `GOOGLE_MAPS_API_KEY` set, the site shows a **brand-styled map** (Explore and Contact pages) with a magnolia pin, an info window and "Get directions", and the booking form gets **Google address autocomplete** (it also fills the guest's country). Without a key, a plain map embed and a normal address field are used.

1. Google Cloud → APIs & Services → enable **Maps JavaScript API**, **Places API (New)** and **Geocoding API**.
2. Credentials → create an API key → **Application restrictions: Websites** → add `https://your-domain.ge/*` (and the Laravel Cloud preview domain) → **API restrictions**: the three APIs above.
3. Put it in `.env`: `GOOGLE_MAPS_API_KEY=...`
4. Optional: set `HOTEL_LAT` / `HOTEL_LNG` to the exact pin position (right-click the hotel in Google Maps to copy them). Otherwise the address is looked up in the browser.

Code: `public/assets/js/maps.js`, `resources/views/partials/maps-config.blade.php`, `partials/map.blade.php`.
