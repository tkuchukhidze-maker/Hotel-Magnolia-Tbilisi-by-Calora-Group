@extends('layouts.app', ['overlay' => true, 'fullFooter' => true])
@php($c = config('hotel.contact'))
@php($rating = config('hotel.rating'))

@section('title', 'Hotel Magnolia Tbilisi by Calora Group | Boutique Hotel, Old Tbilisi')
@php($from = collect($rooms)->min(fn ($r) => $r['p1'][0]))
@section('description', 'Small boutique hotel at 5 Kirile Ukleba St, Old Tbilisi, 950 m from Freedom Square. Rooms from GEL '.$from.', free parking, rooftop terrace and a 127-year-old magnolia. Book direct, pay at the hotel.')

@push('head')
{!! \App\Support\StructuredData::render(\App\Support\StructuredData::hotel()) !!}
{!! \App\Support\StructuredData::render([
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => config('hotel.name'),
    'url' => url('/'),
    'publisher' => ['@id' => url('/').'#hotel'],
]) !!}
@endpush

@section('content')
<!-- HERO -->
<section class="hero">
<img class="hero__img" src="{{ \App\Support\Media::slot('home.hero')['src'] }}" alt="{{ \App\Support\Media::slot('home.hero')['alt'] }}" fetchpriority="high" width="1086" height="1448">
<div class="hero__shade" aria-hidden="true"></div>
<div class="container hero__content">
<div class="hero__copy">
<p class="eyebrow">A small hotel with a lot of character</p>
<h1 class="display h1">Make yourself at home <em>in Tbilisi.</em></h1>
<p class="hero__sub">Come on in. Your room is ready when you are.</p>
<div class="hero__actions">
<a class="btn btn--primary" href="{{ route('rooms') }}">Explore Rooms</a>
<a class="hero__story" href="#magnolia"><span class="hero__story-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21V11"/><path d="M12 11c0-4 3-7 7-7 0 4-3 7-7 7z"/><path d="M12 14c0-3-2-5-6-5 0 3 2 5 6 5z"/></svg></span>Our 127-year-old magnolia</a>
</div>
</div>
</div>
</section>

<!-- BOOKING BAR -->
<section class="booking container" aria-label="Check availability">
<form class="booking__form" action="{{ route('rooms') }}" method="get" data-home-booking>
<label class="field"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg><span class="field__body"><span class="field__label">Check-in</span><input type="date" name="checkin"></span></label>
<label class="field"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg><span class="field__body"><span class="field__label">Check-out</span><input type="date" name="checkout"></span></label>
<label class="field"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg><span class="field__body"><span class="field__label">Adults</span><select name="adults">@for ($i = 1; $i <= config('hotel.max_adults'); $i++)<option value="{{ $i }}" @selected($i === 2)>{{ $i }} {{ \Illuminate\Support\Str::plural('adult', $i) }}</option>@endfor</select></span></label>
<label class="field"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="9" r="3"/><path d="M7 21a5 5 0 0 1 10 0"/></svg><span class="field__body"><span class="field__label">Children</span><select name="children">@for ($i = 0; $i <= config('hotel.max_children'); $i++)<option value="{{ $i }}">{{ $i === 0 ? 'No children' : $i.' '.\Illuminate\Support\Str::plural('child', $i) }}</option>@endfor</select></span></label>
<label class="field"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 18V8M3 14h18v4M21 14v-2a3 3 0 0 0-3-3h-7v5"/></svg><span class="field__body"><span class="field__label">Room type</span><select name="room"><option value="">Any room</option>@foreach (config('hotel.rooms') as $id => $r)<option value="{{ $id }}">{{ $r['name'] }}</option>@endforeach</select></span></label>
<button class="btn btn--dark" type="submit">Check Availability <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg></button>
</form>
@if ($promo['active'])
<p class="booking__note">{{ $promo['label'] }}: <strong>{{ $promo['discount'] }}% off every room</strong> when you book by {{ $promo['ends_label'] }} · <a href="{{ route('offer') }}">see the offer</a></p>
@else
<p class="booking__note">Book here for our <strong>lowest price, guaranteed</strong> · pay at the hotel</p>
@endif
</section>

<!-- TRUST -->
<section class="container" aria-label="Why guests trust us">
<ul class="trust">
<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/></svg><strong>Rated {{ $rating['score'] }} · {{ $rating['label'] }}</strong><span>by guests on {{ $rating['source'] }}</span></li>
<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg><strong>Free cancellation</strong><span>plans change, we get it</span></li>
<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M7 15h3"/></svg><strong>Pay at the hotel</strong><span>no prepayment needed</span></li>
<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 12l-8 8-9-9V3h8z"/><circle cx="7.5" cy="7.5" r="1.5"/></svg><strong>Best price here</strong><span>found it cheaper? we match it</span></li>
<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 17V8h4a3 3 0 0 1 0 6H9"/></svg><strong>Free parking</strong><span>rare in the old town</span></li>
<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 18h14M7 18V11a5 5 0 0 1 10 0v7"/><path d="M12 4v2M10 21h4"/></svg><strong>Someone's always here</strong><span>24-hour front desk</span></li>
</ul>
<div class="ornament" aria-hidden="true"><svg width="34" height="28" stroke-width="3"><use href="#mg-flower"/></svg></div>
</section>

@if ($promo['active'])
<!-- OFFER BAND -->
<section class="container" aria-labelledby="offer-band-h" style="padding-top:40px">
<div class="offer-band decor-host">
<svg class="decor" width="300" height="250" stroke-width=".5" style="right:-50px;top:-40px;opacity:.22;color:var(--ivory)" aria-hidden="true"><use href="#mg-flower"/></svg>
<div class="offer-band__text">
<p class="eyebrow" style="color:var(--gold-light)">{{ $promo['label'] }} · book direct</p>
<h2 id="offer-band-h" class="h3">{{ $promo['discount'] }}% off every room. <em>Until {{ $promo['ends_label'] }}.</em></h2>
</div>
@include('partials.countdown', ['variant' => 'light'])
<a class="btn btn--light" href="{{ route('offer') }}">Get {{ $promo['discount'] }}% off</a>
</div>
</section>
@endif

<!-- MEET MAGNOLIA -->
<section class="section decor-host" style="padding-top: 72px">
<svg class="decor decor--hide-sm" width="520" height="434" stroke-width="0.35" style="left:-120px;top:40px;opacity:.22;color:var(--terracotta)" aria-hidden="true"><use href="#mg-flower"/></svg>
<div class="container split">
<div class="stack">
<p class="eyebrow">Meet Magnolia</p>
<h2 class="h2">A little different. <em>A lot more personal.</em></h2>
<p class="lead">A small hotel in an old brick house in the historic heart of Tbilisi. Classic furniture, floral fabrics, gold frames, and lamps that each have their own idea of light.</p>
<a class="link-arrow" href="{{ route('our-hotel') }}">Discover our hotel <span aria-hidden="true">→</span></a>
<svg width="300" height="150" stroke-width="1.1" style="color:var(--terracotta);opacity:.55;margin-top:12px;max-width:100%" aria-hidden="true"><use href="#mg-branch"/></svg>
</div>
<figure class="figure" style="flex-grow:1.2">
<img src="{{ \App\Support\Media::slot('home.lobby')['src'] }}" alt="{{ \App\Support\Media::slot('home.lobby')['alt'] }}" loading="lazy" style="height:clamp(300px,36vw,460px)" width="1400" height="933">
<figcaption class="caption">Chandeliers, red armchairs and a table for morning coffee.</figcaption>
</figure>
</div>
</section>

<!-- 127-YEAR-OLD MAGNOLIA -->
<section id="magnolia" class="section section--sand decor-host">
<svg class="decor decor--hide-sm" style="top:0;right:0;width:46%;height:100%;opacity:.5" aria-hidden="true"><defs><pattern id="mg-leaves" width="96" height="96" patternUnits="userSpaceOnUse"><path d="M24 40 C14 32 12 18 20 10 C30 18 32 32 24 40Z" fill="none" stroke="#C9B497"/><path d="M24 40 L20 12" stroke="#C9B497" stroke-width=".8"/><path d="M72 88 C82 80 84 66 76 58 C66 66 64 80 72 88Z" fill="none" stroke="#C9B497"/><circle cx="72" cy="24" r="1.6" fill="#C9B497"/><circle cx="24" cy="72" r="1.6" fill="#C9B497"/></pattern></defs><rect width="100%" height="100%" fill="url(#mg-leaves)"/></svg>
<div class="container split">
<figure class="magnolia__media figure">
<img class="arch" src="{{ \App\Support\Media::slot('magnolia')['src'] }}" alt="{{ \App\Support\Media::slot('magnolia')['alt'] }}" loading="lazy" width="1086" height="1448">
<div class="badge-127"><b>127</b><span>YEARS OLD</span></div>
</figure>
<div class="stack">
<p class="eyebrow">The magnolia in our courtyard</p>
<h2 class="h2">Our magnolia is 127 years old. <em>We're the new ones here.</em></h2>
<p class="lead">Long before there was a hotel, there was this tree. It grows in our courtyard, between old brick and new glass, and reaches past the top floor.</p>
<p class="lead">Take your coffee down and sit under it for a while. It has seen 127 Tbilisi summers. It's in no hurry, and neither should you be.</p>
<div><a class="btn btn--primary" href="{{ route('rooms') }}">Rooms with a garden view</a></div>
<p class="since">Since 1899</p>
</div>
</div>
</section>

<!-- ROOMS PREVIEW -->
<section class="section">
<div class="container">
<div class="section-head">
<div class="stack" style="gap:16px">
<p class="eyebrow">Find your room</p>
<h2 class="h2">Your room. <em>Your rhythm.</em></h2>
</div>
<a class="link-arrow" href="{{ route('rooms') }}">See all {{ count($rooms) }} rooms <span aria-hidden="true">→</span></a>
</div>
<div class="cards">
@foreach (['double', 'deluxe', 'twin'] as $id)
@php($r = $rooms[$id])
<a class="room-card" href="{{ route('rooms', ['room' => $id]) }}">
<img src="{{ \App\Support\Media::src($r['img']) }}" alt="{{ $r['alt'] }}" loading="lazy" width="1280" height="853">
<span class="room-card__body">
<span><span class="room-card__name">{{ $r['name'] }}</span><span class="room-card__meta">{{ $r['cap'] }} guests · {{ $r['size'] }} · {{ strtolower($r['extra'] === 'Original art' ? $r['bed'] : $r['extra']) }}</span></span>
<span class="price">@if ($r['w2'] > $r['p2'][0])<s>GEL {{ $r['w2'] }}</s>@endif<strong>GEL {{ $r['p2'][0] }}</strong></span>
</span>
</a>
@endforeach
</div>
<p class="small" style="margin-top:18px">Per night for 2 guests, taxes included. Plus a Triple for families and a Single for solo trips.</p>
</div>
</section>

<!-- BOOK DIRECT -->
<section class="container" style="padding-bottom: clamp(72px, 9vw, 120px)" aria-labelledby="direct-h">
<div class="direct decor-host">
<svg class="decor" width="420" height="210" stroke-width=".8" style="left:-60px;bottom:-40px;opacity:.35;color:var(--gold)" aria-hidden="true"><use href="#mg-branch"/></svg>
<div class="stack on-dark" style="gap:16px">
<p class="eyebrow">Book direct</p>
<h2 id="direct-h" class="h3">Book with us, <em style="color:var(--gold-light)">not through someone else.</em></h2>
<p class="lead" style="color:#E8DCCB;font-size:16px">Same rooms, same people, better terms. And if anything's unclear, you're talking to the hotel, not a call centre.</p>
</div>
<ul>
<li><strong>Lowest price</strong><span>Found it cheaper elsewhere? Send it to us and we'll match it.</span></li>
@if ($promo['active'])<li><strong>−{{ $promo['discount'] }}% this autumn</strong><span>On every room you book by {{ $promo['ends_label'] }}. <a href="{{ route('offer') }}">The offer</a></span></li>@endif
<li><strong>Nothing to pay now</strong><span>Pay at the hotel. Cancellation: {{ strtolower(config('hotel.policies.cancellation')) }}.</span></li>
<li><strong>A real person</strong><span>Questions first? <a href="https://wa.me/{{ $c['whatsapp'] }}">WhatsApp us</a>.</span></li>
</ul>
</div>
</section>

<!-- REVIEWS -->
<section class="section section--sand decor-host" aria-labelledby="reviews-h">
<svg class="decor decor--hide-sm" width="460" height="384" stroke-width=".35" style="right:-90px;top:-60px;opacity:.2;transform:rotate(18deg);color:var(--terracotta)" aria-hidden="true"><use href="#mg-flower"/></svg>
<div class="container">
<div class="section-head">
<div class="stack" style="gap:16px">
<p class="eyebrow">Good words from our guests</p>
<h2 id="reviews-h" class="h2">In their own words.</h2>
</div>
<div class="rating"><span class="rating__score">{{ $rating['score'] }}</span><span style="display:flex;flex-direction:column;font-size:14px"><strong style="font-size:16px">{{ $rating['label'] }}</strong><span class="small">{{ $rating['source'] }} · {{ $rating['count'] }} reviews</span></span><a href="https://www.booking.com" style="font-weight:700;font-size:14px">Read all reviews</a></div>
</div>
<div class="reviews">
@foreach (['Booking.com', 'Google', '[Source]'] as $source)
<blockquote class="review"><span class="review__stars" aria-label="5 out of 5">★★★★★</span><p>“[Real guest review, copied word for word]”</p><footer>[Guest name] · [Country] · {{ $source }}, [month year]</footer></blockquote>
@endforeach
</div>
</div>
</section>

<!-- MORE OF MAGNOLIA -->
<section class="section" aria-labelledby="more-h">
<div class="container">
<div class="stack" style="align-items:center;text-align:center;gap:14px;margin-bottom:40px">
<svg width="40" height="34" stroke-width="3" style="color:var(--terracotta)" aria-hidden="true"><use href="#mg-flower"/></svg>
<h2 id="more-h" class="h3">More of <em>Magnolia.</em></h2>
</div>
<div class="tiles">
<a class="tile" href="{{ route('our-hotel') }}"><img src="{{ asset('assets/img/rooftop-view.webp') }}" alt="" loading="lazy"><span class="tile__text"><strong>Our Hotel &amp; Gallery</strong><span>The house, the roof, the details →</span></span></a>
<a class="tile" href="{{ route('explore') }}"><img src="{{ asset('assets/img/rooftop-domes.webp') }}" alt="" loading="lazy"><span class="tile__text"><strong>Explore Tbilisi</strong><span>What's around the corner →</span></span></a>
<a class="tile" href="{{ route('contact') }}"><img src="{{ asset('assets/img/entrance-door.webp') }}" alt="" loading="lazy"><span class="tile__text"><strong>Good to know</strong><span>Check-in, policies, getting here →</span></span></a>
</div>
</div>
</section>

<!-- CTA -->
<section class="cta-split">
<div class="cta-split__text">
<p class="eyebrow">Your Tbilisi stay starts here</p>
<h2 class="display" style="font-size:clamp(52px,6.5vw,100px)">Come on in.</h2>
<p style="margin:0;font-size:18px;max-width:480px">@if ($promo['active']){{ $promo['discount'] }}% off every room until {{ $promo['ends_label'] }}. @endif Book now, pay when you arrive, cancel free if plans change.</p>
<div style="display:flex;flex-wrap:wrap;gap:12px"><a class="btn btn--light" href="{{ route('rooms') }}">Check Availability</a><a class="btn btn--outline-light" href="https://wa.me/{{ $c['whatsapp'] }}">WhatsApp us</a></div>
</div>
<div class="cta-split__media"><img src="{{ \App\Support\Media::slot('home.cta')['src'] }}" alt="{{ \App\Support\Media::slot('home.cta')['alt'] }}" loading="lazy"></div>
</section>
@endsection
