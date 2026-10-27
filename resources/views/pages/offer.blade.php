@extends('layouts.app', ['overlay' => true, 'fullFooter' => true])
@php($c = config('hotel.contact'))
@php($rating = config('hotel.rating'))
@php($on = $promo['active'])
@php($nights = 3)
@php($from = collect($rooms)->min(fn ($r) => $r['p1'][0]))
@php($order = ['double', 'deluxe', 'twin', 'triple', 'single'])

@section('title', $on
    ? $promo['discount'].'% Off Every Room, Book by '.$promo['ends_label'].' | Hotel Magnolia Tbilisi by Calora Group'
    : 'Offers | Hotel Magnolia Tbilisi by Calora Group')
@section('description', $on
    ? $promo['label'].': '.$promo['discount'].'% off every room at our boutique hotel in Old Tbilisi when you book direct by '.$promo['ends_label'].'. From GEL '.$from.' a night, pay at the hotel, free cancellation.'
    : 'Book direct with Hotel Magnolia Tbilisi by Calora Group for our lowest price, guaranteed. Pay at the hotel, free cancellation.')
@section('crumb', $on ? $promo['label'] : 'Offers')
@section('og_image', asset('assets/img/rooftop-view.webp'))

@push('head')
{!! \App\Support\StructuredData::render(\App\Support\StructuredData::hotel()) !!}
@endpush

@section('content')
<!-- HERO -->
<section class="hero offer-hero">
<img class="hero__img" src="{{ asset('assets/img/rooftop-view.webp') }}" alt="The view from our rooftop terrace over the old roofs of Tbilisi" fetchpriority="high">
<div class="hero__shade" aria-hidden="true"></div>
<svg class="decor decor--hide-sm" width="520" height="434" stroke-width=".35" style="right:-80px;top:90px;opacity:.28;color:var(--gold-light);z-index:1" aria-hidden="true"><use href="#mg-flower"/></svg>
<div class="container hero__content">
<div class="hero__copy">
@if ($on)
<p class="eyebrow">{{ $promo['label'] }} · book direct</p>
<h1 class="display h1">{{ $promo['discount'] }}% off every room. <em>Book by {{ $promo['ends_label'] }}.</em></h1>
<p class="hero__sub">Book here, not on a booking site, and every room in our old brick house in Old Tbilisi costs a quarter less. Nothing to pay until you arrive.</p>
@include('partials.countdown', ['variant' => 'glass'])
<div class="hero__actions">
<a class="btn btn--primary btn--lg" href="{{ route('rooms') }}">Book now &amp; save {{ $promo['discount'] }}%</a>
<a class="hero__story" href="#prices"><span class="hero__story-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M6 13l6 6 6-6"/></svg></span>See what you save</a>
</div>
<ul class="offer-ticks"><li>No card needed</li><li>Free cancellation</li><li>Rated {{ $rating['score'] }} on {{ $rating['source'] }}</li></ul>
@else
<p class="eyebrow">Book direct</p>
<h1 class="display h1">The {{ $promo['label'] }} offer <em>has ended.</em></h1>
<p class="hero__sub">Thank you to everyone who came. Booking here is still the best way to stay with us: our lowest price, guaranteed, and nothing to pay until you arrive.</p>
<div class="hero__actions"><a class="btn btn--primary btn--lg" href="{{ route('rooms') }}">See rooms &amp; prices</a></div>
@endif
</div>
</div>
</section>

<!-- WHAT YOU SAVE -->
<section id="prices" class="section decor-host" style="scroll-margin-top:90px">
<svg class="decor decor--hide-sm" width="420" height="210" stroke-width=".8" style="left:-80px;top:40px;opacity:.35;color:var(--terracotta)" aria-hidden="true"><use href="#mg-branch"/></svg>
<div class="container">
<div class="section-head">
<div class="stack" style="gap:16px">
<p class="eyebrow">{{ $on ? 'What '.$promo['discount'].'% looks like' : 'Rooms & prices' }}</p>
<h2 class="h2">{{ $on ? 'Same rooms.' : 'Five rooms,' }} <em>{{ $on ? 'A quarter less.' : 'one old house.' }}</em></h2>
</div>
<p class="lead" style="max-width:420px;font-size:16px">Per room per night for two guests, taxes included.@if ($on) Savings shown for a {{ $nights }}-night stay.@endif</p>
</div>
<div class="save-grid">
@foreach ($order as $id)
@php($r = $rooms[$id])
@php($now = $r['cap'] === 1 ? $r['p1'][0] : $r['p2'][0])
@php($was = $r['cap'] === 1 ? $r['w1'] : $r['w2'])
<article class="save-card">
<div class="save-card__media">
@if ($r['img'])<img src="{{ asset($r['img']) }}" alt="{{ $r['alt'] }}" loading="lazy" width="920" height="600">@else<svg width="120" height="100" stroke-width=".7" style="color:var(--terracotta);opacity:.45" aria-hidden="true"><use href="#mg-flower"/></svg>@endif
@if ($on && $was > $now)<span class="tag tag--deal">−{{ $promo['discount'] }}%</span>@endif
</div>
<div class="save-card__body">
<h3 class="save-card__name">{{ $r['name'] }}</h3>
<p class="small" style="margin:0">{{ $r['cap'] === 1 ? '1 guest' : 'Up to '.$r['cap'].' guests' }} · {{ $r['size'] }} · {{ $r['bed'] }}</p>
<p class="save-card__price">@if ($was > $now)<s>GEL {{ $was }}</s>@endif<strong>GEL {{ $now }}</strong><span>/ night</span></p>
@if ($on && $was > $now)<p class="save-card__save">You save GEL {{ ($was - $now) * $nights }} on {{ $nights }} nights</p>@endif
<a class="btn btn--outline btn--block" href="{{ route('rooms', ['room' => $id]) }}">Book this room</a>
</div>
</article>
@endforeach
</div>
</div>
</section>

<!-- WHY HERE -->
<section class="container" style="padding-bottom: clamp(72px, 9vw, 120px)" aria-labelledby="why-h">
<div class="direct decor-host">
<svg class="decor" width="300" height="250" stroke-width=".5" style="right:-60px;bottom:-60px;opacity:.2;color:var(--gold-light)" aria-hidden="true"><use href="#mg-flower"/></svg>
<div class="stack on-dark" style="gap:16px">
<p class="eyebrow">Why book here</p>
<h2 id="why-h" class="h3">The best deal is <em style="color:var(--gold-light)">at our front door.</em></h2>
<p class="lead" style="color:#E8DCCB;font-size:16px">Booking sites take a cut. When you book with us, we put that money back into your stay.</p>
</div>
<ul>
@if ($on)<li><strong>−{{ $promo['discount'] }}% on every room</strong><span>On everything you book here by {{ $promo['ends_label'] }}.</span></li>@endif
<li><strong>Lowest price, guaranteed</strong><span>Found it cheaper elsewhere? Send it to us and we'll match it.</span></li>
<li><strong>Nothing to pay now</strong><span>No card, no deposit. Pay when you arrive. Cancellation: {{ strtolower(config('hotel.policies.cancellation')) }}.</span></li>
<li><strong>Talk to the hotel</strong><span>Questions before you book? <a href="https://wa.me/{{ $c['whatsapp'] }}">WhatsApp us</a>, 24 hours.</span></li>
</ul>
</div>
</section>

<!-- THE MAGNOLIA -->
<section class="section section--sand decor-host">
<div class="container split">
<figure class="magnolia__media figure">
<img class="arch" src="{{ asset('assets/img/magnolia-courtyard.webp') }}" alt="The 127-year-old magnolia in the hotel courtyard, rising between brick walls and a spiral staircase" loading="lazy" width="1086" height="1448">
<div class="badge-127"><b>127</b><span>YEARS OLD</span></div>
</figure>
<div class="stack">
<p class="eyebrow">What's waiting for you</p>
<h2 class="h2">Coffee under a <em>127-year-old magnolia.</em></h2>
<p class="lead">A small hotel in an old brick house, 950 m from Freedom Square. Free parking, a rooftop terrace over the old town, and a tree in the courtyard that has been here since 1899.</p>
<ul class="checks" style="max-width:520px"><li>Free parking</li><li>Rooftop terrace</li><li>24-hour front desk</li><li>Buffet breakfast</li><li>Free Wi‑Fi</li><li>Family rooms</li></ul>
<p class="since">Since 1899</p>
</div>
</div>
</section>

<!-- HOW IT WORKS -->
<section class="section" aria-labelledby="how-h">
<div class="container">
<div class="stack" style="align-items:center;text-align:center;gap:14px;margin-bottom:40px">
<svg width="40" height="34" stroke-width="3" style="color:var(--terracotta)" aria-hidden="true"><use href="#mg-flower"/></svg>
<h2 id="how-h" class="h3">Booked in <em>two minutes.</em></h2>
</div>
<ol class="how">
<li><b>1</b><strong>Pick your dates &amp; rooms</strong><span>One room or several. The {{ $on ? 'discount is already in every price' : 'total updates as you go' }}.</span></li>
<li><b>2</b><strong>Leave your details</strong><span>Name, email and phone. No card, no account.</span></li>
<li><b>3</b><strong>Pay when you arrive</strong><span>We confirm by email or WhatsApp. See you in Tbilisi.</span></li>
</ol>
</div>
</section>

@if ($on)
<!-- OFFER TERMS -->
<section class="section section--sand" aria-labelledby="terms-h" style="padding-top:clamp(56px,7vw,88px)">
<div class="container" style="max-width:860px">
<h2 id="terms-h" class="h3" style="margin-bottom:24px">The offer, <em>in plain words.</em></h2>
<div class="faq">
<details open><summary>When does it end?</summary><p>On {{ $promo['ends_long'] }}. That's the moment the countdown reaches zero. After that, prices go back to normal: a Double Room for two will be GEL {{ $rooms['double']['w2'] }} a night instead of GEL {{ $rooms['double']['p2'][0] }}.</p></details>
<details><summary>Which rooms and dates?</summary><p>Every room type. The price you see when you book is the price you pay, for any dates you choose.</p></details>
<details><summary>Do I pay now?</summary><p>No. You don't need a card to book. You pay at the hotel ({{ strtolower(config('hotel.policies.payment')) }}).</p></details>
<details><summary>What if my plans change?</summary><p>Cancellation: {{ strtolower(config('hotel.policies.cancellation')) }}. Just write to us on WhatsApp or by email.</p></details>
<details><summary>Is it cheaper anywhere else?</summary><p>It shouldn't be. If you find our room cheaper somewhere else, send it to us and we'll match it.</p></details>
</div>
</div>
</section>
@endif

<!-- FINAL CTA -->
<section class="offer-final decor-host">
<svg class="decor" width="420" height="210" stroke-width=".8" style="left:-60px;top:24px;opacity:.3;color:var(--gold-light)" aria-hidden="true"><use href="#mg-branch"/></svg>
<svg class="decor decor--hide-sm" width="360" height="300" stroke-width=".4" style="right:-60px;bottom:-60px;opacity:.22;color:var(--ivory)" aria-hidden="true"><use href="#mg-flower"/></svg>
<div class="container offer-final__inner">
<p class="eyebrow" style="color:var(--gold-light)">{{ $on ? 'Don\'t leave it too late' : 'Your Tbilisi stay starts here' }}</p>
<h2 class="display" style="font-size:clamp(46px,6vw,88px)">{{ $on ? 'Your room, '.$promo['discount'].'% less.' : 'Come on in.' }}</h2>
@if ($on)
@include('partials.countdown', ['variant' => 'light', 'label' => 'Book within'])
@endif
<div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center"><a class="btn btn--light btn--lg" href="{{ route('rooms') }}">{{ $on ? 'Book now & save '.$promo['discount'].'%' : 'Check availability' }}</a><a class="btn btn--outline-light btn--lg" href="https://wa.me/{{ $c['whatsapp'] }}">WhatsApp us</a></div>
</div>
</section>
@endsection

@section('mobile-bar')
@if ($on)
<span class="mobile-book__price" data-countdown data-ends="{{ $promo['ends_ms'] }}" data-now="{{ $promo['now_ms'] }}" data-compact>ends in<strong data-cd-compact></strong></span>
<a class="btn btn--primary" href="{{ route('rooms') }}">Save {{ $promo['discount'] }}%</a>
@else
<span class="mobile-book__price">per night from<strong>GEL {{ $from }}</strong></span>
<a class="btn btn--primary" href="{{ route('rooms') }}">Book Now</a>
@endif
@endsection
