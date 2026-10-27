@php($c = config('hotel.contact'))
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', config('hotel.name'))</title>
<meta name="description" content="@yield('description', 'A small hotel with a lot of character in Old Tbilisi.')">
<meta name="robots" content="{{ app()->environment('production') ? 'index, follow, max-image-preview:large' : 'noindex, nofollow' }}">
<link rel="canonical" href="{{ url()->current() }}">
<meta property="og:type" content="{{ request()->routeIs('home') ? 'website' : 'article' }}">
<meta property="og:site_name" content="{{ config('hotel.name') }}">
<meta property="og:locale" content="en_US">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="@yield('title', config('hotel.name'))">
<meta property="og:description" content="@yield('description', 'A small hotel with a lot of character in Old Tbilisi.')">
<meta property="og:image" content="@yield('og_image', asset('assets/img/hero-facade.webp'))">
<meta name="twitter:card" content="summary_large_image">
<meta name="geo.region" content="GE-TB">
<meta name="geo.placename" content="Tbilisi">
<meta name="theme-color" content="#42271F">
@if (! request()->routeIs('home'))
{!! \App\Support\StructuredData::render(\App\Support\StructuredData::breadcrumb(array_filter([
    'Home' => route('home'),
    trim($__env->yieldContent('crumb')) => url()->current(),
]))) !!}
@endif
<link rel="icon" href="{{ asset('assets/img/favicon.svg') }}" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}?v={{ filemtime(public_path('assets/css/styles.css')) }}">
@stack('head')
</head>
<body>
@include('partials.sprite')
<a class="skip-link" href="#main">Skip to content</a>

@include('partials.header', ['overlay' => $overlay ?? false])

<main id="main">
@yield('content')
</main>

@if ($fullFooter ?? false)
    @include('partials.footer-full')
@else
    @include('partials.footer')
@endif

@hasSection('mobile-bar')
    <div class="mobile-book" role="region" aria-label="Quick booking">@yield('mobile-bar')</div>
@else
    <div class="mobile-book" role="region" aria-label="Quick booking">
        <span class="mobile-book__price">from <s>GEL 142</s><strong>GEL 106 <em>−{{ config('hotel.offer.discount') }}%</em></strong></span>
        <a class="btn btn--primary" href="{{ route('rooms') }}">Book Now</a>
    </div>
@endif

<script src="{{ asset('assets/js/main.js') }}?v={{ filemtime(public_path('assets/js/main.js')) }}" defer></script>
@stack('scripts')
</body>
</html>
