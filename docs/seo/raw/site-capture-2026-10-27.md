# Site capture — magnoliatbilisi.com (2026-10-27, ~13:30 UTC)

Method: WebFetch (HTML → Markdown, model-summarised extraction). Shell access to the site was refused by the workspace proxy, so raw HTML was NOT saved. Head-level elements (JSON-LD, html lang, hreflang) were reported absent by the extractor, which also warned it may not see the raw head. Treat those three as "unverified absent".

## Inventory (sitemap.xml, 5 URLs, all lastmod 2026-10-27, no hreflang)
| URL | title | H1 | meta description (paraphrased) |
|---|---|---|---|
| / | Hotel Magnolia Tbilisi by Calora Group \| Boutique Hotel, Old Tbilisi | Make yourself at home in Tbilisi. | small boutique hotel, 5 Kirile Ukleba St, ~950 m from Freedom Square, from GEL 106, free parking, rooftop, 127-year-old magnolia, direct booking, pay at hotel |
| /rooms | Rooms & Prices from GEL 106 \| … | Find your kind of comfort. | five room types 15–30 m², from GEL 106 incl. taxes, free cancellation, pay at hotel |
| /our-hotel | About the Hotel & Photo Gallery \| … | A little different. A lot more personal. | restored brick house, rooftop terrace with glass domes facing Mtatsminda, 127-year-old magnolia |
| /explore | Location & What's Nearby in Old Tbilisi \| … | A city worth getting lost in. | Kirile Ukleba St, nearby sights, metro, airport, free parking |
| /contact | Contact, Check-in & FAQ \| … | Rather talk to a person? | phone/WhatsApp 24h, address, check-in, cancellation, parking, breakfast, FAQ |

robots.txt: `User-agent: * / Allow: / / Disallow: /bookings / Sitemap: …/sitemap.xml`
llms.txt: present, Markdown summary of hotel facts (location, rooms GEL 106–190, booking terms, contact, "10/10 Booking.com").
/ka: client error (no Georgian URL). Footer shows "EN · ქარ · RU" as plain text, no links.

## Facts stated on the site
- Address 5 Kirile Ukleba St, Old Town, Tbilisi. Freedom Square ~950 m; Freedom Square metro ~750 m; Rustaveli metro 1.2 km; airport 15 km.
- Rooms (per night, taxes incl., autumn −25% applied; breakfast price in brackets):
  Single 15 m² GEL 106 (117) · Double 18 m² skylight GEL 129 (142) · Double/Twin 26 m² GEL 137 (150) · Deluxe 30 m² balcony GEL 152 (166) · Triple 26 m² balcony GEL 190 (208). Triple & Single: "photos coming soon".
- Free parking (page calls it rare in the old town); rooftop terrace with glass domes facing Mtatsminda / TV tower; courtyard magnolia 127 years old; "Since 1899"; 24h front desk; free Wi-Fi; A/C; buffet breakfast from GEL 11/room.
- Booking: direct, pay at hotel (card or cash), no prepayment, price match vs other sites, groups >12 adults via WhatsApp.
- Explore page nearby list: Mtatsminda Pantheon 550 m, 9 April Park 700 m, Freedom Square 950 m, Bridge of Peace 1.6 km, Narikala 2.2 km, Botanical Garden 2.5 km; Fox Bay 500 m, Chacha Time 750 m, About Sololaki 900 m.
- /contact FAQ (11 Qs): where, price, room types, breakfast, parking, pay in advance, direct vs Booking.com, 24h desk, family of three, airport distance, what's special.

## Placeholders / inconsistencies observed
- Offer end date "[OFFER END DATE]"; cancellation deadline "[deadline]" (home, rooms, contact).
- Check-in "[14:00]", check-out "[12:00]", breakfast hours "[07:30–10:30]", payment "[cards, cash]", children "[Policy]", pets "[Policy]", email "[EMAIL]".
- Reviews section: three placeholder testimonials; review count "[N] reviews"; claim "Rated 10, Exceptional on Booking.com".
- /our-hotel history paragraph is a placeholder; airport transfer "[On request — confirm]".
- /our-hotel shows "from GEL 142" next to "GEL 106 −25%" (mixed pre/post-discount display).
- No visible JSON-LD (unverified, see method note).
