/* Hotel Magnolia — shared behaviour: navigation, header, mobile booking bar, gallery filter */
(function () {
  'use strict';

  // Mobile navigation drawer
  var toggle = document.querySelector('[data-nav-open]');
  var close = document.querySelector('[data-nav-close]');
  var nav = document.getElementById('site-nav');
  function setNav(open) {
    if (!nav || !toggle) return;
    nav.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', String(open));
    document.body.style.overflow = open ? 'hidden' : '';
    if (open && close) close.focus();
    if (!open) toggle.focus();
  }
  if (toggle) toggle.addEventListener('click', function () { setNav(true); });
  if (close) close.addEventListener('click', function () { setNav(false); });
  if (nav) {
    nav.addEventListener('click', function (e) {
      if (e.target.closest('a') && nav.classList.contains('is-open')) setNav(false);
    });
  }
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && nav && nav.classList.contains('is-open')) setNav(false);
  });

  // Overlay header turns solid after scrolling past the top
  var header = document.querySelector('.site-header--overlay');
  var mobileBook = document.querySelector('.mobile-book');
  function onScroll() {
    var y = window.scrollY || window.pageYOffset;
    if (header) header.classList.toggle('is-scrolled', y > 80);
    if (mobileBook) mobileBook.classList.toggle('is-visible', y > 480);
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
  if (mobileBook) document.body.classList.add('has-mobile-book');

  // Home booking form: pass dates and guests to the rooms page
  var homeForm = document.querySelector('[data-home-booking]');
  if (homeForm) {
    var today = new Date();
    var inEl = homeForm.querySelector('[name="checkin"]');
    var outEl = homeForm.querySelector('[name="checkout"]');
    var iso = function (d) { return d.toISOString().slice(0, 10); };
    if (inEl && !inEl.value) { inEl.min = iso(today); }
    if (inEl && outEl) {
      inEl.addEventListener('change', function () {
        if (!inEl.value) return;
        var next = new Date(inEl.value);
        next.setDate(next.getDate() + 1);
        outEl.min = iso(next);
        if (!outEl.value || outEl.value <= inEl.value) outEl.value = iso(next);
      });
    }
  }

  // Offer countdowns ([data-countdown]). Time left = server deadline − server time,
  // so a wrong clock on the guest's phone can't change it. Ticks on the second.
  var timers = document.querySelectorAll('[data-countdown]');
  if (timers.length) {
    var pad = function (n) { return (n < 10 ? '0' : '') + n; };
    var skew = 0;
    var first = timers[0];
    var serverNow = parseInt(first.getAttribute('data-now'), 10);
    if (serverNow) skew = serverNow - Date.now();
    var startedLive = false;
    var tick = function () {
      var now = Date.now() + skew;
      var anyLive = false;
      timers.forEach(function (el) {
        var ends = parseInt(el.getAttribute('data-ends'), 10);
        if (!ends) return;
        var left = Math.max(0, Math.floor((ends - now) / 1000));
        var d = Math.floor(left / 86400), h = Math.floor(left % 86400 / 3600), m = Math.floor(left % 3600 / 60), s = left % 60;
        if (left > 0) anyLive = true;
        var set = function (k, v) { var b = el.querySelector('[data-cd="' + k + '"]'); if (b && b.textContent !== v) b.textContent = v; };
        set('d', pad(d)); set('h', pad(h)); set('m', pad(m)); set('s', pad(s));
        var compact = el.querySelector('[data-cd-compact]');
        if (compact) compact.textContent = (d ? d + 'd ' : '') + pad(h) + ':' + pad(m) + ':' + pad(s);
        if (left === 0) {
          el.classList.add('is-over');
          var over = el.querySelector('.countdown__over');
          if (over) over.hidden = false;
        }
      });
      if (anyLive) startedLive = true;
      if (!anyLive) {
        // Ended while the page was open: reload once so prices show the full rate again.
        if (startedLive) setTimeout(function () { window.location.reload(); }, 1500);
        return;
      }
      setTimeout(tick, 1000 - (now % 1000) + 20);
    };
    tick();
  }

  // Gallery filter (Our Hotel page)
  var chips = document.querySelectorAll('[data-filter]');
  var items = document.querySelectorAll('[data-cat]');
  chips.forEach(function (chip) {
    chip.addEventListener('click', function () {
      var f = chip.getAttribute('data-filter');
      chips.forEach(function (c) { c.setAttribute('aria-pressed', String(c === chip)); });
      items.forEach(function (item) {
        var cats = item.getAttribute('data-cat').split(' ');
        item.hidden = !(f === 'all' || cats.indexOf(f) > -1);
      });
    });
  });
})();
