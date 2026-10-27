@php
    $links = [
        ['rooms', 'Rooms', route('rooms')],
        ...($promo['active'] ? [['offer', '−'.$promo['discount'].'% Offer', route('offer')]] : []),
        ['our-hotel', 'Our Hotel', route('our-hotel')],
        ['gallery', 'Gallery', route('our-hotel').'#gallery'],
        ['explore', 'Explore Tbilisi', route('explore')],
        ['contact', 'Contact', route('contact')],
    ];
@endphp
<header class="site-header {{ $overlay ? 'site-header--overlay' : '' }}">
@include('partials.promo-bar')
<div class="container site-header__inner">
<a class="logo" href="{{ route('home') }}" aria-label="{{ config('hotel.name') }} — home">
<svg class="logo__mark" stroke-width="1.3"><use href="#mg-mark"/></svg>
<span class="logo__text"><span class="logo__name">MAGNOLIA</span><span class="logo__sub">HOTEL · TBILISI</span><span class="logo__by">by Calora Group</span></span>
</a>
<button class="nav-toggle" type="button" aria-controls="site-nav" aria-expanded="false" data-nav-open aria-label="Open menu"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 8h16M4 16h16"/></svg></button>
<nav class="nav" id="site-nav" aria-label="Main">
<span class="nav__decor" aria-hidden="true">
<svg class="nav__branch" stroke-width=".9"><use href="#mg-branch"/></svg>
<svg class="nav__flower" stroke-width=".5"><use href="#mg-flower"/></svg>
<svg class="nav__petal" stroke-width="1"><use href="#mg-mark"/></svg>
</span>
<button class="nav-toggle nav-close" type="button" data-nav-close aria-label="Close menu"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
@foreach ($links as [$name, $label, $href])
<a class="nav__link{{ $name === 'offer' ? ' nav__link--offer' : '' }}" href="{{ $href }}" @if ($name !== 'gallery' && request()->routeIs($name)) aria-current="page" @endif><svg class="nav__bud" stroke-width="1.4" aria-hidden="true"><use href="#mg-mark"/></svg>{{ $label }}</a>
@endforeach
<span class="nav__lang">EN</span>
<p class="nav__story"><span>Since 1899</span>A 127-year-old magnolia grows in our courtyard. Come and sit under it.</p>
<a class="btn btn--primary nav__cta" href="{{ request()->routeIs('rooms') ? '#search' : route('rooms') }}">Book Your Stay</a>
</nav>
</div>
</header>
