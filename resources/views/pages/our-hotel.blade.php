@extends('layouts.app')

@section('title', 'About the Hotel & Photo Gallery | Hotel Magnolia Tbilisi by Calora Group')
@section('description', 'A restored brick house in Old Tbilisi with classic furniture, a rooftop terrace with glass domes facing Mtatsminda, and a 127-year-old magnolia in the courtyard. See the photos.')
@section('crumb', 'Our hotel & gallery')
@section('og_image', \App\Support\Media::slot('our-hotel.banner')['src'])

@php
$gallery = \App\Support\Media::gallery();
$amenities = \App\Support\Services::active('amenity');
$extras = \App\Support\Services::active('extra');
$units = \App\Support\Services::UNITS;
@endphp

@section('content')
<section class="page-banner">
<img src="{{ \App\Support\Media::slot('our-hotel.banner')['src'] }}" alt="{{ \App\Support\Media::slot('our-hotel.banner')['alt'] }}" fetchpriority="high">
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
<img class="arch-sm" src="{{ \App\Support\Media::slot('our-hotel.door')['src'] }}" alt="{{ \App\Support\Media::slot('our-hotel.door')['alt'] }}" loading="lazy" style="flex:1 1 0;min-width:0;height:clamp(320px,40vw,520px);object-fit:cover">
<img src="{{ \App\Support\Media::slot('our-hotel.stair')['src'] }}" alt="{{ \App\Support\Media::slot('our-hotel.stair')['alt'] }}" loading="lazy" style="flex:1 1 0;min-width:0;height:clamp(240px,30vw,380px);object-fit:cover;border-radius:16px">
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
<div><dt>PRICES</dt><dd>From GEL {{ collect(\App\Support\RoomCatalog::all())->min(fn ($r) => $r['p1'][0]) }} a night, taxes included</dd></div>
<div><dt>INCLUDED</dt><dd>Free parking, Wi‑Fi, air conditioning</dd></div>
<div><dt>SPACES</dt><dd>Rooftop terrace, garden, lobby</dd></div>
<div><dt>FRONT DESK</dt><dd>24 hours · {{ config('hotel.contact.phone_display') }}</dd></div>
<div><dt>THE TREE</dt><dd>A 127-year-old magnolia</dd></div>
</dl>
</div>
</section>

<section class="section section--sand" style="padding:72px 0">
<div class="container split">
<img class="arch-sm" src="{{ \App\Support\Media::slot('magnolia')['src'] }}" alt="{{ \App\Support\Media::slot('magnolia')['alt'] }}" loading="lazy" style="flex:0 1 320px;height:360px;object-fit:cover;width:100%">
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
@foreach ($gallery as $photo)
<figure class="figure {{ $photo->wide ? 'figure--wide' : '' }}" data-cat="{{ $photo->category ?: 'spaces' }}"><img src="{{ \App\Support\Media::src($photo->path) }}" alt="{{ $photo->alt }}" loading="lazy" @if (str_contains($photo->path, 'room-twin')) style="object-position:80% 30%" @endif>@if ($photo->caption)<figcaption class="caption">{{ $photo->caption }}</figcaption>@endif</figure>
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
@foreach ($amenities as $a)
<div class="extra"><span class="extra__icon">{!! \App\Support\Services::icon($a->icon) !!}</span><strong>{{ $a->name }}</strong>@if ($a->description)<span>{{ $a->description }}</span>@endif</div>
@endforeach
</div>
@if ($extras)
<div class="stack" style="gap:10px;margin:48px 0 24px">
<p class="eyebrow">Add to your stay</p>
<h3 class="h3" style="font-size:clamp(28px,3vw,40px)">A few things <em>we can arrange.</em></h3>
<p class="lead" style="font-size:16px;max-width:560px">Tick them when you book, or ask us any time before you arrive.</p>
</div>
<div class="extras">
@foreach ($extras as $e)
<div class="extra"><span class="extra__icon">{!! \App\Support\Services::icon($e->icon) !!}</span><strong>{{ $e->name }}</strong>@if ($e->description)<span>{{ $e->description }}</span>@endif<span class="extra__price">GEL {{ $e->price }} {{ $units[$e->price_unit] ?? '' }}</span></div>
@endforeach
</div>
@endif
</div>
</section>

<section class="section">
<div class="container cta-band">
<h2 class="h2">Like what you see? <em>Come on in.</em></h2>
<a class="btn btn--primary" href="{{ route('rooms') }}">Choose your room</a>
</div>
</section>
@endsection
