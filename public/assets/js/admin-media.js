/* Magnolia Management — photo uploads.
   Photos are scaled down in the browser (max 2400 px, WebP) before uploading,
   so big phone photos upload fast and stay under server size limits. */
(function () {
  'use strict';

  var MAX = 2400;

  function shrink(file) {
    return new Promise(function (resolve) {
      if (!/^image\/(jpeg|png|webp)$/.test(file.type) || !window.createImageBitmap) return resolve(file);
      createImageBitmap(file).then(function (bmp) {
        var scale = Math.min(1, MAX / Math.max(bmp.width, bmp.height));
        if (scale === 1 && file.size < 1.5 * 1024 * 1024) return resolve(file);
        var c = document.createElement('canvas');
        c.width = Math.round(bmp.width * scale); c.height = Math.round(bmp.height * scale);
        c.getContext('2d').drawImage(bmp, 0, 0, c.width, c.height);
        c.toBlob(function (blob) {
          if (!blob || blob.size >= file.size) return resolve(file);
          resolve(new File([blob], file.name.replace(/\.\w+$/, '') + '.webp', { type: 'image/webp' }));
        }, 'image/webp', 0.85);
      }).catch(function () { resolve(file); });
    });
  }

  function shrinkInput(input) {
    var files = Array.prototype.slice.call(input.files || []);
    if (!files.length || !window.DataTransfer) return Promise.resolve();
    return Promise.all(files.map(shrink)).then(function (out) {
      var dt = new DataTransfer();
      out.forEach(function (f) { dt.items.add(f); });
      input.files = dt.files;
    });
  }

  document.querySelectorAll('form[data-upload]').forEach(function (form) {
    var ready = false;
    form.addEventListener('submit', function (e) {
      if (ready) return;
      var inputs = form.querySelectorAll('input[type=file]');
      if (!Array.prototype.some.call(inputs, function (i) { return i.files && i.files.length; })) return;
      e.preventDefault();
      var status = form.querySelector('[data-upload-status]');
      var btn = form.querySelector('button:not([type=button])');
      if (btn) { btn.disabled = true; btn.textContent = 'Uploading…'; }
      if (status) status.textContent = 'Preparing photos…';
      Promise.all(Array.prototype.map.call(inputs, shrinkInput)).then(function () {
        ready = true;
        if (status) status.textContent = 'Uploading…';
        form.submit();
      });
    });
  });

  // Gallery: choosing or dropping files uploads them straight away.
  document.querySelectorAll('[data-autosubmit]').forEach(function (input) {
    input.addEventListener('change', function () {
      if (input.files && input.files.length) {
        var f = input.form;
        if (f.requestSubmit) f.requestSubmit(); else f.dispatchEvent(new Event('submit', { cancelable: true }));
      }
    });
  });
  document.querySelectorAll('[data-drop]').forEach(function (zone) {
    var input = zone.querySelector('input[type=file]');
    ['dragenter', 'dragover'].forEach(function (t) { zone.addEventListener(t, function (e) { e.preventDefault(); zone.classList.add('is-over'); }); });
    ['dragleave', 'drop'].forEach(function (t) { zone.addEventListener(t, function () { zone.classList.remove('is-over'); }); });
    zone.addEventListener('drop', function (e) {
      e.preventDefault();
      if (!e.dataTransfer || !e.dataTransfer.files.length) return;
      input.files = e.dataTransfer.files;
      input.dispatchEvent(new Event('change'));
    });
  });

  // Show a preview as soon as a new photo is chosen for a card.
  document.querySelectorAll('.ph-card input[type=file]').forEach(function (input) {
    input.addEventListener('change', function () {
      var img = input.closest('.ph-card').querySelector('.ph-img img');
      if (img && input.files && input.files[0]) img.src = URL.createObjectURL(input.files[0]);
    });
  });

  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) { if (!window.confirm(form.getAttribute('data-confirm'))) e.preventDefault(); });
  });
})();
