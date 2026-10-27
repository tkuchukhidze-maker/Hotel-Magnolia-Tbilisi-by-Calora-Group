@php
    $links = [
        ['rooms', 'Rooms', route('rooms')],
        ['our-hotel', 'Our Hotel', route('our-hotel')],
        ['gallery', 'Gallery', route('our-hotel').'#gallery'],
        ['explore', 'Explore Tbilisi', route('explore')],
        ['contact', 'Contact', route('contact')],
    ];
@endphp
<header class="site-header {{ $overlay ? 'site-header--overlay' : '' }}">
<div class="container site-header__inner">
<a class="logo" href="{{ route('home') }}" aria-label="{{ config('hotel.name') }} — home">
<svg class="logo__mark" stroke-width="1.3"><use href="#mg-mark"/></svg>
<span class="logo__text"><span class="logo__name">MAGNOLIA</span><span class="logo__sub">HOTEL · TBILISI</span></span>
</a>
<button class="nav-toggle" type="button" aria-controls="site-nav" aria-expanded="false" data-nav-open aria-label="Open menu"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 8h16M4 16h16"/></svg></button>
<nav class="nav" id="site-nav" aria-label="Main">
<button class="nav-toggle nav-close" type="button" data-nav-close aria-label="Close menu"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
@foreach ($links as [$name, $label, $href])
<a href="{{ $href }}" @if ($name !== 'gallery' && request()->routeIs($name)) aria-current="page" @endif>{{ $label }}</a>
@endforeach
<span class="nav__lang">EN</span>
<a class="btn btn--primary nav__cta" href="{{ request()->routeIs('rooms') ? '#search' : route('rooms') }}">Book Your Stay</a>
</nav>
</div>
</header>
