<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title') · Magnolia Management</title>
<link rel="icon" href="{{ asset('assets/img/favicon.svg') }}" type="image/svg+xml">
<link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}?v={{ filemtime(public_path('assets/css/admin.css')) }}">
@stack('head')
</head>
<body>
<header class="a-top">
<a class="a-brand" href="{{ route('admin.dashboard') }}"><svg width="30" height="26" viewBox="0 0 40 34" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" aria-hidden="true"><path d="M20 30 C14 24 14 12 20 4 C26 12 26 24 20 30Z"/><path d="M20 30 C12 29 6 22 5 12 C12 13 17 19 20 26"/><path d="M20 30 C28 29 34 22 35 12 C28 13 23 19 20 26"/></svg><span><strong>Magnolia</strong><small>Management</small></span></a>
<nav class="a-nav" aria-label="Admin">
<a href="{{ route('admin.dashboard') }}" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>Bookings</a>
<a href="{{ route('admin.pricing.index') }}" @if (request()->routeIs('admin.pricing.*')) aria-current="page" @endif>Prices</a>
<a href="{{ route('admin.media.index') }}" @if (request()->routeIs('admin.media.*')) aria-current="page" @endif>Photos</a>
<a href="{{ route('admin.services.index') }}" @if (request()->routeIs('admin.services.*')) aria-current="page" @endif>Services</a>
<a href="{{ route('home') }}" target="_blank" rel="noopener">View site ↗</a>
</nav>
<form method="post" action="{{ route('admin.logout') }}">@csrf<button class="a-btn a-btn--ghost">Sign out</button></form>
</header>
<main class="a-main">
@if (session('success'))<p class="a-flash a-flash--ok" role="status">{{ session('success') }}</p>@endif
@if ($errors->any())<div class="a-flash a-flash--err" role="alert">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
@yield('content')
</main>
@stack('scripts')
</body>
</html>
