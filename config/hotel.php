<?php

/*
|--------------------------------------------------------------------------
| Magnolia Hotel — content & pricing
|--------------------------------------------------------------------------
| Everything the hotel may want to change without touching templates.
| Values in [brackets] are placeholders to fill in before launch.
| Prices are per room per night in GEL, taxes included:
|   p2 = [room only, with breakfast] for 2 guests (also used for 3 in the Triple)
|   p1 = [room only, with breakfast] for 1 guest
|   w2 / w1 = price before the autumn discount
*/

return [

    'name' => 'Magnolia Hotel Tbilisi',

    'contact' => [
        'phone_display' => env('HOTEL_PHONE_DISPLAY', '+995 [PHONE]'),
        'phone_tel' => env('HOTEL_PHONE_TEL', '+995000000000'),
        'whatsapp' => env('HOTEL_WHATSAPP', '995000000000'),
        'email' => env('HOTEL_EMAIL', 'hello@example.com'),
        'email_display' => env('HOTEL_EMAIL_DISPLAY', '[EMAIL]'),
        'address' => env('HOTEL_ADDRESS', '[Street address]'),
        'instagram' => env('HOTEL_INSTAGRAM', 'https://instagram.com'),
        'facebook' => env('HOTEL_FACEBOOK', 'https://facebook.com'),
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
