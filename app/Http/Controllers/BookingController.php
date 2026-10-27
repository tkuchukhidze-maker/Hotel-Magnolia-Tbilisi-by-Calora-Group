<?php

namespace App\Http\Controllers;

use App\Mail\BookingReceived;
use App\Models\Booking;
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
     * Store a booking request. The total is always recalculated on the
     * server from config/hotel.php — never trusted from the browser.
     */
    public function store(Request $request): JsonResponse
    {
        $rooms = config('hotel.rooms');

        $data = $request->validate([
            'room' => ['required', Rule::in(array_keys($rooms))],
            'rate' => ['required', Rule::in(['room', 'bf'])],
            'checkin' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'nights' => ['required', 'integer', 'min:1', 'max:'.config('hotel.max_nights')],
            'adults' => ['required', 'integer', 'min:1', 'max:'.config('hotel.max_guests')],
            'children' => ['required', 'integer', 'min:0', 'max:2'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'string', 'min:6', 'max:40'],
            'arrival' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $room = $rooms[$data['room']];
        $guests = $data['adults'] + $data['children'];

        if ($guests > $room['cap']) {
            throw ValidationException::withMessages([
                'room' => 'This room is too small for '.$guests.' guests.',
            ]);
        }

        $prices = $guests === 1 ? $room['p1'] : $room['p2'];
        $nightly = $data['rate'] === 'bf' ? $prices[1] : $prices[0];
        $checkin = Carbon::createFromFormat('Y-m-d', $data['checkin'])->startOfDay();

        $booking = Booking::create([
            'reference' => $this->newReference(),
            'room' => $data['room'],
            'rate' => $data['rate'],
            'checkin' => $checkin,
            'checkout' => $checkin->copy()->addDays($data['nights']),
            'nights' => $data['nights'],
            'adults' => $data['adults'],
            'children' => $data['children'],
            'nightly_price' => $nightly,
            'total' => $nightly * $data['nights'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'arrival' => $data['arrival'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'requested',
        ]);

        try {
            Mail::to(config('hotel.bookings_email'))->send(new BookingReceived($booking));
        } catch (\Throwable $e) {
            // The booking is saved either way; a mail failure must not lose it.
            Log::error('Booking email failed', ['reference' => $booking->reference, 'error' => $e->getMessage()]);
        }

        return response()->json([
            'reference' => $booking->reference,
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
