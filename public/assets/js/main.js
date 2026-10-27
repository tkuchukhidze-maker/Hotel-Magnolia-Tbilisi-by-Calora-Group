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
    var iso = function (d) { return [d.getFullYear(), String(d.getMonth() + 1).padStart(2, '0'), String(d.getDate()).padStart(2, '0')].join('-'); };
    if (inEl && !inEl.value) { inEl.min = iso(today); }
    if (inEl && outEl) {
      inEl.addEventListener('change', function () {
        if (!inEl.value) return;
        var next = new Date(inEl.value + 'T12:00:00');
        next.setDate(next.getDate() + 1);
        outEl.min = iso(next);
        if (!outEl.value || outEl.value <= inEl.value) outEl.value = iso(next);
      });
      homeForm.addEventListener('submit', function (e) {
        if (!inEl.value || !outEl.value || outEl.value <= inEl.value) {
          e.preventDefault();
          if (!inEl.value) inEl.focus(); else outEl.focus();
          return;
        }
      });
    }
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
