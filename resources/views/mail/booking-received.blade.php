New booking request — {{ $booking->reference }}

Room:      {{ $booking->roomName() }} ({{ $booking->rateName() }})
Dates:     {{ $booking->checkin->format('D j M Y') }} → {{ $booking->checkout->format('D j M Y') }} ({{ $booking->nights }} {{ \Illuminate\Support\Str::plural('night', $booking->nights) }})
Guests:    {{ $booking->adults }} adults, {{ $booking->children }} children
Total:     GEL {{ $booking->total }} (GEL {{ $booking->nightly_price }} per night, taxes included, pay at the hotel)

Guest:     {{ $booking->guestName() }}
Country:   {{ $booking->country }}
Email:     {{ $booking->email }}
Phone:     {{ $booking->phone }}
Arrival:   {{ $booking->arrival ?: '—' }}
Notes:     {{ $booking->notes ?: '—' }}

Please confirm with the guest and enter the booking in the PMS.
