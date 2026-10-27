Hotel Magnolia Tbilisi by Calora Group

{{ $c['label'] }}

{{ $c['title'] }}

Dear {{ $booking->first_name }},

{{ $c['lead'] }}
@if ($c['note'])

{{ $c['note'] }}
@endif

CANCELLED BOOKING
Reference: {{ $booking->reference }}
Dates: {!! html_entity_decode($v['CHECKIN_DAY']) !!} - {!! html_entity_decode($v['CHECKOUT_DAY']) !!} ({!! html_entity_decode($v['NIGHTS']) !!})
Rooms: {{ $booking->roomsSummary() }}

Nothing was charged. You don't need to do anything.

Book new dates: {{ $c['book_url'] }}

Questions, or was this a mistake? Reply to this email or call {{ config('hotel.contact.phone_display') }}.

Warm regards,
The Magnolia team
