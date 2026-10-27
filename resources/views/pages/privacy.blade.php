@extends('layouts.app')
@php($c = config('hotel.contact'))

@section('title', 'Privacy Policy | Magnolia Hotel Tbilisi')
@section('description', 'How Magnolia Hotel Tbilisi uses the personal details you give us when you book or contact us.')
@section('crumb', 'Privacy policy')

@section('content')
<section class="container" style="padding-top:72px;padding-bottom:96px;max-width:820px">
<div class="stack" style="gap:20px">
<p class="eyebrow">Privacy policy</p>
<h1 class="display h1-page" style="font-size:clamp(44px,6vw,72px)">Your details, <em>kept simple.</em></h1>
<p class="small">[Draft — have it reviewed by a lawyer before launch.] Last updated: [date]</p>

<h2 class="h3" style="font-size:30px">What we collect</h2>
<p class="lead" style="font-size:17px">When you book, we ask for your first and last name, email, phone or WhatsApp number, country, arrival time and anything you choose to tell us in the notes, together with your dates, room, rate and number of guests.</p>

<h2 class="h3" style="font-size:30px">Why we use it</h2>
<p class="lead" style="font-size:17px">Only to handle your stay: to confirm and manage your booking, contact you about it, prepare your room and meet the registration rules for hotels in Georgia. We don't sell your details or use them for advertising.</p>

<h2 class="h3" style="font-size:30px">Who sees it</h2>
<p class="lead" style="font-size:17px">Our hotel staff, and the services we use to run the website and send email. [List providers: hosting, email, booking engine/PMS.]</p>

<h2 class="h3" style="font-size:30px">How long we keep it</h2>
<p class="lead" style="font-size:17px">[For as long as needed for your booking and as required by law — e.g. N years.]</p>

<h2 class="h3" style="font-size:30px">Your choices</h2>
<p class="lead" style="font-size:17px">You can ask us to see, correct or delete your details at any time. Write to {{ $c['email_display'] }} or message us on WhatsApp at <a href="https://wa.me/{{ $c['whatsapp'] }}">{{ $c['phone_display'] }}</a>.</p>

<p class="lead" style="font-size:17px">{{ config('hotel.name') }} · {{ $c['address'] }}, Tbilisi, Georgia · [Legal entity name and ID]</p>
</div>
</section>
@endsection
