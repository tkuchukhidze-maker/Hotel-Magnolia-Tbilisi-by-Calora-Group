<?php

namespace App\Http\Controllers;

use App\Mail\BookingReceived;
use App\Models\Booking;
use App\Support\BookingQuote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    /**
     * Store a booking request: one or more rooms of one or more types.
     * The total is always recalculated on the server from config/hotel.php.
     */
    public function store(Request $request): JsonResponse
    {
        $rooms = config('hotel.rooms');

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:'.count($rooms)],
            'items.*.room' => ['required', 'distinct', Rule::in(array_keys($rooms))],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:10'],
            'rate' => ['required', Rule::in(['room', 'bf'])],
            'checkin' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'nights' => ['required', 'integer', 'min:1', 'max:'.config('hotel.max_nights')],
            'adults' => ['required', 'integer', 'min:1', 'max:'.config('hotel.max_adults')],
            'children' => ['required', 'integer', 'min:0', 'max:'.config('hotel.max_children')],
            'children_ages' => ['present', 'array'],
            'children_ages.*' => ['required', 'integer', 'min:0', 'max:'.config('hotel.child_max_age')],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'string', 'min:6', 'max:40', 'regex:/^[0-9+()\-\s]+$/'],
            'country' => ['nullable', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'arrival' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'privacy' => ['accepted'],
        ], [
            'items.required' => 'Please choose at least one room.',
            'first_name.required' => 'Please add your first name.',
            'last_name.required' => 'Please add your last name.',
            'email.required' => 'Please add your email.',
            'email.email' => 'Please check your email address.',
            'phone.required' => 'Please add a phone number.',
            'phone.regex' => 'Please check your phone number.',
            'phone.min' => 'Please check your phone number.',
            'privacy.accepted' => 'Please agree to the privacy policy to book.',
            'checkin.required' => 'Please choose your check-in date.',
            'checkin.after_or_equal' => 'Check-in can\'t be in the past.',
            'children_ages.*.required' => 'Please add the age of each child.',
        ]);

        // Room stock and children's ages must match what was asked for.
        $errors = [];
        foreach ($data['items'] as $i => $item) {
            $stock = $rooms[$item['room']]['stock'];
            if ($item['qty'] > $stock) {
                $errors["items.$i.qty"] = 'Only '.$stock.' '.$rooms[$item['room']]['name'].($stock === 1 ? ' is' : 's are').' available.';
            }
        }
        if (count($data['children_ages']) !== (int) $data['children']) {
            $errors['children_ages'] = 'Please add the age of each child.';
        }

        $guests = $data['adults'] + $data['children'];
        $quote = new BookingQuote($data['items'], $guests, $data['rate'], $data['nights']);

        if ($quote->capacity < $guests) {
            $errors['items'] = 'These rooms sleep '.$quote->capacity.', but you are '.$guests.' guests. Please add another room.';
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $checkin = Carbon::createFromFormat('Y-m-d', $data['checkin'])->startOfDay();

        $booking = Booking::create([
            'reference' => $this->newReference(),
            'items' => $quote->lines,
            'rooms_count' => $quote->rooms,
            'rate' => $data['rate'],
            'checkin' => $checkin,
            'checkout' => $checkin->copy()->addDays($data['nights']),
            'nights' => $data['nights'],
            'adults' => $data['adults'],
            'children' => $data['children'],
            'children_ages' => array_values($data['children_ages']),
            'nightly_price' => $quote->nightly,
            'total' => $quote->total,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'country' => $data['country'] ?? 'Not specified',
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'arrival' => $data['arrival'] ?? null,
            'notes' => $data['notes'] ?? null,
            'privacy_accepted_at' => now(),
            'status' => 'requested',
        ]);

        try {
            Mail::to(config('hotel.bookings_email'))->send(new BookingReceived($booking));
        } catch (\Throwable $e) {
            // The booking is saved either way; a mail failure must not lose it.
            Log::error('Booking email failed', ['reference' => $booking->reference, 'error' => $e->getMessage()]);
        }

        return response()->json([
            'status' => 'requested',
            'message' => 'Request received. Your stay is not confirmed until our team checks availability and contacts you.',
            'reference' => $booking->reference,
            'items' => $quote->lines,
            'nightly' => $quote->nightly,
            'total' => $booking->total,
            'checkin' => $booking->checkin->toDateString(),
            'checkout' => $booking->checkout->toDateString(),
        ], 201);
    }

    private function newReference(): string
    {
        do {
            $ref = 'MG-'.strtoupper(Str::random(6));
        } while (Booking::where('reference', $ref)->exists());

        return $ref;
    }
}
