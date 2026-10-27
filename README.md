# Magnolia Hotel Tbilisi — website

Website for **Magnolia Hotel Tbilisi** (Calora Group): a small hotel in an old brick house in Old Tbilisi, with a 127-year-old magnolia in the courtyard.

Brand idea: **A little different. A lot more personal.**

## Pages

| File | Page |
| --- | --- |
| `index.html` | Home — hero, booking bar, trust strip, the 127-year-old magnolia, room preview, book direct, reviews |
| `rooms.html` | Rooms & booking — guests/nights stepper, room list, inline booking panel (rate → details → confirmation) |
| `our-hotel.html` | Our Hotel & Gallery — story, courtyard, filterable gallery, little extras |
| `explore.html` | Explore Tbilisi — nearby places with distances, getting here |
| `contact.html` | Contact & FAQ — WhatsApp/phone/email, good to know, FAQ |

## Stack

Plain HTML, CSS and a little vanilla JavaScript. No build step, no dependencies — open `index.html` or deploy the folder to any static host (GitHub Pages, Netlify, Cloudflare Pages).

```
assets/
  css/styles.css   design tokens, components, mobile rules
  js/main.js       nav drawer, header, mobile booking bar, gallery filter
  js/rooms.js      room data and booking flow
  img/             photos (WebP) and favicon
```

### Brand tokens

| Token | Hex | Use |
| --- | --- | --- |
| Terracotta | `#A94729` | primary buttons, eyebrows, accents |
| Warm Ivory | `#F5EBDD` | main background |
| Soft Gold | `#B99559` | details on dark backgrounds only |
| Dusty Blue | `#7793A5` | map, decorative blocks |
| Dark Cocoa | `#42271F` | text, dark sections, footer |

Fonts: Cormorant Garamond (headings) + Manrope (text), from Google Fonts.

## Mobile

Mobile-first rules live at the end of `styles.css` (`max-width: 767px`). On phones the menu becomes a full-screen drawer and a fixed **Book Now** bar appears at the bottom after scrolling.

## Before launch — to fill in

Search the HTML for `[` to find every placeholder.

- Address, phone, WhatsApp number (`wa.me/995000000000`), email
- Check-in / check-out times, free-cancellation deadline, payment methods, breakfast hours
- Children and pets policy; airport transfer (yes/no, price)
- Autumn offer end date; price-match terms
- Real guest reviews (word for word, with source) and real review count
- Photos for the Triple and Single rooms
- Google Map embed (Explore and Contact pages)
- Building history line on Our Hotel

## Booking engine

`assets/js/rooms.js` is the front-end flow only. Prices in `ROOMS` come from the hotel's current rates (−25% autumn rate, 2 guests and single-occupancy). Before going live, connect the confirm step (`// TODO` in the submit handler) to the hotel's booking engine / PMS, or replace the flow with the engine's widget.
