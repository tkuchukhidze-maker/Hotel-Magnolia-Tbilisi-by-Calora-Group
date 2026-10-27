<?php

namespace App\Http\Controllers;

use App\Support\RoomCatalog;
use App\Support\RoomPricing;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $days = $start->daysInMonth;
        $rows = [];
        for ($day = 1; $day <= $days; $day++) {
            $date = $start->addDays($day - 1);
            // An inactive custom plan may have a valid editable base; use it in the admin preview.
            $rows[] = ['date' => $date->toDateString(), 'day' => $day, 'weekday' => $date->format('D')];
        }

        $base = DB::table('room_plan_prices')->get()->keyBy(fn($r) => $r->room_type.'|'.$r->plan_code);
        $monthly = DB::table('room_month_prices')->where('month', $month)->get()->keyBy(fn($r) => $r->room_type.'|'.$r->plan_code);
        $daily = DB::table('room_day_prices')->whereBetween('stay_date', [$start->toDateString(), $start->endOfMonth()->toDateString()])
            ->get()->keyBy(fn($r) => $r->room_type.'|'.$r->plan_code.'|'.$r->stay_date);
        $preview = [];
        foreach ($rows as $row) {
            $k = $room.'|'.$plan;
            $override = $daily->get($k.'|'.$row['date']) ?? $monthly->get($k) ?? $base->get($k);
            $legacy = $plan === 'bf' ? [$rooms[$room]['p1'][1], $rooms[$room]['p2'][1]] : [$rooms[$room]['p1'][0], $rooms[$room]['p2'][0]];
            $preview[$row['date']] = [
                'one' => (int) ($override->one_guest ?? $legacy[0]),
                'two' => (int) ($override->two_guests ?? $legacy[1]),
                'source' => $daily->has($k.'|'.$row['date']) ? 'day' : ($monthly->has($k) ? 'month' : ($base->has($k) ? 'plan' : 'base')),
            ];
        }
        return view('admin.pricing', compact('month','room','rooms','plan','plans','savedPlans','rows','preview','base','monthly'));
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
