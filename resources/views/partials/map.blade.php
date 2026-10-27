{{-- Google Maps Quick Builder Locator Plus; default embedded map is the safe fallback. --}}
@php($locator = config('hotel.google.locator_plus_iframe'))
<div class="map-ph hotel-map" @if (config('hotel.google.maps_key') && ! $locator) data-hotel-map @endif style="padding:0;overflow:hidden;min-height:340px">
@if ($locator)
<iframe src="{{ $locator }}" title="Hotel Magnolia Tbilisi — interactive location finder" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen style="border:0;width:100%;height:340px;display:block"></iframe>
@else
<iframe src="{{ config('hotel.contact.map_embed') }}" title="Map: Hotel Magnolia Tbilisi by Calora Group, {{ config('hotel.contact.address') }}, Tbilisi" loading="lazy" referrerpolicy="no-referrer-when-downgrade" style="border:0;width:100%;height:340px;display:block"></iframe>
@endif
</div>
<p style="margin:10px 0 0;font-size:14px">
<a href="{{ config('hotel.contact.google_maps') }}" target="_blank" rel="noopener" style="font-weight:700">View hotel on Google Maps →</a>
<span aria-hidden="true"> · </span>
<a href="{{ config('hotel.google.directions') }}" target="_blank" rel="noopener" style="font-weight:700">Get directions →</a>
</p>
