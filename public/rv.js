/* Routerverse: perilaku halaman publik. Tanpa pustaka. */
(function () {
  'use strict';

  /* Menu mobile */
  var btn = document.getElementById('menu-btn');
  var nav = document.getElementById('site-nav');
  function setMenu(open) {
    if (!btn || !nav) return;
    nav.classList.toggle('is-open', open);
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    btn.textContent = open ? 'Tutup' : 'Menu';
  }
  if (btn && nav) {
    btn.addEventListener('click', function () { setMenu(!nav.classList.contains('is-open')); });
    nav.addEventListener('click', function (e) { if (e.target.closest('a')) setMenu(false); });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && nav.classList.contains('is-open')) { setMenu(false); btn.focus(); }
    });
  }

  /* Konfirmasi sebelum aksi yang menghapus */
  document.addEventListener('submit', function (e) {
    var msg = e.target.getAttribute('data-confirm');
    if (msg && !window.confirm(msg)) e.preventDefault();
  });

  /* Tombol kirim: tampilkan keadaan memuat, cegah kirim ganda */
  document.addEventListener('submit', function (e) {
    if (e.defaultPrevented) return;
    var f = e.target;
    var b = f.querySelector('button[type="submit"][data-busy]');
    if (!b) return;
    f.setAttribute('aria-busy', 'true');
    setTimeout(function () { b.disabled = true; b.textContent = b.getAttribute('data-busy'); }, 0);
  });

  /* Tampilkan atau sembunyikan password */
  document.querySelectorAll('[data-toggle-pw]').forEach(function (t) {
    t.addEventListener('click', function () {
      var input = document.getElementById(t.getAttribute('data-toggle-pw'));
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      t.textContent = show ? 'Sembunyikan' : 'Tampilkan';
      t.setAttribute('aria-pressed', show ? 'true' : 'false');
    });
  });

  /* Galeri produk: ganti gambar utama */
  var main = document.getElementById('gallery-main-img');
  document.querySelectorAll('[data-gallery-src]').forEach(function (b) {
    b.addEventListener('click', function () {
      if (!main) return;
      main.src = b.getAttribute('data-gallery-src');
      main.alt = b.getAttribute('data-gallery-alt') || '';
      document.querySelectorAll('[data-gallery-src]').forEach(function (o) { o.setAttribute('aria-current', 'false'); });
      b.setAttribute('aria-current', 'true');
    });
  });

  /* Dialog pembayaran */
  var dlg = document.getElementById('pay-dialog');
  if (dlg) {
    var wa = dlg.getAttribute('data-wa');
    var methods = (dlg.getAttribute('data-methods') || '').split(',').filter(Boolean);
    var state = { name: '', price: '', kind: 'paket', method: methods[0] || '' };
    var waLink = dlg.querySelector('[data-pay="wa"]');
    var tabs = dlg.querySelectorAll('[role="tab"]');

    var message = function () {
      var s = state;
      if (!s.method) return 'Halo Routerverse! Saya mau pesan ' + (s.kind === 'template' ? 'template ' : 'paket ') + s.name + ' (' + s.price + ').';
      var via = s.method === 'qris' ? 'QRIS' : 'transfer BCA';
      return s.kind === 'template'
        ? 'Halo, saya sudah bayar lewat ' + via + ' untuk template: ' + s.name + ' (' + s.price + '). Berikut bukti transfernya.'
        : 'Halo Routerverse! Saya sudah bayar paket ' + s.name + ' (' + s.price + ') lewat ' + via + '. Berikut bukti transfernya.';
    };
    var render = function () {
      dlg.querySelector('[data-pay="name"]').textContent = state.name;
      dlg.querySelector('[data-pay="price"]').textContent = state.price;
      tabs.forEach(function (t) { t.setAttribute('aria-selected', t.getAttribute('data-method') === state.method ? 'true' : 'false'); });
      dlg.querySelectorAll('[data-panel]').forEach(function (p) { p.hidden = p.getAttribute('data-panel') !== state.method; });
      waLink.href = 'https://wa.me/' + wa + '?text=' + encodeURIComponent(message());
    };

    document.querySelectorAll('[data-pay-open]').forEach(function (o) {
      o.addEventListener('click', function () {
        state.name = o.getAttribute('data-pay-name');
        state.price = o.getAttribute('data-pay-price');
        state.kind = o.getAttribute('data-pay-kind') || 'paket';
        var want = o.getAttribute('data-pay-tab');
        state.method = methods.indexOf(want) > -1 ? want : (methods[0] || '');
        render();
        if (typeof dlg.showModal === 'function') dlg.showModal(); else dlg.setAttribute('open', '');
      });
    });
    tabs.forEach(function (t) {
      t.addEventListener('click', function () { state.method = t.getAttribute('data-method'); render(); });
    });
    /* Klik di luar kotak menutup dialog */
    dlg.addEventListener('click', function (e) { if (e.target === dlg) dlg.close(); });

    var copyBtn = dlg.querySelector('[data-copy]');
    if (copyBtn) {
      copyBtn.addEventListener('click', function () {
        var text = document.getElementById(copyBtn.getAttribute('data-copy')).textContent.trim();
        var done = function (ok) {
          copyBtn.textContent = ok ? 'Tersalin' : 'Gagal menyalin, salin manual';
          setTimeout(function () { copyBtn.textContent = 'Salin'; }, 2000);
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(text).then(function () { done(true); }, function () { done(false); });
        } else { done(false); }
      });
    }
  }
})();
