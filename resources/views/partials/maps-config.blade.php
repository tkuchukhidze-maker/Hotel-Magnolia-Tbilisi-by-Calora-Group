@php($g = config('hotel.google'))
@if (! empty($g['maps_key']))
@php($c = config('hotel.contact'))
<script>
window.MAGNOLIA_MAPS = {!! json_encode([
    'key' => $g['maps_key'],
    'lat' => $g['lat'],
    'lng' => $g['lng'],
    'name' => config('hotel.name'),
    'shortName' => config('hotel.short_name'),
    'address' => $c['address'].', Tbilisi, Georgia',
    'phone' => $c['phone_display'],
    'phoneTel' => $c['phone_tel'],
    'directions' => $g['directions'],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!};
</script>
<script src="{{ asset('assets/js/maps.js') }}?v={{ filemtime(public_path('assets/js/maps.js')) }}" defer></script>
@endif
