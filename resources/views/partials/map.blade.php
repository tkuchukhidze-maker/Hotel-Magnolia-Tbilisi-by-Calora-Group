{{-- Branded Google map when GOOGLE_MAPS_API_KEY is set (maps.js replaces the embed); plain embed otherwise. --}}
<div class="map-ph hotel-map" @if (config('hotel.google.maps_key')) data-hotel-map @endif style="padding:0;overflow:hidden;min-height:340px">
<iframe src="{{ config('hotel.contact.map_embed') }}" title="Map: Hotel Magnolia, {{ config('hotel.contact.address') }}, Tbilisi" loading="lazy" referrerpolicy="no-referrer-when-downgrade" style="border:0;width:100%;height:340px;display:block"></iframe>
</div>
<p style="margin:10px 0 0;font-size:14px"><a href="{{ config('hotel.google.directions') }}" target="_blank" rel="noopener" style="font-weight:700">Get directions →</a></p>
