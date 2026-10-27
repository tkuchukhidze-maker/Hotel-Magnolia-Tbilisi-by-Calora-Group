@extends('layouts.app')

@section('title', 'About the Hotel & Photo Gallery | Hotel Magnolia Tbilisi by Calora Group')
@section('description', 'A restored brick house in Old Tbilisi with classic furniture, a rooftop terrace with glass domes facing Mtatsminda, and a 127-year-old magnolia in the courtyard. See the photos.')
@section('crumb', 'Our hotel & gallery')
@section('og_image', asset('assets/img/lobby.webp'))

@php
$gallery = [
    ['rooftop-view', 'spaces', true, 'Rooftop terrace with glass domes facing Mount Mtatsminda and the TV tower', 'Glass domes on the roof, Mtatsminda straight ahead.'],
    ['bathroom', 'details rooms', false, 'Bathroom with black-and-white patterned tiles', 'Patterned tiles, plenty of hot water.'],
    ['room-deluxe', 'rooms', false, 'Deluxe Room under two skylights', 'Wake up to sky, not street.'],
    ['room-double', 'rooms', false, 'Double Room with a skylight over a writing desk', 'A desk under the skylight.'],
    ['room-twin', 'details rooms', false, 'A gold-framed painting of haystacks above twin beds', 'Real paintings, in real gold frames.'],
    ['welcome-tray', 'details', false, 'Welcome tea and fruit on a small black table', 'Tea and fruit, waiting for you.'],
    ['lobby', 'spaces', false, 'The lobby with chandeliers and red armchairs', 'The lobby, for slow mornings.'],
    ['aerial', 'spaces', true, 'The hotel from above: old brick walls under a white louvred roof', 'Old brick below, a light new roof above.'],
    ['facade-street', 'spaces', true, 'The brick facade with tall wooden windows on a quiet street', 'A quiet street in the old town.'],
];
$extras = [
    ['Buffet breakfast', 'Every morning, from GEL 11 per room.'],
    ['Free parking', 'Rare in the old town. Free for every guest.'],
    ['Free Wi‑Fi', 'In every room and on the terrace.'],
    ['24-hour front desk', "Someone's always here."],
    ['Garden & terrace', 'Green below, the whole city above.'],
    ['Family rooms', 'Triples with a balcony.'],
    ['Air conditioning', 'In every room.'],
    ['Airport transfer', '[On request — confirm]'],
];
@endphp

@section('content')
<section class="page-banner">
<img src="{{ asset('assets/img/lobby.webp') }}" alt="Magnolia's lobby: gold chandeliers, red floral armchairs, blue tiles and a carved wooden door" fetchpriority="high">
<div class="container">
<p class="eyebrow">Our hotel</p>
<h1 class="display h1-page">A little different. <em>A lot more personal.</em></h1>
</div>
</section>

<section class="section">
<div class="container split">
<div class="stack">
<h2 class="h3">An old brick house that likes having guests.</h2>
<p class="lead">We're a small hotel in the historic heart of Tbilisi. Inside you'll find classic furniture, floral fabrics, gold frames and lamps that each have their own idea of light.</p>
<p class="lead">We kept the thick walls, the carved door and the wrought iron, then added a light new roof with glass domes and a terrace. It feels less like checking in and more like visiting someone with good taste and a garden.</p>
<p class="lead">[One or two lines about the building's history — year built, what it was before.]</p>
<svg width="280" height="140" stroke-width="1.1" style="color:var(--terracotta);opacity:.55;max-width:100%" aria-hidden="true"><use href="#mg-branch"/></svg>
</div>
<div style="display:flex;gap:20px;align-items:flex-end">
<img class="arch-sm" src="{{ asset('assets/img/entrance-door.webp') }}" alt="Magnolia's carved wooden front door with wrought-iron grilles" loading="lazy" style="flex:1 1 0;min-width:0;height:clamp(320px,40vw,520px);object-fit:cover">
<img src="{{ asset('assets/img/magnolia-stair.webp') }}" alt="A magnolia tree growing around a black spiral staircase" loading="lazy" style="flex:1 1 0;min-width:0;height:clamp(240px,30vw,380px);object-fit:cover;border-radius:16px">
</div>
</div>
</section>

<section class="container" aria-labelledby="glance-h" style="padding-bottom:72px">
<div style="border-top:1px solid var(--line-2);border-bottom:1px solid var(--line-2);padding:32px 0;display:flex;flex-wrap:wrap;gap:24px 48px">
<h2 id="glance-h" class="h3" style="flex:1 1 220px">Magnolia <em>at a glance</em></h2>
<dl class="facts" style="flex:3 1 560px">
<div><dt>WHERE</dt><dd><a href="{{ config('hotel.contact.google_maps') }}">{{ config('hotel.contact.address') }}</a>, Old Town, Tbilisi</dd></div>
<div><dt>NEARBY</dt><dd>Freedom Square 950 m · metro 750 m</dd></div>
<div><dt>ROOMS</dt><dd>5 types, 15–30 m², 1–3 guests</dd></div>
<div><dt>PRICES</dt><dd>From GEL 106 a night, taxes included</dd></div>
<div><dt>INCLUDED</dt><dd>Free parking, Wi‑Fi, air conditioning</dd></div>
<div><dt>SPACES</dt><dd>Rooftop terrace, garden, lobby</dd></div>
<div><dt>FRONT DESK</dt><dd>24 hours · {{ config('hotel.contact.phone_display') }}</dd></div>
<div><dt>THE TREE</dt><dd>A 127-year-old magnolia</dd></div>
</dl>
</div>
</section>

<section class="section section--sand" style="padding:72px 0">
<div class="container split">
<img class="arch-sm" src="{{ asset('assets/img/magnolia-courtyard.webp') }}" alt="The 127-year-old magnolia tree in the hotel courtyard" loading="lazy" style="flex:0 1 320px;height:360px;object-fit:cover;width:100%">
<div class="stack" style="flex:2 1 480px;gap:16px">
<p class="eyebrow">The courtyard</p>
<h2 class="h3">A 127-year-old magnolia. <em>We're the new ones here.</em></h2>
<p class="lead" style="font-size:17px">It grows between old brick and new glass and reaches past the top floor. Take your coffee down and sit under it for a while.</p>
<p class="since">Since 1899</p>
</div>
</div>
</section>

<section id="gallery" class="section" style="scroll-margin-top:80px">
<div class="container">
<div class="section-head">
<div class="stack" style="gap:14px">
<p class="eyebrow">Gallery</p>
<h2 class="h2">It's all in the <em>details.</em></h2>
</div>
<div class="chips" role="group" aria-label="Gallery category">
@foreach (['all' => 'All', 'rooms' => 'Rooms', 'details' => 'Details', 'spaces' => 'Spaces'] as $key => $label)
<button type="button" class="chip" data-filter="{{ $key }}" aria-pressed="{{ $key === 'all' ? 'true' : 'false' }}">{{ $label }}</button>
@endforeach
</div>
</div>
<div class="gallery">
@foreach ($gallery as [$img, $cat, $wide, $alt, $caption])
<figure class="figure {{ $wide ? 'figure--wide' : '' }}" data-cat="{{ $cat }}"><img src="{{ asset('assets/img/'.$img.'.webp') }}" alt="{{ $alt }}" loading="lazy" @if ($img === 'room-twin') style="object-position:80% 30%" @endif><figcaption class="caption">{{ $caption }}</figcaption></figure>
@endforeach
</div>
</div>
</section>

<section class="section section--sand" aria-labelledby="extras-h">
<div class="container">
<div class="stack" style="gap:14px;margin-bottom:32px">
<p class="eyebrow">The little extras</p>
<h2 id="extras-h" class="h2">Little things, <em>already sorted.</em></h2>
</div>
<div class="extras">
@foreach ($extras as [$title, $text])
<div class="extra"><strong>{{ $title }}</strong><span>{{ $text }}</span></div>
@endforeach
</div>
</div>
</section>

<section class="section">
<div class="container cta-band">
<h2 class="h2">Like what you see? <em>Come on in.</em></h2>
<a class="btn btn--primary" href="{{ route('rooms') }}">Choose your room</a>
</div>
</section>
@endsection
