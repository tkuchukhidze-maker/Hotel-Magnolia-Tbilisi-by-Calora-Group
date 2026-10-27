Hotel Magnolia Tbilisi by Calora Group

YOUR ROOM IS BOOKED

Congratulations, {{ $booking->first_name }}! We're expecting you.

CHECK-IN:  {!! html_entity_decode($v['CHECKIN_DAY']) !!}, {!! html_entity_decode($v['CHECKIN_SUB']) !!}
CHECK-OUT: {!! html_entity_decode($v['CHECKOUT_DAY']) !!}, {!! html_entity_decode($v['CHECKOUT_SUB']) !!}
{!! html_entity_decode($v['NIGHTS']) !!} · {!! html_entity_decode($v['GUESTS'].$v['CHILD_AGES']) !!}

Booking reference: {{ $booking->reference }}
Rooms: {{ $booking->roomsSummary() }}
@foreach ($booking->extras ?? [] as $x)
Extra: {{ $x['name'] }} (GEL {{ $x['amount'] }})
@endforeach
Rate: {{ $booking->rateName() }}, taxes included
Total: GEL {{ number_format($booking->total) }}. Nothing to pay now, you pay at the hotel.

WHERE TO FIND US
{!! html_entity_decode($v['ADDRESS']) !!}
Directions: {!! html_entity_decode($v['DIRECTIONS_URL']) !!}
Front desk, 24 hours: {{ config('hotel.contact.phone_display') }} · WhatsApp: {!! html_entity_decode($v['WHATSAPP_URL']) !!}

Arriving late or need to change something? Just reply to this email.

See you in Tbilisi,
The Magnolia team
