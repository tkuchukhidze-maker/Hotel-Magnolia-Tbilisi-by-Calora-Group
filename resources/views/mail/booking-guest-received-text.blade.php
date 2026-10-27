Hotel Magnolia Tbilisi by Calora Group

BOOKING REQUEST RECEIVED

Hello {{ $booking->first_name }},

Thank you for choosing Hotel Magnolia Tbilisi. We've received your booking request.

IMPORTANT: YOUR STAY IS NOT YET CONFIRMED.
Our hotel team will check availability and contact you to confirm the reservation and final terms. No payment has been taken.

Reference: {{ $booking->reference }}
Check-in: {{ $booking->checkin->format('d M Y') }}
Check-out: {{ $booking->checkout->format('d M Y') }}
Nights: {{ $booking->nights }}
Guests: {{ $booking->adults }} {{ \Illuminate\Support\Str::plural('adult', $booking->adults) }}@if($booking->children), {{ $booking->children }} {{ \Illuminate\Support\Str::plural('child', $booking->children) }}@endif

Rooms: {{ $booking->roomsSummary() }}
Rate plan: {{ $booking->rateName() }}
Quoted stay total: GEL {{ number_format($booking->total) }}

The hotel will confirm availability, applicable cancellation rules, any additional charges and the final price. This message is not a confirmation or payment receipt.

Questions or changes? Reply to this email or contact {{ config('hotel.contact.email') }}.

We look forward to welcoming you!
The Hotel Magnolia Tbilisi Team
{{ config('hotel.contact.address') }}, Tbilisi, Georgia
