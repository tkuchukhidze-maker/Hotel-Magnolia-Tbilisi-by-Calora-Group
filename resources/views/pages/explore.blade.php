@extends('layouts.app')
@php($c = config('hotel.contact'))
@php($n = config('hotel.nearby'))
@php($arrival = config('hotel.arrival'))
@php($F = \App\Support\HotelFacts::class)

@section('title', 'Location & What\'s Nearby in Old Tbilisi | Hotel Magnolia Tbilisi by Calora Group')
@section('description', 'Hotel Magnolia Tbilisi by Calora Group is at 5 Kirile Ukleba St, Old Tbilisi: Freedom Square 950 m (about 12 minutes on foot), metro 750 m, Narikala Fortress 2.2 km, airport 15 km. Free parking for guests.')
@section('crumb', 'Explore Tbilisi')
@section('og_image', asset('assets/img/rooftop-domes.webp'))

@section('content')
<section class="page-banner">
<img src="{{ asset('assets/img/rooftop-domes.webp') }}" alt="Old Tbilisi rooftops around the hotel's terrace with glass domes" fetchpriority="high">
<div class="container">
<p class="eyebrow">Explore Tbilisi</p>
<h1 class="display h1-page">A city worth <em>getting lost in.</em></h1>
</div>
</section>

<section class="section" aria-labelledby="walking-distances">
<div class="container split" style="align-items:flex-start">
<div class="stack">
<p style="margin:0;font-family:var(--font-display);font-size:30px;line-height:1.3">We're in the old town, on a quiet street below Mount Mtatsminda. Leave your car in our free parking and walk.</p>
<p class="lead" style="font-size:17px">Freedom Square is about {{ $F::walkMinutes(950) }} minutes away on foot. The old fortress is an easy afternoon. Ask us at the front desk for the places we'd send a friend.</p>
<p class="lead" style="font-size:17px"><strong>Address:</strong> {{ $c['address'] }}, {{ $c['neighbourhood'] }}, Tbilisi · <a href="{{ $c['google_maps'] }}" style="font-weight:700">Open in Google Maps</a></p>
@include('partials.map')
</div>
<div class="stack" style="gap:36px;flex-grow:1.2">
<h2 id="walking-distances" class="h3" style="margin:0">Walking distances <em>from the hotel</em></h2>
<div>
<h3 class="list-title">See</h3>
@foreach ($n['see'] as [$place, $m])
<div class="list-row list-row--lg"><span>{{ $place }}</span><span>{{ $F::distance($m) }}@if ($F::isWalkable($m)) · about {{ $F::walkMinutes($m) }} min walk @endif</span></div>
@endforeach
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:32px">
<div>
<h3 class="list-title">Eat &amp; drink</h3>
@foreach ($n['eat'] as [$place, $m])
<div class="list-row"><span>{{ $place }}</span><span>{{ $F::distance($m) }} · {{ $F::walkMinutes($m) }} min</span></div>
@endforeach
</div>
<div>
<h3 class="list-title">Get around</h3>
@foreach ($n['transport'] as [$place, $m])
<div class="list-row"><span>{{ $place }}</span><span>{{ $F::distance($m) }} · {{ $F::walkMinutes($m) }} min</span></div>
@endforeach
<div class="list-row"><span>Tbilisi Airport</span><span>{{ $F::distance($arrival['airport_m']) }}</span></div>
</div>
</div>
<p class="small" style="margin:0">Walking times are approximate. Some streets in the old town are steep.</p>
</div>
</div>
</section>

<section class="section section--cocoa" aria-labelledby="getting-here">
<div class="container stack" style="gap:32px">
<h2 id="getting-here" class="h2">Getting here, <em>made easy.</em></h2>
<div class="cards-dark">
<div id="by-car">
<h3 style="margin:0"><strong>By car &amp; free parking</strong></h3>
<span>{{ $arrival['parking']['summary'] }}</span>
@foreach (['where', 'spaces', 'reserve'] as $k)
@if ($F::filled($arrival['parking'][$k] ?? null))
<span>{{ $arrival['parking'][$k] }}</span>
@endif
@endforeach
<span><a href="{{ config('hotel.google.directions') }}" style="color:inherit;font-weight:700">Get directions →</a></span>
</div>
<div id="from-the-airport">
<h3 style="margin:0"><strong>From the airport</strong></h3>
<span>Tbilisi International Airport is {{ $F::distance($arrival['airport_m']) }} from our door.</span>
@if ($F::filled($arrival['transfer']))
<span>{{ $arrival['transfer'] }}</span>
@else
<span>Need a ride? <a href="https://wa.me/{{ $c['whatsapp'] }}" style="color:inherit;font-weight:700">Message us on WhatsApp</a> before you fly.</span>
@endif
</div>
<div id="by-metro">
<h3 style="margin:0"><strong>By metro</strong></h3>
<span>Freedom Square station is 750 m away (about {{ $F::walkMinutes(750) }} minutes on foot), Rustaveli 1.2 km.</span>
</div>
</div>
</div>
</section>

<section class="section">
<div class="container cta-band">
<h2 class="h2">Your Tbilisi stay <em>starts here.</em></h2>
<a class="btn btn--primary" href="{{ route('rooms') }}">Choose your room</a>
</div>
</section>
@endsection
