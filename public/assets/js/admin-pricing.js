/* Magnolia Management — pricing calendar selection & bulk edit.
   Drag with the mouse (or a finger) to pick nights. Ctrl/⌘-drag adds or removes,
   Shift-click extends from the last night, a weekday name toggles every such day.
   The server works out the final prices; this file only previews them. */
(function () {
  'use strict';

  var cal = document.getElementById('pc-cal');
  var form = document.getElementById('pc-form');
  if (!cal || !form) return;
  var PC = window.PC || {};

  var cells = Array.prototype.slice.call(cal.querySelectorAll('.pc-day'));
  var selectable = cells.filter(function (c) { return c.getAttribute('data-past') !== '1'; });
  var sel = new Set();
  var panelVisible = false;
  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (en) { panelVisible = en[0].isIntersecting; var fl = document.getElementById('pc-float'); if (fl) fl.hidden = !sel.size || panelVisible; })
      .observe(document.getElementById('pc-form'));
  }
  var anchor = null;

  var countEl = document.getElementById('pc-count');
  var applyBtn = document.getElementById('pc-apply');
  var resetBtn = document.getElementById('pc-reset');
  var modeInput = document.getElementById('pc-mode');
  var oneIn = document.getElementById('pc-one');
  var twoIn = document.getElementById('pc-two');
  var valIn = document.getElementById('pc-value');
  var valLabel = document.getElementById('pc-value-label');
  var hint = document.getElementById('pc-hint');
  var datesBox = document.getElementById('pc-dates');
  var fromIn = document.getElementById('pc-from');
  var toIn = document.getElementById('pc-to');

  function idx(c) { return cells.indexOf(c); }
  function cellAt(x, y) { var el = document.elementFromPoint(x, y); return el && el.closest ? el.closest('.pc-day') : null; }
  function isPast(c) { return c.getAttribute('data-past') === '1'; }
  function fmt(iso) { var d = new Date(iso + 'T12:00:00'); return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' }); }
  function rangeTyped() { return fromIn && toIn && fromIn.value && toIn.value; }

  /* ---------- Selection ---------- */
  function paint() {
    cells.forEach(function (c) {
      var on = sel.has(c.getAttribute('data-date'));
      c.classList.toggle('is-sel', on);
      c.setAttribute('aria-selected', on ? 'true' : 'false');
    });
    var dates = Array.from(sel).sort();
    var n = dates.length;
    if (rangeTyped()) {
      countEl.innerHTML = 'Using the typed range: <strong>' + fmt(fromIn.value) + ' – ' + fmt(toIn.value) + '</strong>';
    } else if (!n) {
      countEl.textContent = 'Pick nights on the calendar.';
    } else {
      countEl.innerHTML = '<strong>' + n + ' ' + (n === 1 ? 'night' : 'nights') + '</strong> selected · ' +
        (n === 1 ? fmt(dates[0]) : fmt(dates[0]) + ' – ' + fmt(dates[n - 1]));
    }
    var ready = n > 0 || rangeTyped();
    applyBtn.disabled = !ready;
    resetBtn.disabled = !ready;
    applyBtn.textContent = ready ? 'Apply to ' + (rangeTyped() ? 'range' : n + ' ' + (n === 1 ? 'night' : 'nights')) : 'Apply';
    preview();
    try { sessionStorage.setItem(PC.key, JSON.stringify(dates)); } catch (e) {}
    var fl = document.getElementById('pc-float');
    if (fl) {
      fl.hidden = !n || panelVisible;
      document.getElementById('pc-float-text').textContent = n + ' ' + (n === 1 ? 'night' : 'nights') + ' selected';
    }
  }

  function setRange(a, b, add, base) {
    var lo = Math.min(a, b), hi = Math.max(a, b);
    sel = new Set(base);
    for (var i = lo; i <= hi; i++) {
      var c = cells[i];
      if (isPast(c)) continue;
      var d = c.getAttribute('data-date');
      if (add) sel.add(d); else sel.delete(d);
    }
  }

  var drag = null;
  cal.addEventListener('pointerdown', function (e) {
    var c = e.target.closest('.pc-day');
    if (!c || e.button > 0) return;
    if (e.pointerType === 'touch') {
      // On phones a tap toggles a night (so the page can still scroll); use the quick buttons for more.
      if (isPast(c)) return;
      var td = c.getAttribute('data-date');
      if (sel.has(td)) sel.delete(td); else sel.add(td);
      anchor = idx(c); paint();
      return;
    }
    e.preventDefault();
    var additive = e.ctrlKey || e.metaKey;
    var i = idx(c);
    if (e.shiftKey && anchor !== null) {
      setRange(anchor, i, true, sel);
      paint();
      return;
    }
    var base = additive ? new Set(sel) : new Set();
    var add = additive ? !sel.has(c.getAttribute('data-date')) : true;
    // A plain click on the only selected night clears it.
    if (!additive && sel.size === 1 && sel.has(c.getAttribute('data-date'))) add = false;
    drag = { start: i, base: base, add: add, id: e.pointerId };
    anchor = i;
    try { cal.setPointerCapture(e.pointerId); } catch (err) {}
    setRange(i, i, add, base);
    paint();
  });
  cal.addEventListener('pointermove', function (e) {
    if (!drag || e.pointerId !== drag.id) return;
    var c = cellAt(e.clientX, e.clientY);
    if (!c) return;
    setRange(drag.start, idx(c), drag.add, drag.base);
    paint();
  });
  function endDrag() { drag = null; }
  cal.addEventListener('pointerup', endDrag);
  cal.addEventListener('pointercancel', endDrag);

  // Weekday names: toggle every such (future) day.
  cal.querySelectorAll('.pc-wd').forEach(function (b) {
    b.addEventListener('click', function () {
      var dow = b.getAttribute('data-dow');
      var days = selectable.filter(function (c) { return c.getAttribute('data-dow') === dow; });
      var all = days.every(function (c) { return sel.has(c.getAttribute('data-date')); });
      days.forEach(function (c) { var d = c.getAttribute('data-date'); if (all) sel.delete(d); else sel.add(d); });
      paint();
    });
  });

  // Keyboard: Space/Enter toggles, arrows move, Shift+arrow extends, Esc clears, Ctrl+A all.
  cal.addEventListener('keydown', function (e) {
    var c = e.target.closest('.pc-day');
    if (!c) return;
    var i = idx(c), step = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7 }[e.key];
    if (e.key === ' ' || e.key === 'Enter') {
      e.preventDefault();
      if (isPast(c)) return;
      var d = c.getAttribute('data-date');
      if (sel.has(d)) sel.delete(d); else sel.add(d);
      anchor = i; paint();
    } else if (step) {
      e.preventDefault();
      var n = cells[Math.max(0, Math.min(cells.length - 1, i + step))];
      n.focus();
      if (e.shiftKey && !isPast(n)) { sel.add(n.getAttribute('data-date')); paint(); }
    } else if (e.key === 'Escape') {
      sel.clear(); paint();
    } else if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'a') {
      e.preventDefault(); selectable.forEach(function (x) { sel.add(x.getAttribute('data-date')); }); paint();
    }
  });

  document.querySelectorAll('[data-pick]').forEach(function (b) {
    b.addEventListener('click', function () {
      var what = b.getAttribute('data-pick');
      var pick = {
        all: function () { return true; },
        weekdays: function (c) { return ['7', '1', '2', '3', '4'].indexOf(c.getAttribute('data-dow')) > -1; },
        weekends: function (c) { return ['5', '6'].indexOf(c.getAttribute('data-dow')) > -1; },
        none: function () { return false; }
      }[what];
      sel = new Set(selectable.filter(pick).map(function (c) { return c.getAttribute('data-date'); }));
      paint();
    });
  });

  /* ---------- Mode & preview ---------- */
  var mode = 'set';
  form.querySelectorAll('[data-mode]').forEach(function (b) {
    b.addEventListener('click', function () {
      mode = b.getAttribute('data-mode');
      modeInput.value = mode;
      form.querySelectorAll('[data-mode]').forEach(function (x) { x.setAttribute('aria-pressed', String(x === b)); });
      form.querySelectorAll('[data-for]').forEach(function (box) { box.hidden = box.getAttribute('data-for').split(' ').indexOf(mode) < 0; });
      valLabel.textContent = mode === 'percent' ? 'Change by %' : 'Change by GEL';
      hint.textContent = mode === 'set'
        ? 'Leave a box empty to keep that price as it is.'
        : 'Positive raises, negative lowers (e.g. -10). Applied to each night\'s current price.';
      preview();
      (mode === 'set' ? twoIn : valIn).focus();
    });
  });

  function previewFor(one, two) {
    if (mode === 'set') {
      return [oneIn.value ? +oneIn.value : one, twoIn.value ? +twoIn.value : two];
    }
    var v = parseFloat(valIn.value);
    if (isNaN(v) || !v) return null;
    var f = mode === 'percent' ? function (p) { return Math.round(p * (1 + v / 100)); } : function (p) { return Math.round(p + v); };
    return [Math.max(1, f(one)), Math.max(1, f(two))];
  }

  function preview() {
    // Only meaningful for the room & plan on screen, and only if they're ticked.
    var showing = form.querySelector('input[name="rooms[]"][value="' + PC.room + '"]');
    var plan = form.querySelector('input[name="plans[]"][value="' + PC.plan + '"]');
    var on = showing && showing.checked && plan && plan.checked && !rangeTyped();
    cells.forEach(function (c) {
      var out = c.querySelector('.pc-new');
      if (!out) return;
      var p = on && sel.has(c.getAttribute('data-date')) ? previewFor(+c.getAttribute('data-one'), +c.getAttribute('data-two')) : null;
      var changed = p && (p[0] !== +c.getAttribute('data-one') || p[1] !== +c.getAttribute('data-two'));
      out.textContent = changed ? '→ ' + p[1] + ' / ' + p[0] : '';
    });
  }
  [oneIn, twoIn, valIn, fromIn, toIn].forEach(function (el) { if (el) el.addEventListener('input', paint); });
  form.addEventListener('change', function (e) { if (e.target.matches('input[type=checkbox]')) preview(); });

  /* ---------- Submit ---------- */
  function fillDates() {
    datesBox.innerHTML = '';
    if (rangeTyped()) return;
    Array.from(sel).sort().forEach(function (d) {
      var i = document.createElement('input');
      i.type = 'hidden'; i.name = 'dates[]'; i.value = d;
      datesBox.appendChild(i);
    });
  }
  form.addEventListener('submit', function () {
    if (modeInput.value !== 'reset') modeInput.value = mode;
    fillDates();
    applyBtn.disabled = true; resetBtn.disabled = true;
    applyBtn.textContent = 'Saving…';
  });
  resetBtn.addEventListener('click', function () {
    var n = rangeTyped() ? 'the typed range' : sel.size + ' ' + (sel.size === 1 ? 'night' : 'nights');
    if (!window.confirm('Remove special prices for ' + n + '? The month or default price will apply again.')) return;
    modeInput.value = 'reset';
    if (form.requestSubmit) form.requestSubmit(); else { fillDates(); form.submit(); }
  });

  // Keep the selection after saving, so you can see the result and tweak again.
  try {
    var saved = JSON.parse(sessionStorage.getItem(PC.key) || '[]');
    var valid = new Set(selectable.map(function (c) { return c.getAttribute('data-date'); }));
    saved.forEach(function (d) { if (valid.has(d)) sel.add(d); });
  } catch (e) {}
  paint();
})();
