# Magnolia Management · Pricing calendar

## Admin URLs

- Dashboard: /admin
- Flexible room pricing: /admin/rates

Log in using the existing authorized administrator session. This release adds a pricing calendar, not PMS live inventory.

## Price resolution

For every room night and selected rate plan:

1. Exact-day rate override (highest priority).
2. Month-wide rate override (YYYY-MM).
3. Room/plan default rate.
4. Existing configured room price (for the built-in room-only and breakfast plans).

The prices are in GEL per room per night and each scope stores separate rates for one guest and two guests. Single-room types use one-guest occupancy prices even in multi-room bookings. Changing a price affects **new** quotes only, never prior booking snapshots.

For example: Double Room / Room only. July monthly rate 150 GEL (2 guests), July 2 exact-day rate 220 GEL. Three nights July 1–4 cost 150 + 220 + 150 = 520 GEL before additional products. The backend calculates each night; the browser fetches the same pricing preview and cannot choose its own total.

## Admin operations

- Choose room type + plan + month to see a 7-column calendar with each day's one- and two-guest prices.
- Use **Edit day** or **Set a day or date range** to set or change nightly prices.
- Set an entire month's price, which remains effective for days without a more specific override.
- Remove a day or month override to reveal the underlying rate without overwriting it.
- Create a custom rate plan (e.g. FLEX or SUMMER). It starts as an inactive draft. Enter default prices for **all five room categories** before publishing. Published plans appear as rate choices on the guest booking page; unpublish to hide again.
- The existing admin basic room prices remain available. For the two built-in plans, any explicitly saved per-plan default takes priority over basic room rates.

### Safety

- Never label a draft as published.
- Do not use future guest-facing plans to promise refundability, breakfast rules, or payment terms before hotel policy is configured; currently they carry a code/name/breakfast flag only.
- The pricing calendar does **not** track availability. Do not show "last available" or instant confirmations without PMS sync.
- The booking request stays `requested` and the quoted total is authoritative only after server-side reprice.
- Keep Laravel Cloud DB migration and backup in the production release checklist.

## Deployment checklist

1. Review the GitHub pull request and CI tests.
2. Back up production database.
3. Deploy the reviewed main branch.
4. Run `php artisan migrate --force` from Laravel Cloud's approved command/deploy hook. This adds rate_plan, plan defaults, month and day tables.
5. Visit `/admin/rates` and compare old prices against the default rates.
6. Set a monthly price for one room, select a date, verify public `/rooms` and booking request total, remove override and recheck fallback.
7. Test an inactive custom plan (not publicly shown), publish only when every room is priced.
8. Test on mobile and desktop with a 3-night stay crossing a month boundary.
9. Monitor Laravel application logs and avoid deploying extra DB migrations until production storage health is confirmed.
