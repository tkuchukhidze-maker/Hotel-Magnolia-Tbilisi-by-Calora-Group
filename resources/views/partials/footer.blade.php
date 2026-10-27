@php($c = config('hotel.contact'))
<footer class="site-footer">
<div class="container">
<div class="footer-grid" style="align-items:center;padding:36px 0">
<span class="logo__name" style="color:var(--ivory)">MAGNOLIA</span>
<nav aria-label="Footer" style="flex-direction:row;flex-wrap:wrap;gap:20px">
<a href="{{ route('home') }}">Home</a>
<a href="{{ route('rooms') }}">Rooms</a>
<a href="{{ route('our-hotel') }}">Our Hotel</a>
<a href="{{ route('explore') }}">Explore Tbilisi</a>
<a href="{{ route('contact') }}">Contact &amp; FAQ</a>
</nav>
<address style="font-style:normal"><a href="{{ $c['google_maps'] }}">{{ $c['address'] }}, Tbilisi</a> · <a href="tel:{{ $c['phone_tel'] }}">{{ $c['phone_display'] }}</a> · <a href="https://wa.me/{{ $c['whatsapp'] }}">WhatsApp</a></address>
</div>
<div class="footer-bottom"><span>© {{ date('Y') }} {{ config('hotel.name') }}</span><span>Privacy · Booking &amp; cancellation terms</span></div>
</div>
</footer>
