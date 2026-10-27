<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Nightly prices: day override > month override > plan default > legacy room_rates/config.
 * Live date-specific availability is a separate PMS concern.
 */
class RoomPricing
{
    public static function plans(bool $strict = false): array
    {
        $base = [
            ['code' => 'room', 'name' => 'Room only', 'includes_breakfast' => false],
            ['code' => 'bf', 'name' => 'With buffet breakfast', 'includes_breakfast' => true],
        ];
        try {
            if (!Schema::hasTable('room_rate_plans')) return $base;
            foreach (DB::table('room_rate_plans')->where('is_active', true)->orderBy('id')->get() as $p) {
                if (!in_array($p->code, ['room','bf'], true)) {
                    $base[] = ['code' => $p->code, 'name' => $p->name, 'includes_breakfast' => (bool) $p->includes_breakfast];
                }
            }
            return $base;
        } catch (\Throwable $e) {
            if ($strict) throw $e;
            report($e);
            return $base;
        }
    }

    public static function plansAsNames(): array
    {
        return collect(self::plans())->pluck('name', 'code')->all();
    }

    public static function totalForRoom(string $roomType, string $planCode, int $occupancy, string $checkin, int $nights): array
    {
        if ($nights < 1 || $nights > (int) config('hotel.max_nights', 30)) {
            throw ValidationException::withMessages(['nights' => 'Invalid length of stay.']);
        }
        $plans = collect(self::plans(strict: true))->keyBy('code');
        if (!$plans->has($planCode)) {
            throw ValidationException::withMessages(['rate' => 'Rate plan is not available.']);
        }
        $rooms = RoomCatalog::all(strict: true);
        if (!isset($rooms[$roomType])) {
            throw ValidationException::withMessages(['items' => 'Unknown room type.']);
        }
        $room = $rooms[$roomType];
        $start = CarbonImmutable::createFromFormat('!Y-m-d', $checkin);
        if (!$start || $start->format('Y-m-d') !== $checkin) {
            throw ValidationException::withMessages(['checkin' => 'Invalid check-in date.']);
        }
        $dates = [];
        for ($i = 0; $i < $nights; $i++) {
            $dates[] = $start->addDays($i)->format('Y-m-d');
        }
        $lastDate = end($dates);
        $defaults = [
            'room' => [$room['p1'][0], $room['p2'][0]],
            'bf' => [$room['p1'][1], $room['p2'][1]],
        ];
        $prices = $defaults[$planCode] ?? null;

        if (!Schema::hasTable('room_plan_prices') || !Schema::hasTable('room_day_prices') || !Schema::hasTable('room_month_prices')) {
            if ($prices === null) throw ValidationException::withMessages(['rate' => 'Rate plan has no price.']);
            return self::fromDefault($dates, $prices, $occupancy);
        }

        $planDefault = DB::table('room_plan_prices')->where('room_type', $roomType)
            ->where('plan_code', $planCode)->first();
        if ($planDefault) $prices = [(int) $planDefault->one_guest, (int) $planDefault->two_guests];
        if ($prices === null) throw ValidationException::withMessages(['rate' => 'Rate plan is not priced for this room.']);

        $months = array_values(array_unique(array_map(fn ($d) => substr($d, 0, 7), $dates)));
        $monthly = DB::table('room_month_prices')->where('room_type', $roomType)
            ->where('plan_code', $planCode)->whereIn('month', $months)->get()->keyBy('month');
        $daily = DB::table('room_day_prices')->where('room_type', $roomType)
            ->where('plan_code', $planCode)->whereBetween('stay_date', [$checkin, $lastDate])
            ->get()->keyBy('stay_date');
        $nightly = [];
        foreach ($dates as $date) {
            $row = $daily->get($date) ?? $monthly->get(substr($date, 0, 7));
            $nightly[] = ['date' => $date, 'price' => (int) ($row ? ($occupancy === 1 ? $row->one_guest : $row->two_guests) : $prices[$occupancy === 1 ? 0 : 1])];
        }
        return ['total' => array_sum(array_column($nightly, 'price')), 'nights' => $nightly];
    }

    private static function fromDefault(array $dates, array $prices, int $occupancy): array
    {
        $amount = (int) $prices[$occupancy === 1 ? 0 : 1];
        return [
            'total' => $amount * count($dates),
            'nights' => array_map(fn ($d) => ['date' => $d, 'price' => $amount], $dates),
        ];
    }
}
