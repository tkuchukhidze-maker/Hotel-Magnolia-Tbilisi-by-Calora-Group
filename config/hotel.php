<?php

/*
|--------------------------------------------------------------------------
| Hotel Magnolia — content & pricing
|--------------------------------------------------------------------------
| Everything the hotel may want to change without touching templates.
| Values in [brackets] are placeholders to fill in before launch.
| Prices are per room per night in GEL, taxes included:
|   p2 = [room only, with breakfast] for 2 guests (also used for 3 in the Triple)
|   p1 = [room only, with breakfast] for 1 guest
|   w2 / w1 = price before the autumn discount
*/

return [

    'name' => 'Hotel Magnolia Tbilisi by Calora Group',
    'short_name' => 'Hotel Magnolia Tbilisi',

    'contact' => [
        'phone_display' => env('HOTEL_PHONE_DISPLAY', '+995 533 33 44 22'),
        'phone_tel' => env('HOTEL_PHONE_TEL', '+995533334422'),
        'whatsapp' => env('HOTEL_WHATSAPP', '995533334422'),
        'email' => env('HOTEL_EMAIL', 'hello@example.com'),
        'email_display' => env('HOTEL_EMAIL_DISPLAY', '[EMAIL]'),
        'address' => env('HOTEL_ADDRESS', '5 Kirile Ukleba St'),
        'city' => 'Tbilisi',
        'country' => 'GE',
        'neighbourhood' => 'Old Town',
        // Google Business Profile (CID from Google Maps / Search).
        'google_maps' => env('HOTEL_GOOGLE_MAPS', 'https://maps.google.com/?cid=15414144500935940587'),
        'map_embed' => 'https://maps.google.com/maps?q='.rawurlencode('Hotel Magnolia, 5 Kirile Ukleba St, Tbilisi').'&output=embed',
        'instagram' => env('HOTEL_INSTAGRAM', ''),
        'facebook' => env('HOTEL_FACEBOOK', ''),
        'booking_com' => env('HOTEL_BOOKING_COM', ''),
    ],

    // Google Maps Platform. Without a key the site falls back to a plain
    // map embed and a normal address field. The key is public by design:
    // restrict it to your domain in Google Cloud (see README).
    'google' => [
        'maps_key' => env('GOOGLE_MAPS_API_KEY', ''),
        // Exact pin position. Leave empty to look the address up once in the browser.
        'lat' => env('HOTEL_LAT', ''),
        'lng' => env('HOTEL_LNG', ''),
        'directions' => 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode('Hotel Magnolia Tbilisi, 5 Kirile Ukleba St, Tbilisi, Georgia'),
    ],

    // Where new booking requests are emailed.
    'bookings_email' => env('HOTEL_BOOKINGS_EMAIL', env('HOTEL_EMAIL', 'hello@example.com')),

    'offer' => [
        'label' => 'Autumn at Magnolia',
        'discount' => 25,
        'ends' => '[OFFER END DATE]',
    ],

    'policies' => [
        'check_in' => 'From [14:00]',
        'check_out' => 'Until [12:00]',
        'cancellation' => 'Free until [deadline]',
        'payment' => 'At the hotel · [cards, cash]',
        'breakfast' => 'Buffet, [07:30–10:30]',
        'parking' => 'Free for guests',
        'children' => '[Policy]',
        'pets' => '[Policy]',
    ],

    'rating' => [
        'score' => 10,
        'label' => 'Exceptional',
        'source' => 'Booking.com',
        'count' => '[N]',
    ],

    // Short, factual answers. Shown on the Contact page, marked up as FAQPage
    // and listed in /llms.txt so search and AI answers can quote them.
    'faq' => [
        ['Where is Hotel Magnolia Tbilisi?', 'At 5 Kirile Ukleba St in the Old Town of Tbilisi, Georgia, on a quiet street below Mount Mtatsminda. Freedom Square is 950 m away and Freedom Square metro station 750 m.'],
        ['How much does a room cost?', 'Rooms start from GEL 106 per night for one guest and GEL 129 for two, taxes included (autumn rates, 25% off). Prices depend on the room and dates; the Rooms page shows the exact total before you book.'],
        ['What types of rooms are there?', 'Five: Single Room (15 m²), Double Room (18 m², king bed), Double or Twin (26 m², two single beds), Triple Room (26 m², three beds, balcony) and Deluxe Room (30 m², king bed, balcony). All have air conditioning, a private bathroom, a flat-screen TV and free Wi-Fi.'],
        ['Do you serve breakfast?', 'Yes, a buffet breakfast every morning. You can add it when you book, from GEL 11 per room per night.'],
        ['Is there parking?', 'Yes. Parking at the hotel is free for every guest, which is rare in Tbilisi\'s Old Town.'],
        ['Do I need to pay in advance?', 'No. There is no prepayment: you pay at the hotel, and cancellation is free within the cancellation period.'],
        ['Is booking directly cheaper than on Booking.com?', 'Booking on our website is never more expensive. If you find a lower price for the same room and dates, send it to us and we will match it.'],
        ['Is the front desk open 24 hours?', 'Yes. Someone is at the front desk day and night, and you can also reach us on WhatsApp at +995 533 33 44 22.'],
        ['Which room is best for a family of three?', 'The Triple Room: 26 m² with three single beds and a balcony. For larger groups we can give you rooms next to each other.'],
        ['How far is the airport?', 'Tbilisi International Airport is about 15 km from the hotel.'],
        ['What is special about the hotel?', 'Magnolia is a small hotel in a restored brick house, with classic furniture, a rooftop terrace with glass domes facing Mount Mtatsminda, and a 127-year-old magnolia tree in the courtyard.'],
    ],

    'max_guests' => 3,
    'max_nights' => 30,

    'rooms' => [
        'double' => [
            'name' => 'Double Room', 'img' => 'assets/img/room-double.webp',
            'alt' => 'Double Room with a skylight over a writing desk, a sofa and a king bed',
            'cap' => 2, 'size' => '18 m²', 'bed' => 'King bed', 'extra' => 'Skylight',
            'blurb' => 'Skylight above, king bed below. Simple, quiet and our best value for two.',
            'left' => 'Only 7 left',
            'p2' => [129, 142], 'w2' => 172, 'p1' => [106, 117], 'w1' => 142,
        ],
        'deluxe' => [
            'name' => 'Deluxe Room', 'img' => 'assets/img/room-deluxe.webp',
            'alt' => 'Deluxe Room with two skylights, a king bed and a red armchair',
            'cap' => 2, 'size' => '30 m²', 'bed' => 'King bed', 'extra' => 'Balcony',
            'blurb' => 'Our biggest room, with a balcony. Space to unpack properly and stay a while.',
            'left' => 'Only 4 left',
            'p2' => [152, 166], 'w2' => 202, 'p1' => [129, 142], 'w1' => 172,
        ],
        'twin' => [
            'name' => 'Double or Twin', 'img' => 'assets/img/room-twin.webp',
            'alt' => 'Double or Twin Room with dusty blue walls, two beds and a framed painting',
            'cap' => 2, 'size' => '26 m²', 'bed' => '2 single beds', 'extra' => 'Original art',
            'blurb' => 'Two beds, blue walls and a painting of Georgian haystacks to wake up to.',
            'left' => '',
            'p2' => [137, 150], 'w2' => 182, 'p1' => [114, 125], 'w1' => 152,
        ],
        'triple' => [
            'name' => 'Triple Room', 'img' => '',
            'alt' => '',
            'cap' => 3, 'size' => '26 m²', 'bed' => '3 single beds', 'extra' => 'Balcony',
            'blurb' => 'Three beds and a balcony, for families or friends travelling together.',
            'left' => '',
            'p2' => [190, 208], 'w2' => 253, 'p1' => [167, 183], 'w1' => 222,
        ],
        'single' => [
            'name' => 'Single Room', 'img' => '',
            'alt' => '',
            'cap' => 1, 'size' => '15 m²', 'bed' => 'Single bed', 'extra' => 'Quiet',
            'blurb' => 'A small, calm room of your own, close to everything.',
            'left' => 'Only 4 left',
            'p2' => [106, 117], 'w2' => 142, 'p1' => [106, 117], 'w1' => 142,
        ],
    ],

];
