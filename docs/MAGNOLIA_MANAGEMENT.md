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
