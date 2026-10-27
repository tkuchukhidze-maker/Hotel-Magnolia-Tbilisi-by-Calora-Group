# SEO/AEO implementation notes (2026-10-27)

Research: [index.md](index.md). This file records what the keyword map turned into in code.

| Keyword family | Owner page | What the code does |
|---|---|---|
| F01 brand / F13 reviews | `/` | Reviews block renders only from `hotel.reviews` (real reviews); review count and "Read all reviews" only when `hotel.rating.count` / `rating.url` are filled. |
| F04 near Freedom Square | `/explore#walking-distances` | Distances from `hotel.nearby`, with approximate walking minutes (`HotelFacts::walkMinutes`). |
| F05 parking | `/explore#by-car` | `hotel.arrival.parking` lines appear as they are confirmed. |
| F11 airport | `/explore#from-the-airport` | Transfer line only when `hotel.arrival.transfer` is confirmed; otherwise WhatsApp prompt. |
| F10 policies | `/contact` | `HotelFacts::policies()` drops any value still in [brackets]. |
| F07 prices | all pages | Mobile "from" price is computed from the live room catalogue. |
| F14/F15 RU/KA | — | Footer "EN · ქარ · RU" text removed until real translated pages exist (then add hreflang). |
| F16 AI answers | `/llms.txt`, `/*.md`, `/sitemap.md` | Markdown versions generated from the same config, HTML marked canonical via `Link` header and `<link rel="alternate" type="text/markdown">`. robots.txt and sitemap.xml carry comment hints. |

## Fill in before launch
Run `php artisan hotel:content-check` — it lists every fact still in [brackets]. Until filled, those facts are hidden from guests, JSON-LD, `/llms.txt` and the Markdown pages.

## Still open (outside code)
- Google Business Profile ↔ site, Google Hotel Center free booking links.
- Search Console property + Keyword Planner volumes (see `keyword-decisions.md` § 5).
- Neighbourhood wording: site says "Old Town", OTAs say "Mtatsminda" — confirm (`hotel.contact.neighbourhood`).
