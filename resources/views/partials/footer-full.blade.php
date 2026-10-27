@php($c = config('hotel.contact'))
<footer class="site-footer">
<svg class="footer-pattern" aria-hidden="true"><defs><pattern id="mg-petals" width="44" height="36" patternUnits="userSpaceOnUse"><path d="M22 6 C17 12 17 22 22 30 C27 22 27 12 22 6Z" fill="none" stroke="#B99559"/><circle cx="0" cy="18" r="1.4" fill="#B99559"/><circle cx="44" cy="18" r="1.4" fill="#B99559"/></pattern></defs><rect width="100%" height="100%" fill="url(#mg-petals)"/></svg>
<div class="container">
<div class="footer-grid">
<div class="stack" style="gap:14px;max-width:280px">
<span class="logo"><svg class="logo__mark" stroke-width="1.3" style="color:var(--gold)" aria-hidden="true"><use href="#mg-mark"/></svg><span class="logo__text"><span class="logo__name" style="color:var(--ivory)">MAGNOLIA</span><span class="logo__sub" style="color:var(--gold)">HOTEL · TBILISI</span><span class="logo__by" style="color:#E8DCCB">by Calora Group</span></span></span>
<span style="font-size:14px">A small hotel with a lot of character, and a magnolia that's been here since 1899.</span>
</div>
<nav aria-label="Footer">
<a href="{{ route('rooms') }}">Rooms &amp; booking</a>
<a href="{{ route('our-hotel') }}">Our Hotel &amp; Gallery</a>
<a href="{{ route('explore') }}">Explore Tbilisi</a>
<a href="{{ route('contact') }}">Contact &amp; FAQ</a>
</nav>
<address><a href="{{ $c['google_maps'] }}">{{ $c['address'] }}<br>Old Town, Tbilisi, Georgia</a><br><a href="tel:{{ $c['phone_tel'] }}">{{ $c['phone_display'] }}</a><br><a href="mailto:{{ $c['email'] }}">{{ $c['email_display'] }}</a></address>
<div class="footer-col">
<div class="social">
@if ($c['instagram'])
<a href="{{ $c['instagram'] }}" aria-label="Instagram" rel="me"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/></svg></a>
@endif
@if ($c['facebook'])
<a href="{{ $c['facebook'] }}" aria-label="Facebook" rel="me"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M14 8h3V4h-3a4 4 0 0 0-4 4v3H7v4h3v6h4v-6h3l1-4h-4V8z"/></svg></a>
@endif
<a href="{{ $c['google_maps'] }}" aria-label="Google Maps"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-12a7 7 0 0 1 14 0c0 5.8-7 12-7 12z"/><circle cx="12" cy="9" r="2.5"/></svg></a>
<a href="https://wa.me/{{ $c['whatsapp'] }}" aria-label="WhatsApp"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true"><path d="M4 20l1.3-3.9A8 8 0 1 1 8 19z"/></svg></a>
</div>
<span>EN · ქარ · RU</span>
</div>
</div>
<div class="footer-bottom"><span>© {{ date('Y') }} {{ config('hotel.name') }}</span><a href="{{ route('privacy') }}">Privacy policy</a></div>
</div>
</footer>
