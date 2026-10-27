@extends('layouts.app')
@php($c = config('hotel.contact'))

@section('title', 'Rooms & Prices from GEL 106 | Magnolia Hotel Tbilisi')
@section('description', 'Five room types in Old Tbilisi, 15–30 m², from GEL 106 per night with taxes. See your total, book direct with free cancellation and pay at the hotel.')
@section('crumb', 'Rooms & booking')
@section('og_image', asset('assets/img/room-deluxe.webp'))

@push('head')
{!! \App\Support\StructuredData::render(\App\Support\StructuredData::hotel()) !!}
{!! \App\Support\StructuredData::render(\App\Support\StructuredData::rooms()) !!}
@endpush

@section('content')
<section class="container decor-host" style="padding-top:64px;padding-bottom:28px">
<svg class="decor decor--hide-sm" width="360" height="300" stroke-width=".4" style="right:-40px;top:-20px;opacity:.18;color:var(--terracotta)" aria-hidden="true"><use href="#mg-flower"/></svg>
<div class="section-head" style="margin-bottom:0">
<div class="stack" style="gap:14px">
<p class="eyebrow">Rooms &amp; booking</p>
<h1 class="display h1-page">Find your kind of <em>comfort.</em></h1>
</div>
<p class="lead" style="max-width:440px;font-size:16px">Tell us who's coming. We'll show the rooms that fit, the price for your stay, and everything that's in the room.</p>
</div>
</section>

<section id="search" class="container" aria-label="Your stay" style="scroll-margin-top:100px">
<div class="search">
<label class="field"><span class="field__body"><span class="field__label">Check-in</span><input type="date" id="checkin"></span></label>
<div class="stepper"><span class="stepper__value"><span class="field__label">Nights</span><strong data-value="nights">2 nights</strong></span><span class="stepper__btns"><button type="button" class="round-btn" data-action="nights-" aria-label="One night less">−</button><button type="button" class="round-btn" data-action="nights+" aria-label="One night more">+</button></span></div>
<div class="stepper"><span class="stepper__value"><span class="field__label">Adults</span><strong data-value="adults">2</strong></span><span class="stepper__btns"><button type="button" class="round-btn" data-action="adults-" aria-label="Remove an adult">−</button><button type="button" class="round-btn" data-action="adults+" aria-label="Add an adult">+</button></span></div>
<div class="stepper"><span class="stepper__value"><span class="field__label">Children</span><strong data-value="kids">0</strong></span><span class="stepper__btns"><button type="button" class="round-btn" data-action="kids-" aria-label="Remove a child">−</button><button type="button" class="round-btn" data-action="kids+" aria-label="Add a child">+</button></span></div>
</div>
<div class="search-summary">
<strong id="search-summary" aria-live="polite">{{ count($rooms) }} rooms</strong>
<span class="ticks"><span>✓ Free cancellation</span><span>✓ Pay at the hotel</span><span>✓ −{{ config('hotel.offer.discount') }}% autumn rate applied</span></span>
</div>
</section>

<section class="container" aria-label="Rooms">
<div class="room-list" id="room-list">
{{-- Server-rendered so search engines and AI crawlers see every room; rooms.js replaces it with the interactive list. --}}
@foreach ($rooms as $id => $r)
<article class="room" id="room-{{ $id }}">
<div class="room__main">
<div class="room__media">
@if ($r['img'])<img src="{{ asset($r['img']) }}" alt="{{ $r['alt'] }}" loading="lazy" width="920" height="600">@else<div class="room__placeholder">[Room photos coming soon]</div>@endif
</div>
<div class="room__body">
<div class="room__info">
<h2 class="room__name">{{ $r['name'] }}</h2>
<ul class="meta"><li>{{ $r['cap'] === 1 ? '1 guest' : 'Up to '.$r['cap'].' guests' }}</li><li>{{ $r['size'] }}</li><li>{{ $r['bed'] }}</li><li>{{ $r['extra'] }}</li></ul>
<p class="lead" style="font-size:16px">{{ $r['blurb'] }}</p>
<p class="small" style="font-size:14px">Air conditioning · Private bathroom · TV · Free Wi‑Fi · City &amp; garden view</p>
</div>
<div class="room__price">
<div><span class="small">per night, from</span><div class="big"><s>GEL {{ $r['cap'] === 1 ? $r['w1'] : $r['w2'] }}</s><strong>GEL {{ $r['cap'] === 1 ? $r['p1'][0] : $r['p2'][0] }}</strong></div><span class="small">GEL {{ $r['cap'] === 1 ? $r['p1'][1] : $r['p2'][1] }} with breakfast · taxes included</span></div>
<a class="btn btn--primary" href="tel:{{ $c['phone_tel'] }}">Call to book</a>
</div>
</div>
</div>
</article>
@endforeach
</div>
</section>

<section class="container" style="padding-top:28px;padding-bottom:96px">
<div class="why">
<div><strong>Lowest price here</strong><span>Found it cheaper? We'll match it.</span></div>
<div><strong>Nothing to pay now</strong><span>Payment: {{ config('hotel.policies.payment') }}.</span></div>
<div><strong>Change your mind?</strong><span>Cancellation: {{ strtolower(config('hotel.policies.cancellation')) }}.</span></div>
<div><strong>Rated {{ config('hotel.rating.score') }}</strong><span>{{ config('hotel.rating.label') }}, on {{ config('hotel.rating.source') }}.</span></div>
</div>
</section>
@endsection

@section('mobile-bar')
<span class="mobile-book__price">per night from<strong data-mobile-price>GEL 106</strong></span>
<a class="btn btn--primary" href="#room-list">Choose a room</a>
@endsection

@push('scripts')
<script>
window.MAGNOLIA = {!! json_encode([
    'rooms' => collect($rooms)->map(fn ($r, $id) => array_merge($r, [
        'id' => $id,
        'img' => $r['img'] ? asset($r['img']) : '',
    ]))->values(),
    'maxGuests' => config('hotel.max_guests'),
    'maxNights' => config('hotel.max_nights'),
    'bookingUrl' => route('bookings.store'),
    'whatsapp' => 'https://wa.me/'.$c['whatsapp'],
    'cancellation' => config('hotel.policies.cancellation'),
    'img' => ['bathroom' => asset('assets/img/bathroom.webp'), 'tray' => asset('assets/img/welcome-tray.webp')],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!};
</script>
<script src="{{ asset('assets/js/rooms.js') }}?v={{ filemtime(public_path('assets/js/rooms.js')) }}" defer></script>
@endpush
