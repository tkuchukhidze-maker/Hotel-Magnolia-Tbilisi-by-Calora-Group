@extends('layouts.app')
@php($c = config('hotel.contact'))
@php($p = config('hotel.policies'))

@section('title', 'Contact & FAQ — Magnolia Hotel Tbilisi')
@section('description', 'Reach Magnolia Hotel by WhatsApp, phone or email. Check-in times, cancellation, payment, parking and answers to common questions.')

@php
$faq = [
    ['Do you serve breakfast?', 'Yes, a buffet breakfast every morning. Add it when you book, from GEL 11 per room per night.'],
    ['Can I park at the hotel?', 'Yes. Parking is free for every guest.'],
    ['Do I need to pay in advance?', 'No. You pay at the hotel. Cancellation: '.strtolower($p['cancellation']).'.'],
    ['Is booking here cheaper than on Booking.com?', "It's never more expensive. If you find a lower price for the same room and dates, send it to us and we'll match it."],
    ['Do all rooms have a private bathroom?', 'Yes, every room has its own bathroom, air conditioning, a flat-screen TV and free Wi-Fi.'],
    ["We're a family of three. Which room?", "The Triple Room: three beds and a balcony. For larger groups, we'll put you in rooms next to each other."],
    ['How far is the airport?', '15 km. [Transfer info — confirm.]'],
];
$facts = [
    'Check-in' => $p['check_in'], 'Check-out' => $p['check_out'], 'Cancellation' => $p['cancellation'],
    'Payment' => $p['payment'], 'Breakfast' => $p['breakfast'], 'Parking' => $p['parking'],
    'Children' => $p['children'], 'Pets' => $p['pets'],
];
@endphp

@push('head')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => collect($faq)->map(fn ($q) => ['@type' => 'Question', 'name' => $q[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q[1]]])->all(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<section class="container decor-host" style="padding-top:72px;padding-bottom:40px">
<svg class="decor decor--hide-sm" width="380" height="316" stroke-width=".4" style="right:-30px;top:10px;opacity:.18;color:var(--terracotta)" aria-hidden="true"><use href="#mg-flower"/></svg>
<div class="stack" style="gap:14px">
<p class="eyebrow">Contact &amp; FAQ</p>
<h1 class="display h1-page">Rather talk <em>to a person?</em></h1>
<p class="lead" style="max-width:560px">You'll reach the hotel itself, not a call centre. Someone's at the front desk day and night.</p>
</div>
</section>

<section class="container" aria-label="Ways to reach us" style="padding-bottom:72px">
<div class="contact-cards">
<a class="contact-card contact-card--accent" href="https://wa.me/{{ $c['whatsapp'] }}"><span class="eyebrow">Fastest</span><strong>WhatsApp us</strong><span>{{ $c['phone_display'] }} →</span></a>
<a class="contact-card" href="tel:{{ $c['phone_tel'] }}"><span class="eyebrow">24 hours</span><strong>Call the front desk</strong><span>{{ $c['phone_display'] }}</span></a>
<a class="contact-card" href="mailto:{{ $c['email'] }}"><span class="eyebrow">Email</span><strong>Write to us</strong><span>{{ $c['email_display'] }}</span></a>
<div class="contact-card"><span class="eyebrow">Address</span><strong>Find us</strong><address>{{ $c['address'] }}, Old Town, Tbilisi</address></div>
</div>
<div class="map-ph" style="margin-top:16px">[Google Map with directions to Magnolia]</div>
</section>

<section class="section section--sand" aria-labelledby="know-h">
<div class="container" style="display:flex;flex-wrap:wrap;gap:40px">
<h2 id="know-h" class="h3" style="flex:1 1 260px">Good to know <em>before you come.</em></h2>
<dl class="facts" style="flex:3 1 600px">
@foreach ($facts as $label => $value)
<div><dt>{{ strtoupper($label) }}</dt><dd>{{ $value }}</dd></div>
@endforeach
</dl>
</div>
</section>

<section class="section" aria-labelledby="faq-h">
<div class="container split" style="align-items:flex-start">
<div class="stack" style="flex:1 1 300px;gap:16px">
<p class="eyebrow">FAQ</p>
<h2 id="faq-h" class="h3">Questions guests ask.</h2>
<p class="lead" style="font-size:16px">Didn't find yours? <a href="https://wa.me/{{ $c['whatsapp'] }}" style="font-weight:700">Ask us on WhatsApp</a>.</p>
</div>
<div class="faq" style="flex:2 1 560px">
@foreach ($faq as $i => [$q, $a])
<details @if ($i === 0) open @endif><summary>{{ $q }}</summary><p>{{ $a }}</p></details>
@endforeach
</div>
</div>
</section>

<section style="background:var(--terracotta);color:#fff">
<div class="container cta-band" style="padding-top:80px;padding-bottom:80px">
<h2 class="h2" style="color:#fff">Come on in.</h2>
<a class="btn btn--light" href="{{ route('rooms') }}">Check Availability</a>
</div>
</section>
@endsection

@section('mobile-bar')
<span class="mobile-book__price">Questions?<strong>We answer</strong></span>
<a class="btn btn--primary" href="https://wa.me/{{ $c['whatsapp'] }}">WhatsApp us</a>
@endsection
