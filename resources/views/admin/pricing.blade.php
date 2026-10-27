<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Pricing Calendar · Magnolia Management</title>
<style>
:root{font-family:system-ui,Arial,sans-serif;color:#382b23;background:#f7f4ef}*{box-sizing:border-box}
body{margin:0}header{background:#422e26;color:white;padding:20px clamp(16px,4vw,50px);display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap}
header h1{font-family:Georgia,serif;margin:0;font-size:26px}header a{color:#fff;text-decoration:none;border:1px solid #ffffff75;padding:10px 15px;border-radius:9px}
main{max-width:1450px;margin:0 auto;padding:22px}h2{font-family:Georgia,serif;font-size:26px}
.panel{background:#fff;border:1px solid #e6dcd0;border-radius:16px;padding:22px;margin:16px 0}
.controls{display:flex;align-items:end;gap:12px;flex-wrap:wrap}.controls label,.form-grid label{display:grid;gap:5px;font-size:13px;font-weight:650}
input,select,button{font:inherit;border-radius:9px;border:1px solid #d9cbbc;padding:11px;background:#fff;color:#382b23}button,.btn{background:#684638;color:white;border:0;cursor:pointer;padding:12px 16px;text-decoration:none;border-radius:9px;font:inherit;display:inline-block}
button.secondary{background:#eee5d9;color:#463227}.form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;align-items:end}.form-grid input,.form-grid select{width:100%}
.help{color:#74665c;font-size:13px;line-height:1.6}.notice{background:#fff3db;padding:12px;border-radius:8px}.success{background:#e3f3e8;padding:12px;border-radius:8px}.panel-heading{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
.calendar{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:7px}.weekday{text-align:center;color:#76695e;padding:12px 0;font-size:12px;font-weight:700}.day{background:#faf8f4;border:1px solid #e8ded2;border-radius:10px;min-height:102px;padding:10px;display:grid;gap:7px;align-content:start}.day strong{font-size:15px}.day .amount{font-size:18px;font-weight:750}.day small{font-size:11px;color:#75685d}.day[data-source=day]{border-color:#a94729;background:#fff8f4}.day[data-source=month]{border-color:#a5af82;background:#f7f9f0}.day button{padding:3px 7px;background:transparent;color:#684638;border:1px solid #e1d5c8;font-size:12px;text-align:left}
.day-empty{min-height:1px}.pill{background:#f2e9df;border-radius:99px;padding:5px 9px;font-size:11px}
.plan-row{display:flex;justify-content:space-between;gap:16px;align-items:center;flex-wrap:wrap;border-bottom:1px solid #eee4db;padding:12px 0}.plan-row:last-child{border:0}
.plan-row form{display:flex;gap:6px;align-items:center}
.columns{display:grid;grid-template-columns:1fr 1fr;gap:16px}.inline-form{display:inline}
@media(max-width:800px){.columns{grid-template-columns:1fr}.calendar{gap:3px}.day{padding:5px;min-height:75px}.day .amount{font-size:13px}.day small{font-size:9px}.panel{padding:13px}main{padding:10px}}
</style></head><body>
<header><div><h1>Room Pricing Calendar</h1><small>Magnolia Management · GEL per room / night</small></div><a href="{{ route('admin.dashboard') }}">← Dashboard</a></header>
<main>
@if(session('success'))<p role="status" class="success">{{ session('success') }}</p>@endif
@if($errors->any())<p role="alert" class="notice">{{ $errors->first() }}</p>@endif
<section class="panel">
<div class="panel-heading"><div><h2>Pricing calendar</h2><p class="help">Choose room, rate plan and month. A daily override takes priority over a monthly price, then the default price. All prices include configured taxes.</p></div></div>
<form class="controls" method="get" action="{{ route('admin.pricing.index') }}">
<label>Room type<select name="room">@foreach($rooms as $id=>$r)<option value="{{ $id }}" @selected($room===$id)>{{ $r['name'] }}</option>@endforeach</select></label>
<label>Rate plan<select name="plan"><option value="room" @selected($plan==='room')>Room only</option><option value="bf" @selected($plan==='bf')>Buffet breakfast</option>@foreach($savedPlans as $p)<option value="{{ $p->code }}" @selected($plan===$p->code)>{{ $p->name }}{{ $p->is_active?'':' (draft)' }}</option>@endforeach</select></label>
<label>Month<input type="month" name="month" value="{{ $month }}" required></label><button>Show prices</button>
</form>
@php($firstDay = \Carbon\CarbonImmutable::createFromFormat('!Y-m-d', $month.'-01'))
<div class="panel-heading"><h3>{{ $firstDay->format('F Y') }} — {{ $rooms[$room]['name'] }}</h3><span class="pill">1 guest / 2 guests · GEL</span></div>
<div class="calendar" aria-label="Nightly room prices for {{ $month }}">
@foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d)<div class="weekday">{{ $d }}</div>@endforeach
@for($i=1;$i<$firstDay->dayOfWeekIso;$i++)<div class="day-empty"></div>@endfor
@foreach($rows as $d)
@php($prices=$preview[$d['date']])
<div class="day" data-source="{{ $prices['source'] }}">
<strong>{{ $d['day'] }} <small>{{ $d['weekday'] }}</small></strong>
<span class="amount">{{ number_format($prices['two']) }} ₾</span>
<small>1 guest: {{ number_format($prices['one']) }} ₾</small>
<small>{{ ucfirst($prices['source']) }} price</small>
<button type="button" onclick="document.getElementById('range-from').value=this.dataset.date;document.getElementById('range-to').value=this.dataset.date;document.getElementById('range-one').value=this.dataset.one;document.getElementById('range-two').value=this.dataset.two;document.getElementById('range-form').scrollIntoView({behavior:'smooth',block:'center'})" data-date="{{ $d['date'] }}" data-one="{{ $prices['one'] }}" data-two="{{ $prices['two'] }}">Edit day</button>
@if($prices['source']==='day')<form method="post" action="{{ route('admin.pricing.remove') }}">@csrf @method('DELETE')
<input type="hidden" name="room_type" value="{{ $room }}"><input type="hidden" name="plan_code" value="{{ $plan }}"><input type="hidden" name="scope" value="day"><input type="hidden" name="date" value="{{ $d['date'] }}"><button class="secondary" title="Remove date override">Reset</button></form>@endif
</div>
@endforeach
</div></section>
<div class="columns">
<section class="panel"><h2>Change a whole month</h2><p class="help">Applies to every night in this month unless the night has a special price.</p>
<form method="post" action="{{ route('admin.pricing.schedule') }}">@csrf
<input type="hidden" name="room_type" value="{{ $room }}"><input type="hidden" name="plan_code" value="{{ $plan }}"><input type="hidden" name="scope" value="month">
<div class="form-grid"><label>Month<input type="month" name="month" required value="{{ $month }}"></label>
<label>1 guest · GEL<input type="number" name="one_guest" min="1" max="100000" required value="{{ $monthly->get($room.'|'.$plan)->one_guest ?? '' }}"></label>
<label>2 guests · GEL<input type="number" name="two_guests" min="1" max="100000" required value="{{ $monthly->get($room.'|'.$plan)->two_guests ?? '' }}"></label>
<button>Save month</button></div></form>
@if($monthly->has($room.'|'.$plan))<form method="post" action="{{ route('admin.pricing.remove') }}" style="margin-top:12px">@csrf @method('DELETE')
<input type="hidden" name="room_type" value="{{ $room }}"><input type="hidden" name="plan_code" value="{{ $plan }}"><input type="hidden" name="scope" value="month"><input type="hidden" name="date" value="{{ $month }}">
<button class="secondary">Clear this month's override</button></form>@endif</section>
<section class="panel" id="range-form"><h2>Set a day or date range</h2><p class="help">Choose one day or a range (up to 367 days). A date override beats the month's price.</p>
<form method="post" action="{{ route('admin.pricing.schedule') }}">@csrf
<input type="hidden" name="room_type" value="{{ $room }}"><input type="hidden" name="plan_code" value="{{ $plan }}"><input type="hidden" name="scope" value="range">
<div class="form-grid"><label>From<input id="range-from" type="date" name="from" value="{{ $month }}-01" required></label>
<label>To (inclusive)<input id="range-to" type="date" name="to" value="{{ $month }}-01" required></label>
<label>1 guest · GEL<input id="range-one" type="number" min="1" max="100000" name="one_guest" required></label>
<label>2 guests · GEL<input id="range-two" type="number" min="1" max="100000" name="two_guests" required></label><button>Save dates</button></div></form></section>
</div>
<div class="columns">
<section class="panel"><h2>Default price for this plan</h2><p class="help">Used if a date has neither a daily nor monthly override. A change applies only to future booking quotes.</p>
<form method="post" action="{{ route('admin.pricing.base') }}">@csrf
<input type="hidden" name="room_type" value="{{ $room }}"><input type="hidden" name="plan_code" value="{{ $plan }}">
@php($default=$base->get($room.'|'.$plan))
<div class="form-grid"><label>1 guest · GEL<input type="number" min="1" max="100000" name="one_guest" required value="{{ $default->one_guest ?? ($plan==='bf' ? $rooms[$room]['p1'][1] : $rooms[$room]['p1'][0]) }}"></label>
<label>2 guests · GEL<input type="number" min="1" max="100000" name="two_guests" required value="{{ $default->two_guests ?? ($plan==='bf' ? $rooms[$room]['p2'][1] : $rooms[$room]['p2'][0]) }}"></label>
<button>Save default</button></div></form></section>
<section class="panel"><h2>Create a rate plan</h2><p class="help">Examples: Flexible, Non-refundable, Summer special. A new plan stays hidden until all five room types have default prices and you publish it.</p>
<form method="post" action="{{ route('admin.pricing.plans.store') }}">@csrf
<div class="form-grid"><label>Short code (max 8)<input name="code" maxlength="8" pattern="[A-Za-z0-9_-]+" placeholder="FLEX" required></label>
<label>Name<input name="name" maxlength="90" placeholder="Flexible rate" required></label>
<label>Breakfast included<select name="includes_breakfast"><option value="0">No</option><option value="1">Yes</option></select></label>
<button>Create draft</button></div></form></section>
</div>
<section class="panel"><h2>Manage rate plans</h2>
@foreach($savedPlans as $p)<div class="plan-row"><div><strong>{{ $p->name }}</strong> <span class="pill">{{ $p->code }}</span> <span class="pill">{{ $p->is_active?'Published':'Draft' }}</span><p class="help">Breakfast: {{ $p->includes_breakfast?'included':'not included' }} · Set prices for every room type before publishing.</p></div>
<form method="post" action="{{ route('admin.pricing.plans.publish', $p->code) }}">@csrf @method('PATCH')
<input type="hidden" name="is_active" value="{{ $p->is_active?'0':'1' }}"><button class="{{ $p->is_active?'secondary':'' }}">{{ $p->is_active?'Unpublish':'Publish' }}</button></form></div>
@endforeach
<p class="notice">Published custom plans appear as choices on the booking page. Inventory is not synchronized with a PMS. Rates do not imply an available room.</p>
</section></main></body></html>