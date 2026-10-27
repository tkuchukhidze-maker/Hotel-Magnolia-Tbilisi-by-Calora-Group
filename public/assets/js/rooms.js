/* Magnolia Hotel — Rooms & booking flow
   Guests and nights filter the rooms; choosing a room opens an inline panel:
   1 rate → 2 guest details → 3 confirmation.
   Room data and the booking URL come from Laravel (window.MAGNOLIA, see rooms.blade.php).
   The server recalculates the price; this file only shows it. */
(function () {
  'use strict';

  var M = window.MAGNOLIA || {};
  var ROOMS = M.rooms || [];
  var MAX_GUESTS = M.maxGuests || 3;
  var MAX_NIGHTS = M.maxNights || 30;
  var CSRF = (document.querySelector('meta[name="csrf-token"]') || {}).content;

  var params = new URLSearchParams(window.location.search);
  var state = {
    adults: clamp(parseInt(params.get('adults'), 10) || 2, 1, MAX_GUESTS),
    kids: clamp(parseInt(params.get('children'), 10) || 0, 0, 2),
    nights: 2,
    checkin: params.get('checkin') || '',
    openId: params.get('room') || null,
    rate: 'room',
    step: 'choose',
    sending: false,
    error: '',
    result: null,
    form: {}
  };
  if (state.adults + state.kids > MAX_GUESTS) state.kids = Math.max(0, MAX_GUESTS - state.adults);
  var checkout = params.get('checkout');
  if (state.checkin && checkout) {
    var n = Math.round((new Date(checkout) - new Date(state.checkin)) / 86400000);
    if (n > 0) state.nights = clamp(n, 1, MAX_NIGHTS);
  }

  var list = document.getElementById('room-list');
  var summaryEl = document.getElementById('search-summary');
  var checkinEl = document.getElementById('checkin');
  var mobilePrice = document.querySelector('[data-mobile-price]');
  if (!list) return;

  function clamp(v, a, b) { return Math.max(a, Math.min(b, v)); }
  function gel(n) { return 'GEL ' + n; }
  function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function plural(n, one, many) { return n + ' ' + (n === 1 ? one : many); }
  function guests() { return state.adults + state.kids; }
  function guestsLabel() { return plural(state.adults, 'adult', 'adults') + (state.kids ? ', ' + plural(state.kids, 'child', 'children') : ''); }
  function nightsLabel() { return plural(state.nights, 'night', 'nights'); }
  function prices(r) { return guests() === 1 ? { p: r.p1, was: r.w1 } : { p: r.p2, was: r.w2 }; }
  function fmtDate(iso) {
    if (!iso) return '';
    var d = new Date(iso + 'T00:00:00');
    return d.toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short' });
  }
  function today() { return new Date().toISOString().slice(0, 10); }

  var ICON = {
    guests: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>',
    size: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16v16H4z"/><path d="M4 9h5V4"/></svg>',
    bed: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 18V8M3 14h18v4M21 14v-2a3 3 0 0 0-3-3h-7v5"/></svg>',
    plus: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v18M3 12h18"/></svg>',
    check: '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5L20 7"/></svg>'
  };

  function media(r) {
    var img = r.img
      ? '<img src="' + esc(r.img) + '" alt="' + esc(r.alt) + '" loading="lazy" width="920" height="600">'
      : '<div class="room__placeholder">[Room photos coming soon]</div>';
    return '<div class="room__media">' + img +
      '<span class="tag tag--deal">−25%</span>' +
      (r.left ? '<span class="tag tag--left">' + esc(r.left) + '</span>' : '') + '</div>';
  }

  function stayLine(r) {
    var dates = state.checkin ? fmtDate(state.checkin) + ' · ' : '';
    return esc(r.name) + ' · ' + (state.rate === 'bf' ? 'with breakfast' : 'room only') + ' · ' + dates + nightsLabel() + ' · ' + guestsLabel();
  }

  function panel(r) {
    var pr = prices(r);
    var nightly = state.rate === 'bf' ? pr.p[1] : pr.p[0];
    var total = nightly * state.nights;
    var stepIdx = { choose: 1, details: 2, done: 3 }[state.step];
    var steps = ['Room', 'Your details', 'Done'].map(function (s, i) {
      return '<li class="' + (stepIdx >= i + 1 ? 'is-active' : '') + '"' + (stepIdx === i + 1 ? ' aria-current="step"' : '') + '>' + (i + 1) + ' · ' + s + '</li>';
    }).join('');
    var f = state.form;
    var body = '';

    if (state.step === 'choose') {
      body =
        '<div role="group" aria-label="Choose your rate" class="stack" style="gap:10px">' +
          '<button type="button" class="rate" data-action="rate" data-rate="room" aria-pressed="' + (state.rate === 'room') + '"><span><strong>Room only</strong><small>Free cancellation · pay at the hotel</small></span><strong>' + gel(pr.p[0]) + '</strong></button>' +
          '<button type="button" class="rate" data-action="rate" data-rate="bf" aria-pressed="' + (state.rate === 'bf') + '"><span><strong>With buffet breakfast</strong><small>+' + gel(pr.p[1] - pr.p[0]) + ' per night · served every morning</small></span><strong>' + gel(pr.p[1]) + '</strong></button>' +
        '</div>' +
        '<div class="summary">' +
          '<div><span>' + gel(nightly) + ' × ' + nightsLabel() + '</span><span>' + gel(total) + '</span></div>' +
          '<div class="muted"><span>Check-in</span><span>' + (state.checkin ? fmtDate(state.checkin) : 'choose above') + '</span></div>' +
          '<div class="muted"><span>Guests</span><span>' + guestsLabel() + '</span></div>' +
          '<div class="muted"><span>Taxes &amp; fees</span><span>Included</span></div>' +
          '<div class="total"><strong>Total</strong><strong>' + gel(total) + '</strong></div>' +
          '<p class="ok-note">You pay nothing now. Cancellation: ' + esc((M.cancellation || '').toLowerCase()) + '.</p>' +
        '</div>' +
        '<button type="button" class="btn btn--primary btn--block" data-action="next">Continue · ' + gel(total) + '</button>';
    } else if (state.step === 'details') {
      body =
        '<form class="stack" style="gap:12px" data-details novalidate>' +
          '<p class="small">' + stayLine(r) + ' · <strong style="color:var(--cocoa)">' + gel(total) + '</strong></p>' +
          (state.checkin ? '' :
            '<label class="input">Check-in date<input name="checkin" type="date" min="' + today() + '" required></label>') +
          '<p class="list-title" style="margin:4px 0 0">Your details</p>' +
          '<div class="form-grid">' +
            '<label class="input">First name *<input name="first_name" type="text" autocomplete="given-name" required maxlength="80" value="' + esc(f.first_name) + '"></label>' +
            '<label class="input">Last name *<input name="last_name" type="text" autocomplete="family-name" required maxlength="80" value="' + esc(f.last_name) + '"></label>' +
          '</div>' +
          '<div class="form-grid">' +
            '<label class="input">Email *<input name="email" type="email" autocomplete="email" required maxlength="160" value="' + esc(f.email) + '"></label>' +
            '<label class="input">Phone / WhatsApp *<input name="phone" type="tel" autocomplete="tel" required placeholder="+995 5XX XX XX XX" maxlength="40" value="' + esc(f.phone) + '"></label>' +
          '</div>' +
          '<div class="form-grid">' +
            '<label class="input">Country *<input name="country" type="text" autocomplete="country-name" list="countries" required maxlength="80" value="' + esc(f.country) + '"></label>' +
            '<label class="input">Arrival time<select name="arrival">' + ['I don\'t know yet', 'Morning', 'Afternoon', 'Evening', 'Late night'].map(function (o) { return '<option' + (f.arrival === o ? ' selected' : '') + '>' + o + '</option>'; }).join('') + '</select></label>' +
          '</div>' +
          '<datalist id="countries">' + ['Georgia', 'Armenia', 'Azerbaijan', 'Turkey', 'Israel', 'Russia', 'Ukraine', 'Kazakhstan', 'Germany', 'Poland', 'United Kingdom', 'France', 'Italy', 'Spain', 'Netherlands', 'United States', 'Saudi Arabia', 'United Arab Emirates', 'Iran', 'India', 'China'].map(function (c) { return '<option value="' + c + '">'; }).join('') + '</datalist>' +
          '<label class="input">Anything we should know? (optional)<textarea name="notes" rows="2" maxlength="1000" placeholder="Late arrival, a baby cot, a quiet room…">' + esc(f.notes) + '</textarea></label>' +
          '<label style="display:flex;gap:10px;align-items:flex-start;font-size:14px;line-height:1.5;cursor:pointer"><input name="privacy" type="checkbox" required style="width:20px;height:20px;margin-top:1px;accent-color:var(--terracotta)"' + (f.privacy ? ' checked' : '') + '><span>I agree that Magnolia Hotel uses my details to handle this booking, as described in the <a href="' + esc(M.privacyUrl) + '" target="_blank" rel="noopener">privacy policy</a>. *</span></label>' +
          '<p class="small" data-error role="alert"' + (state.error ? '' : ' hidden') + ' style="color:var(--terracotta);font-weight:700">' + esc(state.error) + '</p>' +
          '<div style="display:flex;gap:10px;flex-wrap:wrap">' +
            '<button type="button" class="btn btn--outline" data-action="back">← Back</button>' +
            '<button type="submit" class="btn btn--primary" style="flex-grow:1"' + (state.sending ? ' disabled aria-busy="true"' : '') + '>' + (state.sending ? 'Sending…' : 'Confirm booking') + '</button>' +
          '</div>' +
          '<p class="small">No card needed. You\'ll pay at the hotel.</p>' +
        '</form>';
    } else {
      var res = state.result || {};
      body =
        '<div class="stack" role="status" style="gap:14px;align-items:flex-start">' +
          '<span class="done-icon">' + ICON.check + '</span>' +
          '<h3 class="h3">You\'re booked. <em>See you in Tbilisi.</em></h3>' +
          '<p class="lead" style="font-size:15px">' + esc(r.name) + ' · ' + fmtDate(res.checkin) + ' → ' + fmtDate(res.checkout) + ' · ' + guestsLabel() +
            '<br>Total ' + gel(res.total) + ', paid at the hotel. Booking ref <strong>' + esc(res.reference) + '</strong>.</p>' +
          '<p class="small">We\'ll confirm by email or WhatsApp shortly. Questions? <a href="' + esc(M.whatsapp) + '">WhatsApp us</a>.</p>' +
          '<button type="button" class="btn btn--outline" data-action="close">Back to rooms</button>' +
        '</div>';
    }

    var thumbs = (r.img ? '<img src="' + esc(r.img) + '" alt="" loading="lazy">' : '<div class="ph">[photo]</div>') +
      '<img src="' + esc((M.img || {}).bathroom) + '" alt="Private bathroom with patterned tiles" loading="lazy">' +
      '<img src="' + esc((M.img || {}).tray) + '" alt="Welcome tea and fruit" loading="lazy">';

    return '<div class="panel" id="panel-' + r.id + '">' +
      '<div class="panel__left">' +
        '<div class="thumbs">' + thumbs + '</div>' +
        '<p class="list-title">In your room</p>' +
        '<ul class="checks"><li>' + esc(r.bed) + '</li><li>' + esc(r.size) + ', ' + esc(String(r.extra).toLowerCase()) + '</li><li>Air conditioning</li><li>Private bathroom, shower</li><li>Flat-screen TV</li><li>Free Wi‑Fi</li><li>City &amp; garden view</li><li>Non-smoking</li></ul>' +
      '</div>' +
      '<div class="panel__right"><ol class="steps" aria-label="Booking steps">' + steps + '</ol>' + body + '</div>' +
    '</div>';
  }

  function setText(sel, txt) { var el = document.querySelector(sel); if (el) el.textContent = txt; }
  function setDisabled(sel, v) { var el = document.querySelector(sel); if (el) el.disabled = v; }

  function render() {
    var visible = ROOMS.filter(function (r) { return r.cap >= guests(); });
    if (!visible.some(function (r) { return r.id === state.openId; })) state.openId = null;
    var hidden = ROOMS.length - visible.length;

    setText('[data-value="adults"]', state.adults);
    setText('[data-value="kids"]', state.kids);
    setText('[data-value="nights"]', nightsLabel());
    setDisabled('[data-action="adults-"]', state.adults <= 1);
    setDisabled('[data-action="adults+"]', guests() >= MAX_GUESTS);
    setDisabled('[data-action="kids-"]', state.kids <= 0);
    setDisabled('[data-action="kids+"]', guests() >= MAX_GUESTS || state.kids >= 2);
    setDisabled('[data-action="nights-"]', state.nights <= 1);
    setDisabled('[data-action="nights+"]', state.nights >= MAX_NIGHTS);
    if (summaryEl) summaryEl.textContent = plural(visible.length, 'room fits', 'rooms fit') + ' ' + guestsLabel() + ' · prices for ' + nightsLabel();

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
      html += '<div class="hint-box"><span>' + plural(hidden, 'room is', 'rooms are') + ' too small for ' + guestsLabel() + '. Travelling as a bigger group? We\'ll put you in rooms next to each other.</span><a href="' + esc(M.whatsapp) + '" class="link-arrow">Ask us on WhatsApp →</a></div>';
    }
    list.innerHTML = html;

    if (mobilePrice && visible.length) {
      mobilePrice.textContent = gel(Math.min.apply(null, visible.map(function (r) { return prices(r).p[0]; })));
    }
  }

  function focusPanel(sel) {
    var el = document.getElementById('room-' + state.openId);
    if (!el) return;
    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    var first = el.querySelector(sel || '.panel button, .panel input');
    if (first) setTimeout(function () { first.focus({ preventScroll: true }); }, 350);
  }

  function rememberForm() {
    var form = list.querySelector('[data-details]');
    if (!form) return;
    ['first_name', 'last_name', 'email', 'phone', 'country', 'arrival', 'notes'].forEach(function (k) {
      var el = form.elements.namedItem(k);
      if (el) state.form[k] = el.value;
    });
    var p = form.elements.namedItem('privacy');
    if (p) state.form.privacy = p.checked;
  }

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-action]');
    if (!btn) return;
    var a = btn.getAttribute('data-action');
    if (a === 'adults+' && guests() < MAX_GUESTS) state.adults++;
    else if (a === 'adults-' && state.adults > 1) state.adults--;
    else if (a === 'kids+' && guests() < MAX_GUESTS && state.kids < 2) state.kids++;
    else if (a === 'kids-' && state.kids > 0) state.kids--;
    else if (a === 'nights+' && state.nights < MAX_NIGHTS) state.nights++;
    else if (a === 'nights-' && state.nights > 1) state.nights--;
    else if (a === 'open') { state.openId = btn.getAttribute('data-id'); state.step = 'choose'; state.rate = 'room'; state.error = ''; render(); focusPanel(); return; }
    else if (a === 'close') { var id = state.openId; state.openId = null; state.step = 'choose'; render(); var b = document.querySelector('[data-id="' + id + '"]'); if (b) b.focus(); return; }
    else if (a === 'rate') state.rate = btn.getAttribute('data-rate');
    else if (a === 'next') { state.step = 'details'; state.error = ''; render(); focusPanel('.panel input'); return; }
    else if (a === 'back') { rememberForm(); state.step = 'choose'; }
    else return;
    render();
  });

  if (checkinEl) {
    checkinEl.min = today();
    if (state.checkin) checkinEl.value = state.checkin;
    checkinEl.addEventListener('change', function () { state.checkin = checkinEl.value; render(); });
  }

  document.addEventListener('submit', function (e) {
    var form = e.target.closest('[data-details]');
    if (!form || state.sending) return;
    e.preventDefault();
    rememberForm();
    var el = form.elements;
    var inlineCheckin = el.namedItem('checkin');
    if (inlineCheckin && inlineCheckin.value) {
      state.checkin = inlineCheckin.value;
      if (checkinEl) checkinEl.value = state.checkin;
    }
    var f = state.form;
    var missing = [];
    if (!state.checkin) missing.push('check-in date');
    if (!(f.first_name || '').trim()) missing.push('first name');
    if (!(f.last_name || '').trim()) missing.push('last name');
    if (!/^\S+@\S+\.\S+$/.test((f.email || '').trim())) missing.push('a valid email');
    if (!/^[0-9+()\-\s]{6,}$/.test((f.phone || '').trim())) missing.push('a phone number');
    if (!(f.country || '').trim()) missing.push('country');
    if (missing.length) {
      state.error = 'Please add your ' + missing.join(', ') + '.';
      render(); focusPanel('[data-error]');
      return;
    }
    if (!f.privacy) {
      state.error = 'Please agree to the privacy policy to book.';
      render(); focusPanel('[data-error]');
      return;
    }

    state.sending = true; state.error = ''; render();
    fetch(M.bookingUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({
        room: state.openId, rate: state.rate, checkin: state.checkin, nights: state.nights,
        adults: state.adults, children: state.kids,
        first_name: f.first_name.trim(), last_name: f.last_name.trim(), email: f.email.trim(),
        phone: f.phone.trim(), country: f.country.trim(),
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
      render(); focusPanel(state.step === 'done' ? '.panel button' : '[data-error]');
    }).catch(function () {
      state.sending = false;
      state.error = 'No connection. Please try again, or message us on WhatsApp.';
      render(); focusPanel('[data-error]');
    });
  });

  render();
  if (state.openId) focusPanel();
})();
