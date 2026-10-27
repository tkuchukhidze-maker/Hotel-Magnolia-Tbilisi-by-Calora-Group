New booking request — {{ $booking->reference }}

Dates:     {{ $booking->checkin->format('D j M Y') }} → {{ $booking->checkout->format('D j M Y') }} ({{ $booking->nights }} {{ \Illuminate\Support\Str::plural('night', $booking->nights) }})
Guests:    {{ $booking->adults }} {{ \Illuminate\Support\Str::plural('adult', $booking->adults) }}@if ($booking->children), {{ $booking->children }} {{ \Illuminate\Support\Str::plural('child', $booking->children) }} (ages {{ implode(', ', $booking->children_ages ?? []) }})@endif

Rate:      {{ $booking->rateName() }}

Rooms ({{ $booking->rooms_count }}):
@foreach ($booking->items ?? [] as $line)
  {{ $line['qty'] }} × {{ $line['name'] }} — GEL {{ $line['stay_total'] ?? ($line['subtotal'] * $booking->nights) }} for the full stay@if($line['nightly_varies'] ?? false) (prices vary by night)@endif
@endforeach
@if (! empty($booking->extras))

Extras:
@foreach ($booking->extras as $extra)
  {{ $extra['name'] }} — GEL {{ $extra['amount'] }} (GEL {{ $extra['price'] }} {{ \App\Support\Services::UNITS[$extra['unit']] ?? '' }})
@endforeach
@endif

Total:     GEL {{ $booking->total }} (average GEL {{ $booking->nightly_price }} per night; nightly rates may vary, taxes included, pay at the hotel)

Guest:     {{ $booking->guestName() }}
Country:   {{ $booking->country }}
Address:   {{ collect([$booking->address, $booking->postal_code, $booking->city])->filter()->unique()->implode(', ') ?: '—' }}
Email:     {{ $booking->email }}
Phone:     {{ $booking->phone }}
Arrival:   {{ $booking->arrival ?: '—' }}
Notes:     {{ $booking->notes ?: '—' }}

Please confirm with the guest and enter the booking in the PMS.
