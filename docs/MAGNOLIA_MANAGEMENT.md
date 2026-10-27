# Magnolia Management — administrator operations

## URLs
- Staff login: /admin/login
- Staff dashboard: /admin
- Public hotel website: /

The dashboard and public website use the same Laravel database.

## Deployment checklist (authorized operator only)
1. Review the draft pull request and complete PHP/Laravel and browser tests.
2. Deploy and run php artisan migrate --force.
3. Set MAGNOLIA_ADMIN_USERNAME=admin and MAGNOLIA_ADMIN_PASSWORD_HASH as environment secrets in Laravel Cloud. The login uses a username, not an email. Generate a secure bcrypt hash using Laravel Hash::make. Never commit the plain password or hash to GitHub.
4. Confirm APP_KEY, APP_URL, mail credentials, HTTPS, secure session cookies and CSRF protection.
5. Run Laravel config cache refresh and verify sign-in and failed-login throttling.
6. Test rate changes against public Rooms page and server-calculated booking requests.
7. Back up the database and confirm status-change operations before enabling staff use.

This first version supports only one server-configured administrator. Never commit the plaintext password or password hash to the repository. Do not share one password among multiple employees. Before wider rollout add per-user roles, 2FA, audit logs, account revocation and explicit access controls.

## Implemented modules

Room rates: four GEL nightly tariff cells (one or two guests, with/without breakfast) per room type. Persisted in room_rates; overrides feed home/rooms pages and server-side BookingQuote for new requests. Existing booking price snapshots remain unchanged.

Booking requests: latest 100 records, contact, dates, room selections, amounts and manual status updates. Requested means not confirmed. Staff must independently check inventory before marking confirmed; no PMS inventory sync exists.

Promotions: name, code, percentage and valid dates. Saved as inactive drafts only; not yet applied to customer-facing checkout. Implement eligibility, stacking safeguards, valid-date checks and audited publication before enabling.

Dashboard: pending requests, today's incoming requests/arrivals, guests, and value of booking quotes. This value is NOT collected revenue, and occupancy metrics cannot be verified without complete inventory.

## Google integration roadmap

Stage 1 — Search: verify property ownership in Google Search Console, submit sitemap, monitor indexing.
Stage 2 — Measurement: provision GA4 and optional Google Tag Manager under a consent-aware implementation; events: view_room, select_room, begin_checkout and booking_request_sent. Treat booking_request_sent as a lead, not a paid booking.
Stage 3 — Listings: connect the official Google Business Profile with authorized Google account. Confirm location, photos, opening information and website URL.
Stage 4 — Hotel commerce: investigate Google Hotel Center / Hotel Ads and PMS/channel manager feed for real daily rates, inventory and confirmed reservations. Do not publish unverified live availability.
Stage 5 — Reporting: secure Looker Studio dashboards for source-to-request funnel and separately confirmed revenue after PMS/finance reconciliation.

OAuth credentials must be securely stored, with least privilege. Google services are NOT connected in the initial MVP: require user authorization and account identifiers first.

## Known limitations
- Laravel integration/E2E tests are not yet run.
- The dashboard is single-admin MVP, no per-user audit trail.
- PMS/live inventory is missing.
- Extra-bed supplements, Triple Room 3-guest tariff, complete room photo galleries, and cancellation policies require verification.
- Promotions are inactive drafts and do not modify real checkout prices.

## Prices, photos and services (admin)

- **Prices** (`/admin/rates`): drag across the calendar to pick nights (Ctrl/⌘ adds, Shift extends, a weekday name picks every such day; on phones tap nights). Then *Set price*, *± %* or *± GEL* for one or more rate plans and room types at once, or *Reset to normal* to remove the special price. "Or type a longer date range" covers several months, optionally only some weekdays.
- **Photos** (`/admin/photos`): replace each room's photo and the large page photos; manage the Our Hotel gallery (upload several, description, caption, category, wide, hide, order, delete). Photos are shrunk in the browser and saved as WebP.
  - Storage: set `HOTEL_MEDIA_DISK`. On a single server `public` works (run `php artisan storage:link`). **On Laravel Cloud attach an Object Storage bucket and use its disk name**, otherwise uploads disappear on the next deploy.
- **Services** (`/admin/services`): *Included* services appear on the Our Hotel page. *Paid extras* (price per booking / night / guest / guest-night / room-night) appear on the booking page; the server prices them, they are saved on the booking and listed in the booking email.
- Deploy: run `php artisan migrate --force` (adds `site_photos`, `hotel_services`, and `extras` on bookings; the current gallery and amenities are copied in).
