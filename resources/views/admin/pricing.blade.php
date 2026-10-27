@extends('layouts.admin')
@section('title', 'Pricing calendar')

@php
    $q = fn (array $over) => route('admin.pricing.index', array_merge(['room' => $room, 'plan' => $plan, 'month' => $month], $over));
    $planName = collect($allPlans)->firstWhere('code', $plan)['name'] ?? $plan;
    $today = now()->toDateString();
@endphp

@section('content')
<div class="a-head">
<div>
<h1>Pricing calendar</h1>
<p>Drag across the calendar to pick nights (hold <kbd>Ctrl</kbd>/<kbd>⌘</kbd> to add more, <kbd>Shift</kbd> for a range, click a weekday name for every such day). Then set a price or raise/lower it in one go. Prices are GEL per room per night, taxes included.</p>
</div>
</div>

<div class="a-card" style="margin-bottom:16px">
<div class="pc-toolbar">
<div class="a-pills" role="group" aria-label="Room type">
@foreach ($rooms as $id => $r)<a class="a-pill" href="{{ $q(['room' => $id]) }}" @if ($room === $id) aria-current="true" @endif>{{ $r['name'] }}</a>@endforeach
</div>
<div class="a-pills" role="group" aria-label="Rate plan">
@foreach ($allPlans as $p)<a class="a-pill" href="{{ $q(['plan' => $p['code']]) }}" @if ($plan === $p['code']) aria-current="true" @endif>{{ $p['name'] }}{{ $p['is_active'] ? '' : ' (draft)' }}</a>@endforeach
</div>
</div>
</div>

<div class="pc-layout">
<section class="a-card" aria-label="Calendar">
<div class="pc-toolbar">
<div class="pc-month">
<a class="a-btn a-btn--light a-btn--sm" href="{{ $q(['month' => $prevMonth]) }}" aria-label="Previous month">←</a>
<h2>{{ $start->format('F Y') }}</h2>
<a class="a-btn a-btn--light a-btn--sm" href="{{ $q(['month' => $nextMonth]) }}" aria-label="Next month">→</a>
</div>
<form method="get" action="{{ route('admin.pricing.index') }}" class="a-row" style="gap:6px">
<input type="hidden" name="room" value="{{ $room }}"><input type="hidden" name="plan" value="{{ $plan }}">
<input class="a-input" type="month" name="month" value="{{ $month }}" style="width:auto" aria-label="Go to month">
<button class="a-btn a-btn--light a-btn--sm">Go</button>
</form>
</div>
<div class="pc-quick" role="group" aria-label="Quick select">
<button type="button" class="a-pill" data-pick="all">All nights</button>
<button type="button" class="a-pill" data-pick="weekdays">Sun–Thu</button>
<button type="button" class="a-pill" data-pick="weekends">Fri &amp; Sat</button>
<button type="button" class="a-pill" data-pick="none">Clear</button>
</div>

<div class="pc-cal" id="pc-cal" role="grid" aria-label="Nightly prices, {{ $rooms[$room]['name'] }}, {{ $planName }}, {{ $start->format('F Y') }}" aria-multiselectable="true">
@foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $i => $d)<button type="button" class="pc-wd" data-dow="{{ $i + 1 }}" title="Select every {{ $d }}">{{ $d }}</button>@endforeach
@for ($i = 1; $i < $start->dayOfWeekIso; $i++)<div class="pc-blank"></div>@endfor
@foreach ($rows as $d)
@php($p = $preview[$d['date']])
<div class="pc-day{{ $d['date'] < $today ? ' is-past' : '' }}{{ $d['date'] === $today ? ' is-today' : '' }}" role="gridcell" tabindex="0" aria-selected="false"
 data-date="{{ $d['date'] }}" data-dow="{{ $d['dow'] }}" data-one="{{ $p['one'] }}" data-two="{{ $p['two'] }}" data-past="{{ $d['date'] < $today ? 1 : 0 }}"
 aria-label="{{ $d['weekday'] }} {{ $d['day'] }}: {{ $p['two'] }} GEL for 2 guests, {{ $p['one'] }} for 1 ({{ $p['source'] }} price)">
<span class="pc-num">{{ $d['day'] }}</span>
<span class="pc-src pc-src--{{ $p['source'] }}" title="{{ ['day' => 'Special price for this night', 'month' => 'Month price', 'plan' => 'Default price', 'base' => 'Basic room price'][$p['source']] }}"></span>
<span class="pc-two">{{ number_format($p['two']) }} ₾</span>
<span class="pc-one">1 guest {{ number_format($p['one']) }}</span>
<span class="pc-new" aria-hidden="true"></span>
</div>
@endforeach
</div>
<div class="pc-legend">
<span><i style="background:var(--terracotta)"></i>Special price for that night</span>
<span><i style="background:var(--blue)"></i>Month price</span>
<span><i style="background:var(--gold)"></i>Default price</span>
<span><i style="background:#D9CDBE"></i>Basic room price</span>
<span>Big number: 2 guests · small: 1 guest</span>
</div>
</section>

<aside class="a-card pc-panel" aria-label="Change prices">
<form method="post" action="{{ route('admin.pricing.bulk') }}" id="pc-form" class="pc-fieldset" style="gap:14px">
@csrf
<div>
<h3>Change prices</h3>
<p class="pc-count" id="pc-count" aria-live="polite">Pick nights on the calendar.</p>
</div>
<input type="hidden" name="mode" value="set" id="pc-mode">
<div class="pc-modes" role="group" aria-label="How to change">
<button type="button" data-mode="set" aria-pressed="true">Set price</button>
<button type="button" data-mode="percent" aria-pressed="false">± %</button>
<button type="button" data-mode="amount" aria-pressed="false">± GEL</button>
</div>
<div data-for="set" class="a-row">
<label class="a-field">2 guests · GEL<input type="number" name="two_guests" min="1" max="100000" inputmode="numeric" id="pc-two" placeholder="e.g. 150"></label>
<label class="a-field">1 guest · GEL<input type="number" name="one_guest" min="1" max="100000" inputmode="numeric" id="pc-one" placeholder="e.g. 120"></label>
</div>
<div data-for="percent amount" class="a-row" hidden>
<label class="a-field"><span id="pc-value-label">Change by %</span><input type="number" name="value" step="any" id="pc-value" placeholder="10 or -15"></label>
</div>
<p class="a-help" style="margin:-6px 0 0" id="pc-hint">Leave a box empty to keep that price as it is.</p>

<hr class="pc-sep">
<fieldset class="pc-fieldset">
<legend>Rate plans</legend>
<div class="pc-checks">
@foreach ($allPlans as $p)<label class="a-check"><input type="checkbox" name="plans[]" value="{{ $p['code'] }}" @checked($p['code'] === $plan)> {{ $p['name'] }}</label>@endforeach
</div>
</fieldset>
<fieldset class="pc-fieldset">
<legend>Room types</legend>
<div class="pc-checks">
@foreach ($rooms as $id => $r)<label class="a-check"><input type="checkbox" name="rooms[]" value="{{ $id }}" @checked($id === $room)> {{ $r['name'] }}</label>@endforeach
</div>
</fieldset>

<details id="pc-range-box">
<summary class="a-help" style="cursor:pointer;font-weight:700;color:var(--terracotta);margin:0">Or type a longer date range</summary>
<div class="pc-range is-open" style="margin-top:10px">
<div class="a-row">
<label class="a-field">From<input type="date" name="from" id="pc-from"></label>
<label class="a-field">To<input type="date" name="to" id="pc-to"></label>
</div>
<div class="pc-checks" role="group" aria-label="Only these weekdays">
@foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $i => $d)<label class="a-check"><input type="checkbox" name="weekdays[]" value="{{ $i + 1 }}" checked> {{ $d }}</label>@endforeach
</div>
<p class="a-help" style="margin:0">Used instead of the calendar selection when both dates are filled.</p>
</div>
</details>

<div class="pc-actions">
<button class="a-btn" id="pc-apply" disabled>Apply</button>
<button class="a-btn a-btn--light" type="button" id="pc-reset" disabled title="Remove the special price so the month or default price applies">Reset to normal</button>
</div>
<div id="pc-dates"></div>
</form>
</aside>
</div>

<div class="a-grid-2">
<section class="a-card">
<h3>Price for a whole month</h3>
<p class="a-help">Applies to every night of the month without a special price. {{ $rooms[$room]['name'] }} · {{ $planName }}.</p>
<form method="post" action="{{ route('admin.pricing.schedule') }}" class="a-row">@csrf
<input type="hidden" name="room_type" value="{{ $room }}"><input type="hidden" name="plan_code" value="{{ $plan }}"><input type="hidden" name="scope" value="month">
<label class="a-field">Month<input type="month" name="month" required value="{{ $month }}"></label>
<label class="a-field">1 guest<input type="number" name="one_guest" min="1" max="100000" required value="{{ $monthly->get($room.'|'.$plan)->one_guest ?? '' }}"></label>
<label class="a-field">2 guests<input type="number" name="two_guests" min="1" max="100000" required value="{{ $monthly->get($room.'|'.$plan)->two_guests ?? '' }}"></label>
<button class="a-btn">Save month</button>
</form>
@if ($monthly->has($room.'|'.$plan))
<form method="post" action="{{ route('admin.pricing.remove') }}" style="margin-top:10px">@csrf @method('DELETE')
<input type="hidden" name="room_type" value="{{ $room }}"><input type="hidden" name="plan_code" value="{{ $plan }}"><input type="hidden" name="scope" value="month"><input type="hidden" name="date" value="{{ $month }}">
<button class="a-btn a-btn--danger a-btn--sm">Remove this month's price</button></form>
@endif
</section>

<section class="a-card">
<h3>Default price</h3>
<p class="a-help">Used when a night has neither a special nor a month price. Changes affect new bookings only.</p>
@php($default = $base->get($room.'|'.$plan))
<form method="post" action="{{ route('admin.pricing.base') }}" class="a-row">@csrf
<input type="hidden" name="room_type" value="{{ $room }}"><input type="hidden" name="plan_code" value="{{ $plan }}">
<label class="a-field">1 guest<input type="number" min="1" max="100000" name="one_guest" required value="{{ $default->one_guest ?? ($plan === 'bf' ? $rooms[$room]['p1'][1] : $rooms[$room]['p1'][0]) }}"></label>
<label class="a-field">2 guests<input type="number" min="1" max="100000" name="two_guests" required value="{{ $default->two_guests ?? ($plan === 'bf' ? $rooms[$room]['p2'][1] : $rooms[$room]['p2'][0]) }}"></label>
<button class="a-btn">Save default</button>
</form>
</section>
</div>

<div class="a-grid-2">
<section class="a-card">
<h3>New rate plan</h3>
<p class="a-help">E.g. Flexible, Non-refundable, Summer special. It stays hidden until every room type has a default price and you publish it.</p>
<form method="post" action="{{ route('admin.pricing.plans.store') }}" class="a-row">@csrf
<label class="a-field">Code<input name="code" maxlength="8" pattern="[A-Za-z0-9_-]+" placeholder="FLEX" required></label>
<label class="a-field" style="flex-grow:2">Name<input name="name" maxlength="90" placeholder="Flexible rate" required></label>
<label class="a-field">Breakfast<select name="includes_breakfast"><option value="0">Not included</option><option value="1">Included</option></select></label>
<button class="a-btn">Create draft</button>
</form>
</section>
<section class="a-card">
<h3>Rate plans</h3>
@forelse ($savedPlans as $p)
<div class="a-row" style="justify-content:space-between;border-bottom:1px solid var(--line);padding:10px 0">
<div><strong>{{ $p->name }}</strong> <span class="a-badge">{{ $p->code }}</span> <span class="a-badge {{ $p->is_active ? 'a-badge--ok' : 'a-badge--off' }}">{{ $p->is_active ? 'Published' : 'Draft' }}</span>
<div class="a-help" style="margin:2px 0 0">Breakfast {{ $p->includes_breakfast ? 'included' : 'not included' }}</div></div>
<form method="post" action="{{ route('admin.pricing.plans.publish', $p->code) }}">@csrf @method('PATCH')
<input type="hidden" name="is_active" value="{{ $p->is_active ? '0' : '1' }}"><button class="a-btn a-btn--sm {{ $p->is_active ? 'a-btn--light' : '' }}">{{ $p->is_active ? 'Unpublish' : 'Publish' }}</button></form>
</div>
@empty
<p class="a-help">Only the two standard plans so far: room only and with breakfast.</p>
@endforelse
</section>
</div>
@endsection

@push('scripts')
<script>window.PC = {!! json_encode(['room' => $room, 'plan' => $plan, 'key' => 'pc-sel-'.$room.'-'.$plan.'-'.$month]) !!};</script>
<script src="{{ asset('assets/js/admin-pricing.js') }}?v={{ filemtime(public_path('assets/js/admin-pricing.js')) }}" defer></script>
@endpush
