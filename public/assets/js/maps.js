/* Hotel Magnolia — Google Maps: branded hotel map + address autocomplete.
   Settings come from Laravel (window.MAGNOLIA_MAPS, see partials/maps-config.blade.php).
   Loaded only when GOOGLE_MAPS_API_KEY is set. */
(function () {
  'use strict';

  var CFG = window.MAGNOLIA_MAPS;
  if (!CFG || !CFG.key) return;

  // Official Google Maps dynamic library loader (adds google.maps.importLibrary).
  /* eslint-disable */
  (g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({ key: CFG.key, v: 'weekly', language: 'en', region: 'GE' });
  /* eslint-enable */

  // Brand palette: Warm Ivory land, Dusty Blue water, Sand roads, Cocoa labels.
  var BRAND_STYLE = [
    { elementType: 'geometry', stylers: [{ color: '#F5EBDD' }] },
    { elementType: 'labels.text.fill', stylers: [{ color: '#5A3A2F' }] },
    { elementType: 'labels.text.stroke', stylers: [{ color: '#F5EBDD' }] },
    { featureType: 'administrative', elementType: 'geometry.stroke', stylers: [{ color: '#C9B497' }] },
    { featureType: 'poi', elementType: 'labels.icon', stylers: [{ saturation: -60 }, { lightness: 10 }] },
    { featureType: 'poi', elementType: 'geometry', stylers: [{ color: '#EFE2CF' }] },
    { featureType: 'poi.park', elementType: 'geometry', stylers: [{ color: '#DCE3C9' }] },
    { featureType: 'poi.business', stylers: [{ visibility: 'off' }] },
    { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#FFFDF9' }] },
    { featureType: 'road', elementType: 'geometry.stroke', stylers: [{ color: '#E6D8C3' }] },
    { featureType: 'road.arterial', elementType: 'geometry', stylers: [{ color: '#FBF3E6' }] },
    { featureType: 'road.highway', elementType: 'geometry', stylers: [{ color: '#E9D3B4' }] },
    { featureType: 'road.highway', elementType: 'geometry.stroke', stylers: [{ color: '#D9BE98' }] },
    { featureType: 'transit.line', elementType: 'geometry', stylers: [{ color: '#C9B497' }] },
    { featureType: 'transit.station', elementType: 'labels.icon', stylers: [{ saturation: -40 }] },
    { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#AFC2CE' }] },
    { featureType: 'water', elementType: 'labels.text.fill', stylers: [{ color: '#42271F' }] }
  ];

  // Terracotta pin with the line-drawn magnolia.
  var PIN = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(
    '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="60" viewBox="0 0 48 60">' +
    '<path d="M24 58s20-18.6 20-34A20 20 0 0 0 4 24c0 15.4 20 34 20 34z" fill="#A94729" stroke="#FFFDF9" stroke-width="2"/>' +
    '<g transform="translate(9 7)" fill="none" stroke="#F5EBDD" stroke-width="1.4" stroke-linejoin="round">' +
    '<path d="M15 26 C10.5 21 10.5 11 15 4 C19.5 11 19.5 21 15 26Z"/>' +
    '<path d="M15 26 C9 25 4.5 19.5 3.8 11 C9 12 12.8 16.5 15 22.5"/>' +
    '<path d="M15 26 C21 25 25.5 19.5 26.2 11 C21 12 17.2 16.5 15 22.5"/></g></svg>'
  );

  function esc(s) { return String(s || '').replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }

  var positionPromise = null;
  function hotelPosition() {
    if (positionPromise) return positionPromise;
    var lat = parseFloat(CFG.lat), lng = parseFloat(CFG.lng);
    if (!isNaN(lat) && !isNaN(lng)) {
      positionPromise = Promise.resolve({ lat: lat, lng: lng });
    } else {
      positionPromise = google.maps.importLibrary('geocoding').then(function (lib) {
        return new lib.Geocoder().geocode({ address: CFG.address }).then(function (res) {
          var loc = res.results[0].geometry.location;
          return { lat: loc.lat(), lng: loc.lng() };
        });
      });
    }
    return positionPromise;
  }

  /* ---------- Branded map ---------- */
  function mountMap(el) {
    var mapCanvas = null;
    var fallback = el.querySelector('iframe');
    Promise.all([google.maps.importLibrary('maps'), google.maps.importLibrary('marker'), hotelPosition()])
      .then(function (r) {
        var maps = r[0], marker = r[1], pos = r[2];
        // Leave the embedded map visible until Google's interactive tiles have loaded.
        // This also preserves a working map when billing, key restrictions, or network fail.
        el.style.position = 'relative';
        mapCanvas = document.createElement('div');
        mapCanvas.style.cssText = 'position:absolute;inset:0;opacity:0;pointer-events:none;transition:opacity .2s';
        mapCanvas.setAttribute('role', 'region');
        mapCanvas.setAttribute('aria-label', 'Interactive map showing ' + CFG.name);
        el.appendChild(mapCanvas);
        var map = new maps.Map(mapCanvas, {
          center: pos,
          zoom: 16,
          styles: BRAND_STYLE,
          mapTypeControl: false,
          streetViewControl: false,
          fullscreenControl: true,
          clickableIcons: false,
          gestureHandling: 'cooperative'
        });
        var pin = new marker.Marker({
          map: map,
          position: pos,
          title: CFG.name,
          icon: { url: PIN, scaledSize: new google.maps.Size(48, 60), anchor: new google.maps.Point(24, 58) }
        });
        var info = new maps.InfoWindow({
          content:
            '<div style="font-family:Manrope,system-ui,sans-serif;color:#42271F;max-width:240px;line-height:1.45">' +
              '<strong style="font-family:\'Cormorant Garamond\',Georgia,serif;font-size:20px;font-weight:600">' + esc(CFG.shortName) + '</strong><br>' +
              '<span style="font-size:13px">' + esc(CFG.address) + ', Old Town, Tbilisi</span><br>' +
              '<a href="tel:' + esc(CFG.phoneTel) + '" style="color:#A94729;font-size:13px;font-weight:700">' + esc(CFG.phone) + '</a><br>' +
              '<a href="' + esc(CFG.directions) + '" target="_blank" rel="noopener" style="display:inline-block;margin-top:8px;background:#A94729;color:#fff;text-decoration:none;font-weight:700;font-size:13px;padding:8px 14px;border-radius:999px">Get directions</a>' +
            '</div>'
        });
        google.maps.event.addListenerOnce(map, 'tilesloaded', function () {
          mapCanvas.style.opacity = '1';
          mapCanvas.style.pointerEvents = 'auto';
          if (fallback) fallback.style.display = 'none';
          el.setAttribute('data-map-ready', 'true');
        });
        pin.addListener('click', function () { info.open({ map: map, anchor: pin }); });
        info.open({ map: map, anchor: pin });
      })
      .catch(function (err) {
        console.warn('Hotel map could not load:', err);
        if (mapCanvas) mapCanvas.remove();
        // Keep the plain embed that the page already contains.
      });
  }

  document.querySelectorAll('[data-hotel-map]').forEach(function (el) {
    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (entries) {
        if (entries[0].isIntersecting) { io.disconnect(); mountMap(el); }
      }, { rootMargin: '300px' });
      io.observe(el);
    } else {
      mountMap(el);
    }
  });

  /* ---------- Address autocomplete (Places API New) ---------- */
  function component(components, type, short) {
    var c = (components || []).find(function (x) { return x.types.indexOf(type) > -1; });
    return c ? (short ? c.shortText : c.longText) : '';
  }

  /**
   * Mount Google's address search into `host`. `onSelect` receives
   * { address, city, postal_code, country, country_code }.
   */
  window.MagnoliaMaps = {
    enabled: true,
    mountAddressAutocomplete: function (host, onSelect) {
      return google.maps.importLibrary('places').then(function (places) {
        // The new widget no longer supports the legacy `types` option.
        // Allow addresses worldwide: international guests enter their home address.
        var el = new places.PlaceAutocompleteElement({
          description: 'Home address',
          placeholder: 'Start typing your address'
        });
        el.id = 'address-autocomplete';
        el.setAttribute('aria-label', 'Home address');
        host.innerHTML = '';
        host.appendChild(el);

        function handle(place) {
          return place.fetchFields({ fields: ['formattedAddress', 'addressComponents'] }).then(function () {
            var ac = place.addressComponents;
            var street = [component(ac, 'street_number'), component(ac, 'route')].filter(Boolean).join(' ');
            onSelect({
              address: place.formattedAddress || street,
              city: component(ac, 'locality') || component(ac, 'postal_town') || component(ac, 'administrative_area_level_1'),
              postal_code: component(ac, 'postal_code'),
              country: component(ac, 'country'),
              country_code: component(ac, 'country', true)
            });
          });
        }

        // Current event name, and the older one for safety.
        el.addEventListener('gmp-select', function (e) { if (e.placePrediction) handle(e.placePrediction.toPlace()); });
        el.addEventListener('gmp-placeselect', function (e) { if (e.place) handle(e.place); });
        return el;
      });
    }
  };
})();
