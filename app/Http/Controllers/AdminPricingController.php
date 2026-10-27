<?php

namespace App\Http\Controllers;

use App\Support\RoomCatalog;
use App\Support\RoomPricing;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminPricingController extends Controller
{
    private function guard(Request $request): void
    {
        abort_unless($request->session()->get('magnolia_admin') === true, 403);
    }

    public function index(Request $request)
    {
        $this->guard($request);
        // A new deployment can precede its database migration; show an
        // actionable setup message rather than a generic server error.
        try {
            foreach (['room_rate_plans', 'room_plan_prices', 'room_month_prices', 'room_day_prices'] as $table) {
                if (!Schema::hasTable($table)) {
                    return response('Room pricing calendar needs the database migration. Run php artisan migrate --force in Laravel Cloud, then reload.', 503);
                }
            }
        } catch (\Throwable $e) {
            report($e);
            return response('Pricing database is temporarily unavailable. Please contact the administrator.', 503);
        }

        $month = $request->query('month', now()->format('Y-m'));
        if (!is_string($month) || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = now()->format('Y-m');
        }
        $room = $request->query('room', 'double');
        $rooms = RoomCatalog::all(strict: true);
        if (!array_key_exists($room, $rooms)) $room = array_key_first($rooms);
        $plans = RoomPricing::plans(strict: true);
        $savedPlans = DB::table('room_rate_plans')->orderBy('id')->get();
        $plan = $request->query('plan', 'room');
        if (!in_array($plan, array_merge(['room','bf'], $savedPlans->pluck('code')->all()), true)) $plan = 'room';

        $start = CarbonImmutable::createFromFormat('!Y-m-d', $month.'-01');
        $rows = [];
        for ($day = 1; $day <= $start->daysInMonth; $day++) {
            $date = $start->addDays($day - 1);
            $rows[] = ['date' => $date->toDateString(), 'day' => $day, 'weekday' => $date->format('D'), 'dow' => $date->dayOfWeekIso];
        }
        $preview = RoomPricing::resolveDates($room, $plan, array_column($rows, 'date'));
        $base = DB::table('room_plan_prices')->get()->keyBy(fn($r) => $r->room_type.'|'.$r->plan_code);
        $monthly = DB::table('room_month_prices')->where('month', $month)->get()->keyBy(fn($r) => $r->room_type.'|'.$r->plan_code);
        $allPlans = array_merge(
            [['code' => 'room', 'name' => 'Room only', 'is_active' => true], ['code' => 'bf', 'name' => 'With breakfast', 'is_active' => true]],
            $savedPlans->map(fn ($p) => ['code' => $p->code, 'name' => $p->name, 'is_active' => (bool) $p->is_active])->all(),
        );
        $prevMonth = $start->subMonth()->format('Y-m');
        $nextMonth = $start->addMonth()->format('Y-m');
        return view('admin.pricing', compact('month','room','rooms','plan','plans','savedPlans','rows','preview','base','monthly','allPlans','prevMonth','nextMonth','start'));
    }

    /**
     * Bulk change from the calendar: the dates picked with the mouse (or a typed range
     * filtered by weekday), for one or more rooms and rate plans at once.
     * Modes: set a price, change by %, change by GEL, or reset to the underlying price.
     */
    public function bulk(Request $request)
    {
        $this->guard($request);
        $roomKeys = array_keys(config('hotel.rooms'));
        $data = $request->validate([
            'dates' => ['nullable', 'array', 'max:400'],
            'dates.*' => ['date_format:Y-m-d'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'weekdays' => ['nullable', 'array'],
            'weekdays.*' => ['integer', 'between:1,7'],
            'rooms' => ['required', 'array', 'min:1'],
            'rooms.*' => [Rule::in($roomKeys)],
            'plans' => ['required', 'array', 'min:1'],
            'plans.*' => ['string', 'max:8'],
            'mode' => ['required', Rule::in(['set', 'percent', 'amount', 'reset'])],
            'one_guest' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'two_guests' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'value' => ['nullable', 'numeric', 'between:-100000,100000'],
        ], [
            'rooms.required' => 'Choose at least one room type.',
            'plans.required' => 'Choose at least one rate plan.',
        ]);

        // Which nights: the picked dates, or a from–to range (optionally only some weekdays).
        $dates = array_values(array_unique($data['dates'] ?? []));
        if (! $dates && ! empty($data['from']) && ! empty($data['to'])) {
            $first = CarbonImmutable::parse($data['from']);
            $last = CarbonImmutable::parse($data['to']);
            if ($first->diffInDays($last) > 400) {
                throw ValidationException::withMessages(['to' => 'Please choose a range of at most 400 days.']);
            }
            $days = array_map('intval', $data['weekdays'] ?? []);
            for ($d = $first; $d->lessThanOrEqualTo($last); $d = $d->addDay()) {
                if (! $days || in_array($d->dayOfWeekIso, $days, true)) {
                    $dates[] = $d->toDateString();
                }
            }
        }
        if (! $dates) {
            throw ValidationException::withMessages(['dates' => 'Select at least one night on the calendar.']);
        }
        sort($dates);

        foreach ($data['plans'] as $code) {
            $this->checkPlan($code);
        }
        $mode = $data['mode'];
        if ($mode === 'set' && empty($data['one_guest']) && empty($data['two_guests'])) {
            throw ValidationException::withMessages(['two_guests' => 'Enter a new price for 1 guest, 2 guests, or both.']);
        }
        if (in_array($mode, ['percent', 'amount'], true) && (! isset($data['value']) || (float) $data['value'] == 0.0)) {
            throw ValidationException::withMessages(['value' => 'Enter how much to change the price by.']);
        }
        if ($mode === 'percent' && (float) $data['value'] <= -100) {
            throw ValidationException::withMessages(['value' => 'A price can\'t go down by 100% or more.']);
        }

        $changed = 0;
        DB::transaction(function () use ($data, $dates, $mode, &$changed) {
            foreach ($data['rooms'] as $room) {
                foreach ($data['plans'] as $plan) {
                    if ($mode === 'reset') {
                        $changed += DB::table('room_day_prices')->where('room_type', $room)->where('plan_code', $plan)
                            ->whereIn('stay_date', $dates)->delete();
                        continue;
                    }
                    $current = RoomPricing::resolveDates($room, $plan, $dates);
                    foreach ($dates as $date) {
                        $cur = $current[$date];
                        [$one, $two] = match ($mode) {
                            'set' => [(int) ($data['one_guest'] ?? $cur['one']), (int) ($data['two_guests'] ?? $cur['two'])],
                            'percent' => [(int) round($cur['one'] * (1 + $data['value'] / 100)), (int) round($cur['two'] * (1 + $data['value'] / 100))],
                            'amount' => [(int) round($cur['one'] + $data['value']), (int) round($cur['two'] + $data['value'])],
                        };
                        DB::table('room_day_prices')->updateOrInsert(
                            ['room_type' => $room, 'plan_code' => $plan, 'stay_date' => $date],
                            ['one_guest' => max(1, $one), 'two_guests' => max(1, $two)],
                        );
                        $changed++;
                    }
                }
            }
        });

        $nights = count($dates);
        $msg = $mode === 'reset'
            ? 'Special prices removed for '.$nights.' '.($nights === 1 ? 'night' : 'nights').'. The month or default price applies again.'
            : 'Prices updated: '.$nights.' '.($nights === 1 ? 'night' : 'nights').' × '.count($data['rooms']).' room '.(count($data['rooms']) === 1 ? 'type' : 'types').' × '.count($data['plans']).' '.(count($data['plans']) === 1 ? 'plan' : 'plans').'.';

        if ($request->wantsJson()) {
            return response()->json(['message' => $msg, 'changed' => $changed]);
        }

        return back()->with('success', $msg);
    }

    public function createPlan(Request $request)
    {
        $this->guard($request);
        $request->merge(['code' => strtolower(trim((string) $request->input('code', '')))]);
        $data = $request->validate([
            'code' => ['required','alpha_dash:ascii','max:8', Rule::notIn(['room','bf']), 'unique:room_rate_plans,code'],
            'name' => ['required','string','max:90'],
            'includes_breakfast' => ['sometimes','boolean'],
        ]);
        DB::table('room_rate_plans')->insert([
            'code' => strtolower($data['code']),
            'name' => $data['name'],
            'includes_breakfast' => (bool) ($data['includes_breakfast'] ?? false),
            'is_active' => false,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return back()->with('success', 'Rate plan created as draft. Set room prices before publishing.');
    }

    public function saveBase(Request $request)
    {
        $this->guard($request);
        $data = $request->validate([
            'room_type' => ['required', Rule::in(array_keys(config('hotel.rooms')))],
            'plan_code' => ['required','string','max:8'],
            'one_guest' => ['required','integer','min:1','max:100000'],
            'two_guests' => ['required','integer','min:1','max:100000'],
        ]);
        $this->checkPlan($data['plan_code']);
        DB::table('room_plan_prices')->updateOrInsert(
            ['room_type' => $data['room_type'], 'plan_code' => $data['plan_code']],
            ['one_guest' => $data['one_guest'], 'two_guests' => $data['two_guests']]
        );
        return back()->with('success', 'Default nightly prices saved.');
    }

    public function saveSchedule(Request $request)
    {
        $this->guard($request);
        $data = $request->validate([
            'room_type' => ['required', Rule::in(array_keys(config('hotel.rooms')))],
            'plan_code' => ['required','string','max:8'],
            'scope' => ['required', Rule::in(['month','range'])],
            'month' => ['required_if:scope,month','nullable','date_format:Y-m'],
            'from' => ['required_if:scope,range','nullable','date_format:Y-m-d'],
            'to' => ['required_if:scope,range','nullable','date_format:Y-m-d','after_or_equal:from'],
            'one_guest' => ['required','integer','min:1','max:100000'],
            'two_guests' => ['required','integer','min:1','max:100000'],
        ]);
        $this->checkPlan($data['plan_code']);
        $key = ['room_type' => $data['room_type'], 'plan_code' => $data['plan_code']];
        $values = ['one_guest' => $data['one_guest'], 'two_guests' => $data['two_guests']];
        if ($data['scope'] === 'month') {
            DB::table('room_month_prices')->updateOrInsert($key + ['month' => $data['month']], $values);
            return back()->with('success', 'Monthly prices saved for '.$data['month'].'.');
        }
        $first = CarbonImmutable::parse($data['from'])->startOfDay();
        $last = CarbonImmutable::parse($data['to'])->startOfDay();
        if ($first->diffInDays($last) > 366) {
            throw ValidationException::withMessages(['to' => 'Please choose a range of at most 367 days.']);
        }
        DB::transaction(function () use ($first, $last, $key, $values) {
            for ($date = $first; $date->lessThanOrEqualTo($last); $date = $date->addDay()) {
                DB::table('room_day_prices')->updateOrInsert($key + ['stay_date' => $date->toDateString()], $values);
            }
        });
        return back()->with('success', 'Date-range pricing saved.');
    }

    public function removeOverride(Request $request)
    {
        $this->guard($request);
        $data = $request->validate([
            'room_type' => ['required', Rule::in(array_keys(config('hotel.rooms')))],
            'plan_code' => ['required','string','max:8'],
            'scope' => ['required', Rule::in(['month','day'])],
            'date' => ['required','string','max:10'],
        ]);
        $this->checkPlan($data['plan_code']);
        if ($data['scope'] === 'day') {
            validator(['date' => $data['date']], ['date' => ['date_format:Y-m-d']])->validate();
            DB::table('room_day_prices')->where('room_type', $data['room_type'])
                ->where('plan_code', $data['plan_code'])->where('stay_date', $data['date'])->delete();
        } else {
            validator(['date' => $data['date']], ['date' => ['date_format:Y-m']])->validate();
            DB::table('room_month_prices')->where('room_type', $data['room_type'])
                ->where('plan_code', $data['plan_code'])->where('month', $data['date'])->delete();
        }
        return back()->with('success', 'Price override removed. The next applicable price will be used.');
    }

    public function publishPlan(Request $request, string $code)
    {
        $this->guard($request);
        $p = DB::table('room_rate_plans')->where('code', $code)->first();
        abort_unless($p, 404);
        $active = $request->boolean('is_active');
        if ($active) {
            $count = DB::table('room_plan_prices')->where('plan_code', $code)
                ->whereIn('room_type', array_keys(config('hotel.rooms')))->count();
            if ($count < count(config('hotel.rooms'))) {
                throw ValidationException::withMessages(['plan' => 'Set default prices for every room type before publishing.']);
            }
        }
        DB::table('room_rate_plans')->where('code', $code)->update(['is_active' => $active, 'updated_at' => now()]);
        return back()->with('success', $active ? 'Rate plan is now visible in booking.' : 'Rate plan unpublished.');
    }

    private function checkPlan(string $code): void
    {
        abort_unless(in_array($code, ['room','bf'], true) || DB::table('room_rate_plans')->where('code', $code)->exists(), 422);
    }
}
