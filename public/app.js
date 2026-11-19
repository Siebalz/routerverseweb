/* Routerverse: perilaku dashboard. Dialog pembayaran dan menu ada di rv.js. */
(function () {
  'use strict';

  /* Tab kategori layanan */
  var tabs = Array.prototype.slice.call(document.querySelectorAll('[data-cat-tab]'));
  function selectTab(tab, focus) {
    tabs.forEach(function (t) {
      var on = t === tab;
      t.setAttribute('aria-selected', on ? 'true' : 'false');
      t.tabIndex = on ? 0 : -1;
      document.getElementById(t.getAttribute('aria-controls')).hidden = !on;
    });
    if (focus) tab.focus();
    try { history.replaceState(null, '', '#' + tab.getAttribute('data-cat-tab')); } catch (e) {}
  }
  if (tabs.length) {
    var start = tabs.filter(function (t) { return location.hash === '#' + t.getAttribute('data-cat-tab'); })[0] || tabs[0];
    selectTab(start, false);
    tabs.forEach(function (t, i) {
      t.addEventListener('click', function () { selectTab(t, false); });
      t.addEventListener('keydown', function (e) {
        var n = null;
        if (e.key === 'ArrowRight') n = tabs[(i + 1) % tabs.length];
        if (e.key === 'ArrowLeft') n = tabs[(i - 1 + tabs.length) % tabs.length];
        if (e.key === 'Home') n = tabs[0];
        if (e.key === 'End') n = tabs[tabs.length - 1];
        if (n) { e.preventDefault(); selectTab(n, true); }
      });
    });
  }

  /* Dialog pesan layanan */
  var dlg = document.getElementById('order-dialog');
  if (dlg) {
    var set = function (id, v) { var el = document.getElementById(id); if (el.tagName === 'INPUT') el.value = v; else el.textContent = v; };
    document.querySelectorAll('[data-order-open]').forEach(function (b) {
      b.addEventListener('click', function () {
        set('order-category', b.getAttribute('data-category'));
        set('order-service', b.getAttribute('data-name'));
        set('order-price', b.getAttribute('data-price'));
        set('order-show-name', b.getAttribute('data-name'));
        set('order-show-price', b.getAttribute('data-price'));
        var f = dlg.querySelector('form'); f.removeAttribute('aria-busy');
        var s = f.querySelector('button[type="submit"]'); s.disabled = false; s.textContent = 'Kirim Pesanan';
        if (typeof dlg.showModal === 'function') dlg.showModal(); else dlg.setAttribute('open', '');
        document.getElementById('order-notes').focus();
      });
    });
    dlg.querySelectorAll('[data-close]').forEach(function (c) { c.addEventListener('click', function () { dlg.close(); }); });
    dlg.addEventListener('click', function (e) { if (e.target === dlg) dlg.close(); });
  }

  /* Pratinjau gambar QRIS sebelum disimpan */
  var qrisInput = document.getElementById('qris_image');
  var qrisPreview = document.getElementById('qris-preview');
  if (qrisInput && qrisPreview) {
    qrisInput.addEventListener('change', function () {
      var f = qrisInput.files[0]; if (!f) return;
      qrisPreview.src = URL.createObjectURL(f); qrisPreview.hidden = false;
    });
  }

  /* VPN Remote: total harga per paket, port tujuan hanya untuk paket 1 port */
  var vf = document.getElementById('vpn-form');
  if (vf) {
    var tot = document.getElementById('vpn-total');
    var choice = vf.querySelector('[data-port-choice]'), fixed = vf.querySelector('[data-port-fixed]');
    var box = vf.querySelector('[data-custom-port]'), cin = box.querySelector('input');
    var upd = function () {
      var pl = vf.querySelector('[data-plan-radio]:checked');
      var price = pl ? parseInt(pl.getAttribute('data-price'), 10) : 0;
      tot.textContent = 'Rp ' + price.toLocaleString('id-ID');
      var one = !!pl && pl.getAttribute('data-single') === '1';
      choice.hidden = !one; fixed.hidden = one;
      var c = vf.querySelector('[data-port-radio]:checked');
      var on = one && !!c && c.value === 'custom';
      box.hidden = !on; cin.required = on;
    };
    vf.querySelectorAll('[data-plan-radio], [data-port-radio]').forEach(function (r) { r.addEventListener('change', upd); });
    upd();
  }
  document.querySelectorAll('[data-copy-text]').forEach(function (b) {
    b.addEventListener('click', function () {
      var t = document.getElementById(b.getAttribute('data-copy-text')).textContent.trim();
      var done = function (ok) { b.textContent = ok ? 'Tersalin' : 'Salin manual'; setTimeout(function () { b.textContent = 'Salin'; }, 2000); };
      if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(t).then(function () { done(true); }, function () { done(false); });
      else done(false);
    });
  });

  /* Top up poin: hitung biaya dan total secara langsung (server tetap menghitung ulang) */
  var tf = document.getElementById('topup-form');
  if (tf) {
    var amt = document.getElementById('amount');
    var flat = parseInt(tf.getAttribute('data-fee-flat'), 10) || 0;
    var pct = parseFloat(tf.getAttribute('data-fee-percent')) || 0;
    var rp = function (n) { return 'Rp ' + n.toLocaleString('id-ID'); };
    var calc = function () {
      var n = parseInt(amt.value, 10) || 0;
      var fee = n > 0 ? flat + Math.ceil(n * pct / 100) : 0;
      document.getElementById('tp-points').textContent = rp(n);
      document.getElementById('tp-fee').textContent = rp(fee);
      document.getElementById('tp-total').innerHTML = '<strong>' + rp(n + fee) + '</strong>';
    };
    amt.addEventListener('input', calc);
    tf.querySelectorAll('[data-amount]').forEach(function (b) {
      b.addEventListener('click', function () { amt.value = b.getAttribute('data-amount'); calc(); amt.focus(); });
    });
    calc();
  }
})();
