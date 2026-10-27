<?php

namespace App\Http\Controllers;

use App\Support\RoomPricing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicPricingController extends Controller
{
    public function preview(Request $request): JsonResponse
    {
        $data = $request->validate([
            'checkin' => ['required','date_format:Y-m-d','after_or_equal:today'],
            'nights' => ['required','integer','min:1','max:'.config('hotel.max_nights')],
        ]);
        $plans = RoomPricing::plans(strict: true);
        $quotes = [];
        foreach (config('hotel.rooms') as $id => $room) {
            foreach ($plans as $p) {
                $priceOne = RoomPricing::totalForRoom($id, $p['code'], 1, $data['checkin'], (int) $data['nights']);
                $priceTwo = RoomPricing::totalForRoom($id, $p['code'], 2, $data['checkin'], (int) $data['nights']);
                $quotes[$id][$p['code']] = ['one' => $priceOne['total'], 'two' => $priceTwo['total']];
            }
        }
        return response()->json(['plans' => $plans, 'rooms' => $quotes, 'nights' => (int) $data['nights']]);
    }
}
