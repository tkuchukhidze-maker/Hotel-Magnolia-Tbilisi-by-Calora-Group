<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Magnolia Management</title>
<style>
:root{font-family:system-ui,sans-serif;color:#382b23;background:#f7f4ef}*{box-sizing:border-box}body{margin:0}header{background:#422e26;color:#fff;padding:20px clamp(16px,4vw,60px);display:flex;justify-content:space-between;align-items:center;gap:20px}h1,h2{font-family:Georgia,serif}h1{font-size:25px;margin:0}header button{background:transparent;border:1px solid #e4cfc2;color:white}main{max-width:1400px;margin:auto;padding:24px}nav{display:flex;gap:8px;overflow-x:auto;margin-bottom:24px}nav a{white-space:nowrap;text-decoration:none;color:#513a2d;background:#fff;border:1px solid #e4d9cd;padding:12px 16px;border-radius:9px}.metrics{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}.metric,.panel{background:#fff;border:1px solid #e7dfd6;border-radius:16px;padding:22px}.metric small{display:block;color:#756b62;margin-bottom:10px}.metric strong{font-size:28px}.panel{margin-top:20px}table{width:100%;border-collapse:collapse;font-size:13px}td,th{text-align:left;border-bottom:1px solid #e8e1d9;padding:12px 9px;white-space:nowrap}th{color:#756b62}input,select,button{font:inherit;padding:9px;border:1px solid #d4c8bc;border-radius:7px}input[type=number]{width:95px}button{background:#654638;color:white;border:0;cursor:pointer}form.inline{display:flex;gap:7px;align-items:center}.scroll{overflow-x:auto}.room-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(270px,1fr));gap:14px}.room-grid article{background:#faf7f2;border-radius:12px;padding:18px}.room-grid label{display:flex;justify-content:space-between;align-items:center;gap:10px;margin:8px 0;font-size:13px}.room-grid input{max-width:110px}.promotion-form{display:flex;flex-wrap:wrap;gap:10px;align-items:end}.promotion-form label{display:grid;gap:5px;font-size:13px}.notice{background:#fff3db;padding:12px;border-radius:8px}.success{background:#e3f3e8;padding:12px;border-radius:8px}@media(max-width:700px){.metrics{grid-template-columns:repeat(2,1fr)}main{padding:12px}header{padding:18px}}
</style></head><body>
<header><div><h1>Magnolia Management</h1><small>Hotel operations · private dashboard</small></div><form method="post" action="{{ route('admin.logout') }}">@csrf<button>Sign out</button></form></header>
<main>
<nav><a href="#overview">Overview</a><a href="#bookings">Bookings</a><a href="#rooms">Room prices</a><a href="#promotions">Promotions</a><a href="#analytics">Analytics & integrations</a></nav>
@if(session('success'))<p class="success" role="status">{{ session('success') }}</p>@endif
@if($errors->any())<p class="notice" role="alert">{{ $errors->first() }}</p>@endif
<section id="overview"><h2>Overview</h2><div class="metrics">
<div class="metric"><small>Pending requests</small><strong>{{ $stats['new_requests'] }}</strong></div>
<div class="metric"><small>Today's arrivals</small><strong>{{ $stats['arrivals'] }}</strong></div>
<div class="metric"><small>Guests arriving today</small><strong>{{ $stats['guests_today'] }}</strong></div>
<div class="metric"><small>Monthly booking base value*</small><strong>{{ number_format($stats['base_value']) }} ₾</strong></div></div>
<p class="notice">*Requested/confirmed room value, not collected revenue. Extra beds, refunds and channel bookings are not reconciled.</p></section>
<section id="bookings" class="panel"><h2>Latest booking requests</h2><div class="scroll"><table><thead><tr><th>Reference</th><th>Guest</th><th>Phone</th><th>Stay</th><th>Rooms</th><th>Base total</th><th>Status</th><th>Update</th></tr></thead><tbody>
@forelse($bookings as $booking)<tr><td>{{ $booking->reference }}</td><td>{{ $booking->guestName() }}</td><td>{{ $booking->phone }}</td><td>{{ $booking->checkin->format('d M') }} – {{ $booking->checkout->format('d M') }}</td><td>{{ $booking->roomsSummary() }}</td><td>{{ number_format($booking->total) }} ₾</td><td>{{ $booking->status }}</td><td>
<form class="inline" method="post" action="{{ route('admin.bookings.status', $booking) }}">@csrf @method('PATCH')
<select name="status" aria-label="Status for {{ $booking->reference }}">@foreach(['requested','confirmed','cancelled','rejected','checked_in','checked_out'] as $status)<option value="{{ $status }}" @selected($booking->status === $status)>{{ ucfirst(str_replace('_',' ',$status)) }}</option>@endforeach</select><button>Save</button></form></td></tr>
@empty<tr><td colspan="8">No booking requests yet.</td></tr>@endforelse</tbody></table></div></section>
<section id="rooms" class="panel"><h2>Room prices</h2><p>Rates are GEL per room / night, for 1 or 2 guests, with or without breakfast. Updates apply to new requests, not existing bookings.</p><div class="room-grid">
@foreach($rooms as $id => $room)@php($override = $overrides->get($id))
<article><h3>{{ $room['name'] }}</h3><p>{{ $room['size'] }} · standard max {{ $room['cap'] }} guests</p>
<form method="post" action="{{ route('admin.rooms.rate', $id) }}">@csrf @method('PUT')
<label>1 guest · room <input type="number" min="1" name="one_room" value="{{ old('one_room', $override->one_room ?? $room['p1'][0]) }}" required></label>
<label>1 guest · breakfast <input type="number" min="1" name="one_breakfast" value="{{ old('one_breakfast', $override->one_breakfast ?? $room['p1'][1]) }}" required></label>
<label>2 guests · room <input type="number" min="1" name="two_room" value="{{ old('two_room', $override->two_room ?? $room['p2'][0]) }}" required></label>
<label>2 guests · breakfast <input type="number" min="1" name="two_breakfast" value="{{ old('two_breakfast', $override->two_breakfast ?? $room['p2'][1]) }}" required></label>
<button type="submit">Save prices</button></form></article>@endforeach
</div></section>
<section id="promotions" class="panel"><h2>Promotions</h2><p class="notice">Promotions are drafts only. They do not alter customer checkout until pricing rules, date validity and stacking safeguards are implemented.</p>
<form class="promotion-form" method="post" action="{{ route('admin.promotions.store') }}">@csrf
<label>Promotion name<input name="name" required maxlength="100"></label>
<label>Code<input name="code" required maxlength="32"></label>
<label>Discount %<input type="number" name="discount_percent" min="1" max="90" required></label>
<label>Start<input type="date" name="starts_at" required></label>
<label>End<input type="date" name="ends_at" required></label>
<button type="submit">Create draft</button></form>
<div class="scroll"><table><thead><tr><th>Name</th><th>Code</th><th>Discount</th><th>Dates</th><th>Status</th></tr></thead><tbody>
@forelse($promotions as $promotion)<tr><td>{{ $promotion->name }}</td><td>{{ $promotion->code }}</td><td>{{ $promotion->discount_percent }}%</td><td>{{ $promotion->starts_at }} – {{ $promotion->ends_at }}</td><td>Draft · not published</td></tr>
@empty<tr><td colspan="5">No promotions yet.</td></tr>@endforelse
</tbody></table></div></section>
<section id="analytics" class="panel"><h2>Analytics & Google integrations</h2><p>Booking-request metrics are displayed above. Google Analytics 4, Google Tag Manager, Search Console, Google Business Profile and Looker Studio are planned connections and are <strong>not connected yet</strong>.</p><p>Connect verified accounts and consent-aware measurement before showing external traffic, conversions, or campaign attribution.</p></section>
</main></body></html>