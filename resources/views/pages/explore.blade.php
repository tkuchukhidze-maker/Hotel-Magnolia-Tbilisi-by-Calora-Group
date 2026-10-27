@extends('layouts.app')

@section('title', 'Location & What\'s Nearby in Old Tbilisi | Hotel Magnolia Tbilisi')
@section('description', 'Hotel Magnolia is at 5 Kirile Ukleba St, Old Tbilisi: 950 m to Freedom Square, 750 m to the metro, 2.2 km to Narikala Fortress, 15 km to the airport. Free parking.')
@section('crumb', 'Explore Tbilisi')
@section('og_image', asset('assets/img/rooftop-domes.webp'))

@php
$see = [['Mtatsminda Pantheon', '550 m'], ['9 April Park', '700 m'], ['Freedom Square', '950 m'], ['Bridge of Peace', '1.6 km'], ['Narikala Fortress', '2.2 km'], ['National Botanical Garden', '2.5 km']];
$eat = [['Fox Bay', '500 m'], ['Chacha Time', '750 m'], ['About Sololaki', '900 m']];
$move = [['Freedom Square metro', '750 m'], ['Rustaveli metro', '1.2 km'], ['Tbilisi Airport', '15 km']];
@endphp

@section('content')
<section class="page-banner">
<img src="{{ asset('assets/img/rooftop-domes.webp') }}" alt="Old Tbilisi rooftops around the hotel's terrace with glass domes" fetchpriority="high">
<div class="container">
<p class="eyebrow">Explore Tbilisi</p>
<h1 class="display h1-page">A city worth <em>getting lost in.</em></h1>
</div>
</section>

<section class="section">
<div class="container split" style="align-items:flex-start">
<div class="stack">
<p style="margin:0;font-family:var(--font-display);font-size:30px;line-height:1.3">We're in the old town, on a quiet street below Mount Mtatsminda. Leave your car in our free parking and walk.</p>
<p class="lead" style="font-size:17px">Freedom Square is ten minutes away. The old fortress is an easy afternoon. Ask us at the front desk for the places we'd send a friend.</p>
<p class="lead" style="font-size:17px"><strong>Address:</strong> {{ config('hotel.contact.address') }}, Old Town, Tbilisi · <a href="{{ config('hotel.contact.google_maps') }}" style="font-weight:700">Open in Google Maps</a></p>
@include('partials.map')
</div>
<div class="stack" style="gap:36px;flex-grow:1.2">
<div>
<h2 class="list-title">See</h2>
@foreach ($see as [$place, $dist])
<div class="list-row list-row--lg"><span>{{ $place }}</span><span>{{ $dist }}</span></div>
@endforeach
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:32px">
<div>
<h2 class="list-title">Eat &amp; drink</h2>
@foreach ($eat as [$place, $dist])
<div class="list-row"><span>{{ $place }}</span><span>{{ $dist }}</span></div>
@endforeach
</div>
<div>
<h2 class="list-title">Get around</h2>
@foreach ($move as [$place, $dist])
<div class="list-row"><span>{{ $place }}</span><span>{{ $dist }}</span></div>
@endforeach
</div>
</div>
</div>
</div>
</section>

<section class="section section--cocoa" aria-labelledby="arrive-h">
<div class="container stack" style="gap:32px">
<h2 id="arrive-h" class="h2">Getting here, <em>made easy.</em></h2>
<div class="cards-dark">
<div><strong>From the airport</strong><span>15 km to our door. [Transfer: on request / price — confirm]</span></div>
<div><strong>By metro</strong><span>Freedom Square station is 750 m away, Rustaveli 1.2 km.</span></div>
<div><strong>By car</strong><span>Free parking for guests. Rare in the old town.</span></div>
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
