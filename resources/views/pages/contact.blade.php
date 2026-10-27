@extends('layouts.app')
@php($c = config('hotel.contact'))
@php($p = config('hotel.policies'))
@php($faq = config('hotel.faq'))
@php($facts = [
    'Check-in' => $p['check_in'], 'Check-out' => $p['check_out'], 'Cancellation' => $p['cancellation'],
    'Payment' => $p['payment'], 'Breakfast' => $p['breakfast'], 'Parking' => $p['parking'],
    'Children' => $p['children'], 'Pets' => $p['pets'],
])

@section('title', 'Contact, Check-in & FAQ | Hotel Magnolia Tbilisi by Calora Group')
@section('description', 'Call or WhatsApp +995 533 33 44 22, 24 hours. Hotel Magnolia Tbilisi by Calora Group, 5 Kirile Ukleba St, Old Tbilisi. Check-in, cancellation, parking, breakfast and answers to common questions.')
@section('crumb', 'Contact & FAQ')
@section('og_image', asset('assets/img/entrance-door.webp'))

@push('head')
{!! \App\Support\StructuredData::render(\App\Support\StructuredData::hotel()) !!}
{!! \App\Support\StructuredData::render(\App\Support\StructuredData::faq($faq)) !!}
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
<a class="contact-card" href="{{ $c['google_maps'] }}"><span class="eyebrow">Address</span><strong>Find us</strong><address>{{ $c['address'] }}, Old Town, Tbilisi, Georgia</address></a>
</div>
<div style="margin-top:16px">@include('partials.map')</div>
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
<details @if ($i < 2) open @endif><summary><h3 style="font:inherit;margin:0;display:inline">{{ $q }}</h3></summary><p>{{ $a }}</p></details>
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
