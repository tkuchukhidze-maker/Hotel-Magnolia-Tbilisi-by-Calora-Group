/* Hotel Magnolia — Rooms & booking flow
   1 choose rooms (any types, any number, up to what we have free)
   → 2 guest details → 3 confirmation.
   Guests: adults + children (each child with an age). The selected rooms must
   sleep everyone; if not, we suggest the cheapest combination that does.
   Room data and limits come from Laravel (window.MAGNOLIA, see rooms.blade.php).
   The server recalculates the price (App\Support\BookingQuote); this file only shows it. */
(function () {
  'use strict';

  var M = window.MAGNOLIA || {};
  var ROOMS = M.rooms || [];
  var MAX_ADULTS = M.maxAdults || 12;
  var MAX_KIDS = M.maxChildren || 6;
  var CHILD_MAX_AGE = M.childMaxAge == null ? 17 : M.childMaxAge;
  var MAX_NIGHTS = M.maxNights || 30;
  var CSRF = (document.querySelector('meta[name="csrf-token"]') || {}).content;
  var BY_ID = {};
  ROOMS.forEach(function (r) { BY_ID[r.id] = r; });

  var list = document.getElementById('room-list');
  var aside = document.getElementById('booking-summary');
  var agesEl = document.querySelector('[data-ages]');
  var checkinEl = document.getElementById('checkin');
  var mobileLabel = document.querySelector('[data-mobile-label]');
  var mobileCta = document.querySelector('[data-mobile-cta]');
  if (!list || !aside) return;

  function clamp(v, a, b) { return Math.max(a, Math.min(b, v)); }
  function int(v, d) { var n = parseInt(v, 10); return isNaN(n) ? d : n; }

  /* ---------- State (URL params from the home page preselect it) ---------- */
  var params = new URLSearchParams(window.location.search);
  var state = {
    adults: clamp(int(params.get('adults'), 2), 1, MAX_ADULTS),
    kids: clamp(int(params.get('children'), 0), 0, MAX_KIDS),
    ages: [],
    nights: 2,
    checkin: params.get('checkin') || '',
    qty: {},
    rate: 'room',
    step: 'choose',
    sending: false,
    error: '',
    result: null,
    form: {}
  };
  (params.get('ages') || '').split(',').forEach(function (a, i) {
    var n = parseInt(a, 10);
    if (i < state.kids && !isNaN(n)) state.ages[i] = clamp(n, 0, CHILD_MAX_AGE);
  });
  var checkout = params.get('checkout');
  if (state.checkin && checkout) {
    var n0 = Math.round((new Date(checkout) - new Date(state.checkin)) / 86400000);
    if (n0 > 0) state.nights = clamp(n0, 1, MAX_NIGHTS);
  }
  var preRoom = params.get('room');
  if (preRoom && BY_ID[preRoom]) {
    // Enough of the chosen type to sleep everyone, if we have them.
    var r0 = BY_ID[preRoom];
    state.qty[preRoom] = clamp(Math.ceil((state.adults + state.kids) / r0.cap), 1, r0.stock || 1);
  }

  /* ---------- Helpers ---------- */
  function gel(n) { return 'GEL ' + n; }
  function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function plural(n, one, many) { return n + ' ' + (n === 1 ? one : many); }
  function guests() { return state.adults + state.kids; }
  function guestsLabel() { return plural(state.adults, 'adult', 'adults') + (state.kids ? ', ' + plural(state.kids, 'child', 'children') : ''); }
  function nightsLabel() { return plural(state.nights, 'night', 'nights'); }
  function today() { var d = new Date(); return [d.getFullYear(), String(d.getMonth()+1).padStart(2,'0'), String(d.getDate()).padStart(2,'0')].join('-'); }
  function addDays(iso, d) { var x = new Date(iso + 'T12:00:00'); x.setDate(x.getDate()+d); return [x.getFullYear(),String(x.getMonth()+1).padStart(2,'0'),String(x.getDate()).padStart(2,'0')].join('-'); }
  function fmtDate(iso) {
    if (!iso) return '';
    return new Date(iso + 'T00:00:00').toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short' });
  }
  function ageLabel(a) { return a === 0 ? 'under 1' : a + (a === 1 ? ' year' : ' years'); }

  function items() {
    return ROOMS.filter(function (r) { return state.qty[r.id] > 0; })
      .map(function (r) { return { room: r.id, qty: state.qty[r.id] }; });
  }
  function roomsCount() { return items().reduce(function (s, i) { return s + i.qty; }, 0); }

  /* Same rule as BookingQuote.php: the 1-guest price only for one room, one guest. */
  function quote(its, rate) {
    var rooms = its.reduce(function (s, i) { return s + i.qty; }, 0);
    var single = rooms === 1 && guests() === 1;
    var q = { lines: [], capacity: 0, rooms: rooms, nightly: 0 };
    its.forEach(function (i) {
      var r = BY_ID[i.room];
      var p = single ? r.p1 : r.p2;
      var per = rate === 'bf' ? p[1] : p[0];
      q.lines.push({ room: r.id, name: r.name, qty: i.qty, nightly: per, subtotal: per * i.qty });
      q.capacity += r.cap * i.qty;
      q.nightly += per * i.qty;
    });
    q.total = q.nightly * state.nights;
    return q;
  }

  /* Cheapest set of rooms that sleeps everyone (fewest rooms on a tie). ~2,400 combinations at most. */
  function suggest() {
    var need = guests();
    var best = null;
    function walk(i, its, cap, cost, rooms) {
      if (cap >= need) {
        if (!best || cost < best.cost || (cost === best.cost && rooms < best.rooms)) best = { its: its.slice(), cost: cost, rooms: rooms };
        return;
      }
      if (i === ROOMS.length) return;
      var r = ROOMS[i];
      var max = Math.min(r.stock || 0, Math.ceil(need / r.cap));
      for (var q = 0; q <= max; q++) {
        if (q) its.push({ room: r.id, qty: q });
        walk(i + 1, its, cap + r.cap * q, cost + (need === 1 && q === 1 && rooms === 0 ? r.p1[0] : r.p2[0]) * q, rooms + q);
        if (q) its.pop();
      }
    }
    walk(0, [], 0, 0, 0);
    return best;
  }
  function itemsText(its) { return its.map(function (i) { return i.qty + ' × ' + BY_ID[i.room].name; }).join(' + '); }
  function sameItems(a, b) { return itemsText(a) === itemsText(b); }

  var ICON = {
    guests: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>',
    size: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16v16H4z"/><path d="M4 9h5V4"/></svg>',
    bed: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 18V8M3 14h18v4M21 14v-2a3 3 0 0 0-3-3h-7v5"/></svg>',
    plus: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v18M3 12h18"/></svg>',
    check: '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5L20 7"/></svg>'
  };

  /* ---------- Search bar: steppers + children's ages ---------- */
  function setText(sel, txt) { var el = document.querySelector(sel); if (el) el.textContent = txt; }
  function setDisabled(sel, v) { var el = document.querySelector(sel); if (el) el.disabled = v; }

  function renderSearch() {
    setText('[data-value="adults"]', state.adults);
    setText('[data-value="kids"]', state.kids);
    setText('[data-value="nights"]', nightsLabel());
    var locked = state.step !== 'choose';
    setDisabled('[data-action="adults-"]', locked || state.adults <= 1);
    setDisabled('[data-action="adults+"]', locked || state.adults >= MAX_ADULTS);
    setDisabled('[data-action="kids-"]', locked || state.kids <= 0);
    setDisabled('[data-action="kids+"]', locked || state.kids >= MAX_KIDS);
    setDisabled('[data-action="nights-"]', locked || state.nights <= 1);
    setDisabled('[data-action="nights+"]', locked || state.nights >= MAX_NIGHTS);
    if (checkinEl) checkinEl.disabled = locked;

    if (!agesEl) return;
    agesEl.hidden = state.kids === 0;
    // Rebuild only when the number of children changes, so an open select keeps focus.
    if (agesEl.getAttribute('data-count') !== String(state.kids)) {
      agesEl.setAttribute('data-count', String(state.kids));
      var opts = '';
      for (var a = 0; a <= CHILD_MAX_AGE; a++) opts += '<option value="' + a + '">' + (a === 0 ? 'Under 1' : a) + '</option>';
      var html = '<p class="ages__title">How old are the children on the day of arrival?</p><div class="ages__grid">';
      for (var i = 0; i < state.kids; i++) {
        html += '<label class="input">Child ' + (i + 1) + ' age<select data-age="' + i + '"><option value="">Choose age</option>' + opts + '</select></label>';
      }
      agesEl.innerHTML = html + '</div>';
    }
    agesEl.querySelectorAll('[data-age]').forEach(function (sel) {
      var i = int(sel.getAttribute('data-age'), 0);
      sel.value = state.ages[i] == null ? '' : String(state.ages[i]);
      sel.disabled = locked;
    });
  }

  /* ---------- Room list ---------- */
  function media(r, sel) {
    var img = r.img
      ? '<img src="' + esc(r.img) + '" alt="' + esc(r.alt) + '" loading="lazy" width="920" height="600">'
      : '<div class="room__placeholder">[Room photos coming soon]</div>';
    var left = ''; // No date-specific inventory: do not imply rooms are almost sold out
    return '<div class="room__media">' + img + '<span class="tag tag--deal">−25%</span>' + left +
      (sel ? '<span class="tag tag--picked">' + ICON.check.replace(/26/g, '14') + ' ' + sel + ' selected</span>' : '') + '</div>';
  }

  function roomCard(r) {
    var q = state.qty[r.id] || 0;
    var single = guests() === 1 && r.cap === 1;
    var price = single || (guests() === 1 && roomsCount() <= 1) ? r.p1 : r.p2;
    var was = price === r.p1 ? r.w1 : r.w2;
    var stock = r.stock || 0;
    var control;
    if (stock === 0) {
      control = '<p class="small" style="font-weight:700">Please ask about this room. <a href="' + esc(M.whatsapp) + '">Ask us</a></p>';
    } else if (q === 0) {
      control = '<button type="button" class="btn btn--primary" data-action="qty+" data-id="' + r.id + '">Add this room</button>';
    } else {
      control = '<div class="qty" role="group" aria-label="Number of ' + esc(r.name) + 's">' +
        '<button type="button" class="round-btn" data-action="qty-" data-id="' + r.id + '" aria-label="One ' + esc(r.name) + ' less">−</button>' +
        '<span class="qty__value"><strong>' + q + '</strong> ' + (q === 1 ? 'room' : 'rooms') + '</span>' +
        '<button type="button" class="round-btn" data-action="qty+" data-id="' + r.id + '" aria-label="One more ' + esc(r.name) + '"' + (q >= stock ? ' disabled' : '') + '>+</button>' +
      '</div>' +
      (q >= stock ? '<span class="small">For more rooms, please contact us.</span>' : '');
    }
    return '<article class="room' + (q ? ' is-selected' : '') + '" id="room-' + r.id + '">' +
      '<div class="room__main">' + media(r, q) +
        '<div class="room__body">' +
          '<div class="room__info">' +
            '<h2 class="room__name">' + esc(r.name) + '</h2>' +
            '<ul class="meta"><li>' + ICON.guests + (r.cap === 1 ? '1 guest' : 'Up to ' + r.cap + ' guests') + '</li><li>' + ICON.size + esc(r.size) + '</li><li>' + ICON.bed + esc(r.bed) + '</li><li>' + ICON.plus + esc(r.extra) + '</li></ul>' +
            '<p class="lead" style="font-size:16px">' + esc(r.blurb) + '</p>' +
            '<p class="small" style="font-size:14px">Air conditioning · Private bathroom · TV · Free Wi‑Fi · City &amp; garden view</p>' +
          '</div>' +
          '<div class="room__price">' +
            '<div><span class="small">per room per night</span><div class="big"><s>' + gel(was) + '</s><strong>' + gel(price[0]) + '</strong></div><span class="small">' + gel(price[1]) + ' with breakfast · taxes included</span></div>' +
            '<div class="room__ctrl">' + control + '</div>' +
          '</div>' +
        '</div>' +
      '</div>' +
    '</article>';
  }

  /* ---------- Details form & confirmation (replace the list) ---------- */
  function detailsForm(q) {
    var f = state.form;
    return '<div class="checkout">' +
      '<h2 class="h3" style="margin:0">Your details</h2>' +
      '<p class="small">This is a booking request, not an instant confirmation. We'll check availability and contact you.</p>' +
      '<form class="stack" style="gap:12px" data-details novalidate>' +
        (state.checkin ? '' :
          '<label class="input">Check-in date *<input name="checkin" type="date" min="' + today() + '" required></label>') +
        '<div class="form-grid">' +
          '<label class="input">First name *<input name="first_name" type="text" autocomplete="given-name" required maxlength="80" value="' + esc(f.first_name) + '"></label>' +
          '<label class="input">Last name *<input name="last_name" type="text" autocomplete="family-name" required maxlength="80" value="' + esc(f.last_name) + '"></label>' +
        '</div>' +
        '<div class="form-grid">' +
          '<label class="input">Email *<input name="email" type="email" autocomplete="email" required maxlength="160" value="' + esc(f.email) + '"></label>' +
          '<label class="input">Phone / WhatsApp *<input name="phone" type="tel" autocomplete="tel" required placeholder="+995 5XX XX XX XX" maxlength="40" value="' + esc(f.phone) + '"></label>' +
        '</div>' +
        '<div class="form-grid">' +
          '<label class="input">Anything we should know? (optional)<textarea name="notes" rows="2" maxlength="1000" placeholder="' + (state.kids ? 'A baby cot, rooms next to each other, late arrival…' : 'Rooms next to each other, late arrival, a quiet room…') + '">' + esc(f.notes) + '</textarea></label>' +
        '<label style="display:flex;gap:10px;align-items:flex-start;font-size:14px;line-height:1.5;cursor:pointer"><input name="privacy" type="checkbox" required style="width:20px;height:20px;margin-top:1px;flex:none;accent-color:var(--terracotta)"' + (f.privacy ? ' checked' : '') + '><span>I agree that Hotel Magnolia Tbilisi by Calora Group uses my details to handle this booking, as described in the <a href="' + esc(M.privacyUrl) + '" target="_blank" rel="noopener">privacy policy</a>. *</span></label>' +
        '<p class="small" data-error role="alert"' + (state.error ? '' : ' hidden') + ' style="color:var(--terracotta);font-weight:700">' + esc(state.error) + '</p>' +
        '<div style="display:flex;gap:10px;flex-wrap:wrap">' +
          '<button type="button" class="btn btn--outline" data-action="back">← Change rooms</button>' +
          '<button type="submit" class="btn btn--primary" style="flex-grow:1"' + (state.sending ? ' disabled aria-busy="true"' : '') + '>' + (state.sending ? 'Sending…' : 'Send booking request · ' + gel(q.total)) + '</button>' +
        '</div>' +
        '<p class="small">No card needed. You\'ll pay at the hotel.</p>' +
      '</form>' +
    '</div>';
  }

  function doneView() {
    var res = state.result || {};
    var lines = (res.items || []).map(function (l) { return '<li>' + l.qty + ' × ' + esc(l.name) + '</li>'; }).join('');
    return '<div class="checkout stack" role="status" style="gap:14px;align-items:flex-start">' +
      '<span class="done-icon">' + ICON.check + '</span>' +
      '<h2 class="h3" style="margin:0">You\'re booked. <em>We'll be in touch.</em></h2>' +
      '<p class="lead" style="font-size:16px">' + fmtDate(res.checkin) + ' → ' + fmtDate(res.checkout) + ' · ' + guestsLabel() + '</p>' +
      '<ul class="checks" style="grid-template-columns:1fr">' + lines + '</ul>' +
      '<p class="lead" style="font-size:16px">Quoted total ' + gel(res.total) + ', payable after confirmation. Request ref <strong>' + esc(res.reference) + '</strong>.</p>' +
      '<p class="small">We\'ll confirm by email or WhatsApp shortly. Questions? <a href="' + esc(M.whatsapp) + '">WhatsApp us</a>.</p>' +
      '<button type="button" class="btn btn--outline" data-action="restart">Make another booking</button>' +
    '</div>';
  }

  /* ---------- Summary (sticky on desktop) ---------- */
  function agesMissing() {
    for (var i = 0; i < state.kids; i++) if (state.ages[i] == null) return true;
    return false;
  }

  function summary(q) {
    var stepIdx = { choose: 1, details: 2, done: 3 }[state.step];
    var steps = ['Rooms', 'Your details', 'Request sent'].map(function (s, i) {
      return '<li class="' + (stepIdx >= i + 1 ? 'is-active' : '') + '"' + (stepIdx === i + 1 ? ' aria-current="step"' : '') + '>' + s + '</li>';
    }).join('');
    var need = guests();
    var enough = q.rooms > 0 && q.capacity >= need;
    var best = suggest();
    var html = '<ol class="steps" aria-label="Booking steps">' + steps + '</ol>' +
      '<div class="book-summary__stay">' +
        '<div><span>Dates</span><strong>' + (state.checkin ? fmtDate(state.checkin) + ' → ' + fmtDate(addDays(state.checkin, state.nights)) : 'Choose check-in') + '</strong></div>' +
        '<div><span>Stay</span><strong>' + nightsLabel() + '</strong></div>' +
        '<div><span>Guests</span><strong>' + guestsLabel() + '</strong></div>' +
        (state.kids && !agesMissing() ? '<div><span>Children\'s ages</span><strong>' + state.ages.slice(0, state.kids).map(ageLabel).join(', ') + '</strong></div>' : '') +
      '</div>';

    if (state.step === 'done') return html;

    if (!q.rooms) {
      html += '<p class="small" style="margin:0">No rooms selected yet. Add rooms from the list' + (best ? ', or take our suggestion:' : '.') + '</p>';
    } else {
      html += '<ul class="book-summary__lines">' + q.lines.map(function (l) {
        return '<li><span>' + l.qty + ' × ' + esc(l.name) + '<small>' + gel(l.nightly) + ' per room per night</small></span><strong>' + gel(l.subtotal) + '</strong></li>';
      }).join('') + '</ul>';
      var pct = Math.min(100, Math.round(q.capacity / Math.max(need, 1) * 100));
      html += '<div class="capacity' + (enough ? ' is-ok' : '') + '">' +
        '<div class="capacity__bar"><span style="width:' + pct + '%"></span></div>' +
        '<p>' + (enough
          ? '✓ Sleeps ' + q.capacity + ' · everyone has a bed'
          : 'These rooms sleep ' + q.capacity + '. You are ' + need + ', so add ' + (need - q.capacity === 1 ? 'a bed for 1 more' : 'beds for ' + (need - q.capacity) + ' more') + '.') + '</p>' +
      '</div>';
    }

    if (state.step === 'choose' && best && !(enough && sameItems(items(), best.its)) && (!enough || quote(best.its, state.rate).nightly < q.nightly)) {
      html += '<div class="suggest"><span class="small">' + (enough ? 'Cheaper option for ' : 'Fits ') + guestsLabel() + '</span>' +
        '<strong>' + esc(itemsText(best.its)) + '</strong>' +
        '<span class="small">' + gel(quote(best.its, state.rate).nightly) + ' per night</span>' +
        '<button type="button" class="btn btn--outline btn--sm" data-action="suggest">Use this</button></div>';
    } else if (!best) {
      html += '<div class="suggest"><span class="small">We don\'t have enough free rooms online for ' + guestsLabel() + '. Write to us and we\'ll sort it out.</span><a class="link-arrow" href="' + esc(M.whatsapp) + '">WhatsApp us →</a></div>';
    }

    if (q.rooms) {
      var bf = quote(items(), 'bf'), ro = quote(items(), 'room');
      if (state.step === 'choose') {
        html += '<div role="group" aria-label="Choose your rate" class="stack" style="gap:8px">' +
          '<button type="button" class="rate" data-action="rate" data-rate="room" aria-pressed="' + (state.rate === 'room') + '"><span><strong>Room only</strong><small>Terms confirmed by the hotel</small></span><strong>' + gel(ro.nightly) + '</strong></button>' +
          '<button type="button" class="rate" data-action="rate" data-rate="bf" aria-pressed="' + (state.rate === 'bf') + '"><span><strong>With buffet breakfast</strong><small>+' + gel(bf.nightly - ro.nightly) + ' per night, all rooms</small></span><strong>' + gel(bf.nightly) + '</strong></button>' +
        '</div>';
      } else {
        html += '<p class="small" style="margin:0">' + (state.rate === 'bf' ? 'With buffet breakfast' : 'Room only') + '</p>';
      }
      html += '<div class="summary">' +
        '<div><span>' + gel(q.nightly) + ' × ' + nightsLabel() + '</span><span>' + gel(q.total) + '</span></div>' +
        '<div class="muted"><span>Taxes &amp; fees</span><span>Included</span></div>' +
        '<div class="total"><strong>Total</strong><strong>' + gel(q.total) + '</strong></div>' +
        '<p class="ok-note">No payment now. Final terms and availability are subject to hotel confirmation.</p>' +
      '</div>';
    }

    if (state.step === 'choose') {
      var why = !q.rooms ? 'Add at least one room' : !enough ? 'Add room for everyone first' : agesMissing() ? 'Add the children\'s ages above' : '';
      html += '<button type="button" class="btn btn--primary btn--block" data-action="next"' + (why ? ' disabled' : '') + '>' + (why || 'Continue ·  + gel(q.total)) + '</button>';
    }
    return html;
  }

  /* ---------- Render ---------- */
  function render() {
    var q = quote(items(), state.rate);
    renderSearch();
    var layout = list.parentElement;
    if (layout) layout.classList.toggle('is-checkout', state.step !== 'choose');

    if (state.step === 'choose') list.innerHTML = ROOMS.map(roomCard).join('');
    else if (state.step === 'details') list.innerHTML = detailsForm(q);
    else list.innerHTML = doneView();
    aside.innerHTML = summary(q);
    // No address lookup required for an initial booking request.

    if (mobileLabel) {
      mobileLabel.innerHTML = q.rooms
        ? plural(q.rooms, 'room', 'rooms') + ' · ' + nightsLabel() + '<strong data-mobile-price>' + gel(q.total) + '</strong>'
        : 'per night from<strong data-mobile-price>' + gel(Math.min.apply(null, ROOMS.map(function (r) { return guests() === 1 ? r.p1[0] : r.p2[0]; }))) + '</strong>';
    }
    if (mobileCta) mobileCta.textContent = state.step === 'details' ? 'Send request' : q.rooms ? 'Review request' : 'Choose rooms';
  }

  function focusIn(el, sel) {
    if (!el) return;
    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    var first = el.querySelector(sel);
    if (first) setTimeout(function () { first.focus({ preventScroll: true }); }, 350);
  }

  // Google address search (maps.js) replaces the plain address input when a Maps key is set.
  function mountAddress() {
    var host = list.querySelector('[data-address-host]');
    if (!host || !window.MagnoliaMaps) return;
    var form = host.closest('form');
    window.MagnoliaMaps.mountAddressAutocomplete(host, function (a) {
      state.form.address = a.address;
      state.form.city = a.city;
      state.form.postal_code = a.postal_code;
      form.elements.namedItem('address').value = a.address;
      form.elements.namedItem('city').value = a.city;
      form.elements.namedItem('postal_code').value = a.postal_code;
      var country = form.elements.namedItem('country');
      if (a.country && country) { country.value = a.country; state.form.country = a.country; }
      var picked = form.querySelector('[data-address-picked]');
      picked.textContent = '✓ ' + a.address;
      picked.hidden = false;
    }).catch(function (err) { console.warn('Address search unavailable:', err); });
  }

  function rememberForm() {
    var form = list.querySelector('[data-details]');
    if (!form) return;
    ['first_name', 'last_name', 'email', 'phone', 'arrival', 'notes'].forEach(function (k) {
      var el = form.elements.namedItem(k);
      if (el) state.form[k] = el.value;
    });
    var p = form.elements.namedItem('privacy');
    if (p) state.form.privacy = p.checked;
  }

  /* ---------- Events ---------- */
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-action]');
    if (!btn || btn.disabled) return;
    var a = btn.getAttribute('data-action');
    var id = btn.getAttribute('data-id');
    var r = id && BY_ID[id];
    if (a === 'adults+' && state.adults < MAX_ADULTS) state.adults++;
    else if (a === 'adults-' && state.adults > 1) state.adults--;
    else if (a === 'kids+' && state.kids < MAX_KIDS) state.kids++;
    else if (a === 'kids-' && state.kids > 0) { state.kids--; state.ages.length = state.kids; }
    else if (a === 'nights+' && state.nights < MAX_NIGHTS) state.nights++;
    else if (a === 'nights-' && state.nights > 1) state.nights--;
    else if (a === 'qty+' && r) {
      var cur = state.qty[id] || 0;
      if (cur >= (r.stock || 0)) return;
      state.qty[id] = cur + 1;
      render();
      var ctrl = document.querySelector('#room-' + id + ' [data-action="qty+"]') || document.querySelector('#room-' + id + ' [data-action="qty-"]');
      if (ctrl) ctrl.focus({ preventScroll: true });
      return;
    }
    else if (a === 'qty-' && r) {
      state.qty[id] = Math.max(0, (state.qty[id] || 0) - 1);
      render();
      var c2 = document.querySelector('#room-' + id + ' [data-action="qty-"]') || document.querySelector('#room-' + id + ' [data-action="qty+"]');
      if (c2) c2.focus({ preventScroll: true });
      return;
    }
    else if (a === 'suggest') {
      var best = suggest();
      if (!best) return;
      state.qty = {};
      best.its.forEach(function (i) { state.qty[i.room] = i.qty; });
    }
    else if (a === 'rate') state.rate = btn.getAttribute('data-rate');
    else if (a === 'next') { state.step = 'details'; state.error = ''; render(); focusIn(list, 'input'); return; }
    else if (a === 'back') { rememberForm(); state.step = 'choose'; state.error = ''; render(); focusIn(list, '.room.is-selected button, .room button'); return; }
    else if (a === 'restart') { state.step = 'choose'; state.qty = {}; state.result = null; render(); focusIn(document.getElementById('search'), 'input, button'); return; }
    else return;
    render();
  });

  if (agesEl) {
    agesEl.addEventListener('change', function (e) {
      var sel = e.target.closest('[data-age]');
      if (!sel) return;
      var i = int(sel.getAttribute('data-age'), 0);
      state.ages[i] = sel.value === '' ? null : int(sel.value, 0);
      render();
    });
  }

  if (checkinEl) {
    checkinEl.min = today();
    if (state.checkin) checkinEl.value = state.checkin;
    checkinEl.addEventListener('change', function () { state.checkin = checkinEl.value; render(); });
  }

  document.addEventListener('submit', function (e) {
    var form = e.target.closest('[data-details]');
    if (!form) return;
    e.preventDefault();
    if (state.sending) return;
    rememberForm();
    var inlineCheckin = form.elements.namedItem('checkin');
    if (inlineCheckin && inlineCheckin.value) {
      state.checkin = inlineCheckin.value;
      if (checkinEl) checkinEl.value = state.checkin;
    }
    var f = state.form;
    var missing = [];
    if (!state.checkin || state.checkin < today()) missing.push('a valid check-in date');
    if (!(f.first_name || '').trim()) missing.push('first name');
    if (!(f.last_name || '').trim()) missing.push('last name');
    if (!/^\S+@\S+\.\S+$/.test((f.email || '').trim())) missing.push('a valid email');
    if (!/^[0-9+()\-\s]{6,}$/.test((f.phone || '').trim())) missing.push('a phone number');
    if (missing.length) state.error = 'Please add your ' + missing.join(', ') + '.';
    else if (!f.privacy) state.error = 'Please agree to the privacy policy to book.';
    if (missing.length || !f.privacy) { render(); focusIn(list, '[data-error]'); return; }

    state.sending = true; state.error = ''; render();
    fetch(M.bookingUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({
        items: items(), rate: state.rate, checkin: state.checkin, nights: state.nights,
        adults: state.adults, children: state.kids, children_ages: state.ages.slice(0, state.kids),
        first_name: f.first_name.trim(), last_name: f.last_name.trim(), email: f.email.trim(),
        phone: f.phone.trim(),
        arrival: f.arrival || null, notes: f.notes || null, privacy: true
      })
    }).then(function (res) {
      return res.json().catch(function () { return {}; }).then(function (data) { return { ok: res.ok, status: res.status, data: data }; });
    }).then(function (r) {
      state.sending = false;
      if (r.ok) {
        state.result = r.data; state.step = 'done'; state.form = {};
      } else if (r.status === 422 && r.data.errors) {
        state.error = Object.keys(r.data.errors).map(function (k) { return r.data.errors[k][0]; }).join(' ');
      } else if (r.status === 419) {
        state.error = 'Your session expired. Please refresh the page and try again.';
      } else {
        state.error = 'Something went wrong on our side. Please try again, or message us on WhatsApp.';
      }
      render(); focusIn(list, state.step === 'done' ? 'button' : '[data-error]');
    }).catch(function () {
      state.sending = false;
      state.error = 'No connection. Please try again, or message us on WhatsApp.';
      render(); focusIn(list, '[data-error]');
    });
  });

  render();
  if (preRoom && BY_ID[preRoom]) {
    var pre = document.getElementById('room-' + preRoom);
    if (pre) pre.scrollIntoView({ block: 'start' });
  }
})();
