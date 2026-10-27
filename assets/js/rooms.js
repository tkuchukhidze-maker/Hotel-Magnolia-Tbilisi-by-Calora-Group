/* Magnolia Hotel — Rooms & booking flow
   Guests and nights filter the rooms; choosing a room opens an inline panel:
   1 rate → 2 guest details → 3 confirmation.
   NOTE: this is the front-end flow only. Connect the "confirm" step to the
   hotel's booking engine / PMS before going live. */
(function () {
  'use strict';

  var ROOMS = [
    { id: 'double', name: 'Double Room', img: 'assets/img/room-double.webp', alt: 'Double Room with a skylight over a writing desk, a sofa and a king bed', cap: 2, size: '18 m²', bed: 'King bed', extra: 'Skylight', blurb: 'Skylight above, king bed below. Simple, quiet and our best value for two.', left: 'Only 7 left', p2: [129, 142], w2: 172, p1: [106, 117], w1: 142 },
    { id: 'deluxe', name: 'Deluxe Room', img: 'assets/img/room-deluxe.webp', alt: 'Deluxe Room with two skylights, a king bed and a red armchair', cap: 2, size: '30 m²', bed: 'King bed', extra: 'Balcony', blurb: 'Our biggest room, with a balcony. Space to unpack properly and stay a while.', left: 'Only 4 left', p2: [152, 166], w2: 202, p1: [129, 142], w1: 172 },
    { id: 'twin', name: 'Double or Twin', img: 'assets/img/room-twin.webp', alt: 'Double or Twin Room with dusty blue walls, two beds and a framed painting', cap: 2, size: '26 m²', bed: '2 single beds', extra: 'Original art', blurb: 'Two beds, blue walls and a painting of Georgian haystacks to wake up to.', left: '', p2: [137, 150], w2: 182, p1: [114, 125], w1: 152 },
    { id: 'triple', name: 'Triple Room', img: '', alt: '', cap: 3, size: '26 m²', bed: '3 single beds', extra: 'Balcony', blurb: 'Three beds and a balcony, for families or friends travelling together.', left: '', p2: [190, 208], w2: 253, p1: [167, 183], w1: 222 },
    { id: 'single', name: 'Single Room', img: '', alt: '', cap: 1, size: '15 m²', bed: 'Single bed', extra: 'Quiet', blurb: 'A small, calm room of your own, close to everything.', left: 'Only 4 left', p2: [106, 117], w2: 142, p1: [106, 117], w1: 142 }
  ];

  var MAX_GUESTS = 3;
  var params = new URLSearchParams(window.location.search);
  var state = {
    adults: clamp(parseInt(params.get('adults'), 10) || 2, 1, 3),
    kids: clamp(parseInt(params.get('children'), 10) || 0, 0, 2),
    nights: 2,
    checkin: params.get('checkin') || '',
    checkout: params.get('checkout') || '',
    openId: params.get('room') || null,
    rate: 'room',
    step: 'choose'
  };
  if (state.adults + state.kids > MAX_GUESTS) state.kids = Math.max(0, MAX_GUESTS - state.adults);
  if (state.checkin && state.checkout) {
    var n = Math.round((new Date(state.checkout) - new Date(state.checkin)) / 86400000);
    if (n > 0) state.nights = clamp(n, 1, 30);
  }

  var list = document.getElementById('room-list');
  var summaryEl = document.getElementById('search-summary');
  var mobilePrice = document.querySelector('[data-mobile-price]');
  if (!list) return;

  function clamp(v, a, b) { return Math.max(a, Math.min(b, v)); }
  function gel(n) { return 'GEL ' + n; }
  function esc(s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function plural(n, one, many) { return n + ' ' + (n === 1 ? one : many); }
  function guestsLabel() { return plural(state.adults, 'adult', 'adults') + (state.kids ? ', ' + plural(state.kids, 'child', 'children') : ''); }
  function nightsLabel() { return plural(state.nights, 'night', 'nights'); }
  function guests() { return state.adults + state.kids; }
  function prices(r) { return guests() === 1 ? { p: r.p1, was: r.w1 } : { p: r.p2, was: r.w2 }; }

  var ICON = {
    guests: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>',
    size: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16v16H4z"/><path d="M4 9h5V4"/></svg>',
    bed: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 18V8M3 14h18v4M21 14v-2a3 3 0 0 0-3-3h-7v5"/></svg>',
    plus: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v18M3 12h18"/></svg>',
    check: '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5L20 7"/></svg>'
  };

  function media(r) {
    var img = r.img
      ? '<img src="' + r.img + '" alt="' + esc(r.alt) + '" loading="lazy" width="920" height="600">'
      : '<div class="room__placeholder">[Room photos coming soon]</div>';
    return '<div class="room__media">' + img +
      '<span class="tag tag--deal">−25%</span>' +
      (r.left ? '<span class="tag tag--left">' + esc(r.left) + '</span>' : '') + '</div>';
  }

  function panel(r) {
    var pr = prices(r);
    var nightly = state.rate === 'bf' ? pr.p[1] : pr.p[0];
    var total = nightly * state.nights;
    var stepIdx = { choose: 1, details: 2, done: 3 }[state.step];
    var steps = ['Room', 'Your details', 'Done'].map(function (s, i) {
      return '<li class="' + (stepIdx >= i + 1 ? 'is-active' : '') + '"' + (stepIdx === i + 1 ? ' aria-current="step"' : '') + '>' + (i + 1) + ' · ' + s + '</li>';
    }).join('');
    var rateName = state.rate === 'bf' ? 'with breakfast' : 'room only';
    var line = esc(r.name) + ' · ' + rateName + ' · ' + nightsLabel() + ' · ' + guestsLabel();

    var body = '';
    if (state.step === 'choose') {
      body =
        '<div role="group" aria-label="Choose your rate" class="stack" style="gap:10px">' +
          '<button type="button" class="rate" data-action="rate" data-rate="room" aria-pressed="' + (state.rate === 'room') + '"><span><strong>Room only</strong><small>Free cancellation · pay at the hotel</small></span><strong>' + gel(pr.p[0]) + '</strong></button>' +
          '<button type="button" class="rate" data-action="rate" data-rate="bf" aria-pressed="' + (state.rate === 'bf') + '"><span><strong>With buffet breakfast</strong><small>+' + gel(pr.p[1] - pr.p[0]) + ' per night · served every morning</small></span><strong>' + gel(pr.p[1]) + '</strong></button>' +
        '</div>' +
        '<div class="summary">' +
          '<div><span>' + gel(nightly) + ' × ' + nightsLabel() + '</span><span>' + gel(total) + '</span></div>' +
          '<div class="muted"><span>Guests</span><span>' + guestsLabel() + '</span></div>' +
          '<div class="muted"><span>Taxes &amp; fees</span><span>Included</span></div>' +
          '<div class="total"><strong>Total</strong><strong>' + gel(total) + '</strong></div>' +
          '<p class="ok-note">You pay nothing now. Free cancellation until [DEADLINE].</p>' +
        '</div>' +
        '<button type="button" class="btn btn--primary btn--block" data-action="next">Continue · ' + gel(total) + '</button>';
    } else if (state.step === 'details') {
      body =
        '<form class="stack" style="gap:12px" data-details novalidate>' +
          '<p class="small">' + line + ' · <strong style="color:var(--cocoa)">' + gel(total) + '</strong></p>' +
          '<label class="input">Full name<input name="name" type="text" autocomplete="name" required></label>' +
          '<div class="form-grid">' +
            '<label class="input">Email<input name="email" type="email" autocomplete="email" required></label>' +
            '<label class="input">Phone / WhatsApp<input name="phone" type="tel" autocomplete="tel" required></label>' +
          '</div>' +
          '<label class="input">Arrival time<select name="arrival"><option>I don\'t know yet</option><option>Morning</option><option>Afternoon</option><option>Evening</option><option>Late night</option></select></label>' +
          '<label class="input">Anything we should know? (optional)<textarea name="notes" rows="2"></textarea></label>' +
          '<p class="small" data-error role="alert" hidden style="color:var(--terracotta);font-weight:700">Please add your name, a valid email and a phone number.</p>' +
          '<div style="display:flex;gap:10px;flex-wrap:wrap">' +
            '<button type="button" class="btn btn--outline" data-action="back">← Back</button>' +
            '<button type="submit" class="btn btn--primary" style="flex-grow:1">Confirm booking</button>' +
          '</div>' +
          '<p class="small">No card needed. You\'ll pay at the hotel.</p>' +
        '</form>';
    } else {
      body =
        '<div class="stack" role="status" style="gap:14px;align-items:flex-start">' +
          '<span class="done-icon">' + ICON.check + '</span>' +
          '<h3 class="h3">You\'re booked. <em>See you in Tbilisi.</em></h3>' +
          '<p class="lead" style="font-size:15px">' + line + '<br>Total ' + gel(total) + ', paid at the hotel. Booking ref <strong>[MG-0000]</strong>.</p>' +
          '<p class="small">We\'ve emailed your confirmation. Questions? <a href="https://wa.me/995533334422">WhatsApp us</a>.</p>' +
          '<button type="button" class="btn btn--outline" data-action="close">Back to rooms</button>' +
        '</div>';
    }

    var thumbs = (r.img ? '<img src="' + r.img + '" alt="" loading="lazy">' : '<div class="ph">[photo]</div>') +
      '<img src="assets/img/bathroom.webp" alt="Private bathroom with patterned tiles" loading="lazy">' +
      '<img src="assets/img/welcome-tray.webp" alt="Welcome tea and fruit" loading="lazy">';

    return '<div class="panel" id="panel-' + r.id + '">' +
      '<div class="panel__left">' +
        '<div class="thumbs">' + thumbs + '</div>' +
        '<p class="list-title">In your room</p>' +
        '<ul class="checks"><li>' + esc(r.bed) + '</li><li>' + esc(r.size) + ', ' + esc(r.extra.toLowerCase()) + '</li><li>Air conditioning</li><li>Private bathroom, shower</li><li>Flat-screen TV</li><li>Free Wi‑Fi</li><li>City &amp; garden view</li><li>Non-smoking</li></ul>' +
      '</div>' +
      '<div class="panel__right"><ol class="steps" aria-label="Booking steps">' + steps + '</ol>' + body + '</div>' +
    '</div>';
  }

  function render() {
    var visible = ROOMS.filter(function (r) { return r.cap >= guests(); });
    if (!visible.some(function (r) { return r.id === state.openId; })) state.openId = null;
    var hidden = ROOMS.length - visible.length;

    document.querySelector('[data-value="adults"]').textContent = state.adults;
    document.querySelector('[data-value="kids"]').textContent = state.kids;
    document.querySelector('[data-value="nights"]').textContent = nightsLabel();
    document.querySelector('[data-action="adults-"]').disabled = state.adults <= 1;
    document.querySelector('[data-action="adults+"]').disabled = guests() >= MAX_GUESTS;
    document.querySelector('[data-action="kids-"]').disabled = state.kids <= 0;
    document.querySelector('[data-action="kids+"]').disabled = guests() >= MAX_GUESTS || state.kids >= 2;
    document.querySelector('[data-action="nights-"]').disabled = state.nights <= 1;
    document.querySelector('[data-action="nights+"]').disabled = state.nights >= 30;
    summaryEl.textContent = plural(visible.length, 'room fits', 'rooms fit') + ' ' + guestsLabel() + ' · prices for ' + nightsLabel();

    var html = visible.map(function (r) {
      var pr = prices(r);
      var open = r.id === state.openId;
      return '<article class="room" id="room-' + r.id + '">' +
        '<div class="room__main">' + media(r) +
          '<div class="room__body">' +
            '<div class="room__info">' +
              '<h2 class="room__name">' + esc(r.name) + '</h2>' +
              '<ul class="meta"><li>' + ICON.guests + (r.cap === 1 ? '1 guest' : 'Up to ' + r.cap + ' guests') + '</li><li>' + ICON.size + esc(r.size) + '</li><li>' + ICON.bed + esc(r.bed) + '</li><li>' + ICON.plus + esc(r.extra) + '</li></ul>' +
              '<p class="lead" style="font-size:16px">' + esc(r.blurb) + '</p>' +
              '<p class="small" style="font-size:14px">Air conditioning · Private bathroom · TV · Free Wi‑Fi · City &amp; garden view</p>' +
            '</div>' +
            '<div class="room__price">' +
              '<div><span class="small">per night, from</span><div class="big"><s>' + gel(pr.was) + '</s><strong>' + gel(pr.p[0]) + '</strong></div><span class="small">' + gel(pr.p[0] * state.nights) + ' for ' + nightsLabel() + ' · taxes included</span></div>' +
              (open
                ? '<button type="button" class="btn btn--outline" data-action="close" aria-expanded="true" aria-controls="panel-' + r.id + '">Close ↑</button>'
                : '<button type="button" class="btn btn--primary" data-action="open" data-id="' + r.id + '" aria-expanded="false">Choose this room</button>') +
            '</div>' +
          '</div>' +
        '</div>' +
        (open ? panel(r) : '') +
      '</article>';
    }).join('');

    if (hidden > 0) {
      html += '<div class="hint-box"><span>' + plural(hidden, 'room is', 'rooms are') + ' too small for ' + guestsLabel() + '. Travelling as a bigger group? We\'ll put you in rooms next to each other.</span><a href="https://wa.me/995533334422" class="link-arrow">Ask us on WhatsApp →</a></div>';
    }
    list.innerHTML = html;

    if (mobilePrice) {
      var cheapest = Math.min.apply(null, visible.map(function (r) { return prices(r).p[0]; }));
      mobilePrice.textContent = gel(cheapest);
    }
  }

  function focusPanel() {
    var el = document.getElementById('room-' + state.openId);
    if (el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'start' });
      var first = el.querySelector('.panel button, .panel input');
      if (first) setTimeout(function () { first.focus({ preventScroll: true }); }, 350);
    }
  }

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-action]');
    if (!btn) return;
    var a = btn.getAttribute('data-action');
    if (a === 'adults+' && guests() < MAX_GUESTS) state.adults++;
    else if (a === 'adults-' && state.adults > 1) state.adults--;
    else if (a === 'kids+' && guests() < MAX_GUESTS && state.kids < 2) state.kids++;
    else if (a === 'kids-' && state.kids > 0) state.kids--;
    else if (a === 'nights+' && state.nights < 30) state.nights++;
    else if (a === 'nights-' && state.nights > 1) state.nights--;
    else if (a === 'open') { state.openId = btn.getAttribute('data-id'); state.step = 'choose'; state.rate = 'room'; render(); focusPanel(); return; }
    else if (a === 'close') { var id = state.openId; state.openId = null; state.step = 'choose'; render(); var b = document.querySelector('[data-id="' + id + '"]'); if (b) b.focus(); return; }
    else if (a === 'rate') state.rate = btn.getAttribute('data-rate');
    else if (a === 'next') { state.step = 'details'; render(); focusPanel(); return; }
    else if (a === 'back') state.step = 'choose';
    else return;
    render();
  });

  document.addEventListener('submit', function (e) {
    var form = e.target.closest('[data-details]');
    if (!form) return;
    e.preventDefault();
    var f = form.elements;
    var ok = f.namedItem('name').value.trim() && /\S+@\S+\.\S+/.test(f.namedItem('email').value) && f.namedItem('phone').value.trim().length >= 6;
    var err = form.querySelector('[data-error]');
    if (!ok) { err.hidden = false; return; }
    // TODO: send the booking to the hotel's booking engine here.
    state.step = 'done';
    render();
    focusPanel();
  });

  var checkinEl = document.getElementById('checkin');
  if (checkinEl) {
    checkinEl.min = new Date().toISOString().slice(0, 10);
    if (state.checkin) checkinEl.value = state.checkin;
  }

  render();
  if (state.openId) focusPanel();
})();
