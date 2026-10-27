New booking request — {{ $booking->reference }}

Dates:     {{ $booking->checkin->format('D j M Y') }} → {{ $booking->checkout->format('D j M Y') }} ({{ $booking->nights }} {{ \Illuminate\Support\Str::plural('night', $booking->nights) }})
Guests:    {{ $booking->adults }} {{ \Illuminate\Support\Str::plural('adult', $booking->adults) }}@if ($booking->children), {{ $booking->children }} {{ \Illuminate\Support\Str::plural('child', $booking->children) }}@endif

Rate:      {{ $booking->rateName() }}

Rooms ({{ $booking->rooms_count }}):
@foreach ($booking->items ?? [] as $line)
  {{ $line['qty'] }} × {{ $line['name'] }} — GEL {{ $line['nightly'] }} per room per night = GEL {{ $line['subtotal'] }} per night@if (($line['extra_beds'] ?? 0) > 0) · {{ $line['extra_beds'] }} EXTRA BED(S) REQUESTED (supplement not included; confirm before booking)@endif
@endforeach

Base room quote: GEL {{ $booking->total }} (GEL {{ $booking->nightly_price }} per night, listed taxes included). If extra beds are requested, their supplement is NOT included and must be approved. Confirm final terms with the guest.

Guest:     {{ $booking->guestName() }}
Country:   {{ $booking->country }}
Address:   {{ collect([$booking->address, $booking->postal_code, $booking->city])->filter()->unique()->implode(', ') ?: '—' }}
Email:     {{ $booking->email }}
Phone:     {{ $booking->phone }}
Arrival:   {{ $booking->arrival ?: '—' }}
Notes:     {{ $booking->notes ?: '—' }}

Please confirm with the guest and enter the booking in the PMS.
