# Rencana: poin web, bayar otomatis, top up (Duitku)

Status: CATATAN untuk dikerjakan nanti. Belum ada kode. Dibuat 2026-11-18.

## Tujuan dari pemilik
1. Setiap pelanggan punya **poin** (saldo) di web untuk membayar layanan.
2. Kalau bayar pakai poin, pesanan **langsung diproses** tanpa menunggu admin.
3. **Top up poin** lewat QRIS dan payment gateway **Duitku**.
4. (Dari contoh Tunnel ID) opsi "Auto renew dari saldo" saat order VPN.

## Yang sudah ada dan bisa dipakai ulang
- `VpnAccount::applyOrder($order)` mengaktifkan akun dan langsung sinkron ke MikroTik (`syncNow`). Tinggal dipanggil dari alur bayar poin, sama seperti saat admin menyimpan status.
- `OrderController::updateStatus` berisi logika aktivasi (activateForOneMonth, VPN, ProvisionOrderJob untuk Mikhmon). **Pindahkan ke satu service** (mis. `OrderActivator::activate($order)`) supaya admin, bayar poin, dan callback Duitku memakai jalur yang sama.
- Dialog pembayaran `components/payment-dialog.blade.php` sudah siap ditambah tab gateway. Status `.status-*` dan `.order-badge-*` sudah ada.
- Kolom `Order.status`: pending, diproses, selesai, dibatalkan, expired.

## Rancangan data (usulan)
- `users.points` (integer, Rupiah, 1 poin = Rp 1) **tidak disimpan sebagai angka tunggal**. Simpan buku besar:
  `point_ledgers`: id, user_id, type (topup | purchase | refund | adjust), amount (+/-), balance_after, order_id?, topup_id?, note, created_at.
  Saldo = jumlah ledger. `users.points` hanya cache yang diubah di transaksi yang sama.
- `topups`: id, user_id, amount, method (qris | duitku), status (pending | paid | expired | failed), merchant_order_id (unik), duitku_reference, payment_url, expires_at, paid_at.
- `orders`: tambah `paid_with` (points | manual | gateway), `paid_at`.
- `vpn_accounts`: tambah `auto_renew` (boolean).

## Alur bayar pakai poin
1. Pelanggan klik **Bayar dengan poin** di Pesanan Saya.
2. Dalam satu transaksi DB dengan kunci baris pada user (`lockForUpdate`): cek saldo cukup, catat ledger `purchase` (negatif), set order `paid_with=points`.
3. Panggil `OrderActivator::activate($order)` (status diproses, VPN tersinkron). Kalau aktivasi gagal, jangan hilangkan poin: biarkan order tetap dibayar, tampilkan galat ke admin, `vpn:sync` mengulang.
4. Saldo kurang: tawarkan tombol Top up dengan jumlah selisih.
- Harga ditentukan **di server** dari data paket, jangan percaya angka dari form.

## Alur top up
- **Duitku (otomatis):** buat `topups` pending, minta transaksi ke Duitku, arahkan pelanggan ke halaman bayar. Duitku memanggil **callback** ke server kita. Callback wajib: verifikasi **signature** sesuai dokumentasi Duitku, cek `merchantOrderId` ada dan masih pending, cocokkan **jumlah**, lalu dalam satu transaksi tandai paid + ledger `topup`. Harus **idempoten** (callback bisa datang berkali-kali). Tambah juga pengecekan status manual lewat endpoint cek transaksi Duitku sebagai cadangan.
- **QRIS statis (manual):** pelanggan upload bukti, admin konfirmasi, baru ledger `topup`. Dipertahankan sampai Duitku stabil.
- Jangan pasang kode gateway sebelum merchant code + API key sandbox ada. **Cek dokumentasi resmi Duitku** untuk nama endpoint, format signature, dan daftar metode pembayaran sebelum menulis kode (jangan mengandalkan ingatan).
- Simpan kunci di `.env` (`DUITKU_MERCHANT_CODE`, `DUITKU_API_KEY`, `DUITKU_ENV=sandbox|production`, `DUITKU_CALLBACK_URL`), bukan di database atau repo.
- Callback harus bisa dijangkau dari internet (HTTPS) dan **dikecualikan dari CSRF**.

## Auto renew dari poin
- `vpn_accounts.auto_renew`. Jalankan di `vpn:sync` (atau perintah baru) H-1 sebelum expired: kalau saldo cukup, potong poin dan perpanjang; kalau tidak, kirim pengingat (WA/email) dan biarkan expired.
- Jangan perpanjang dua kali untuk periode yang sama (pakai `applied_order_id` seperti sekarang).

## Risiko yang harus ditangani
- Race condition saldo: selalu `lockForUpdate` dan ledger append-only, tidak ada UPDATE manual pada ledger.
- Callback palsu: tolak bila signature salah, jangan pernah percaya status dari browser.
- Top up kedaluwarsa tapi dibayar belakangan: tetap diterima kalau Duitku mengabarkan lunas (cek `merchant_order_id`).
- Refund: catat ledger `refund`, jangan menghapus baris lama.
- Halaman admin untuk koreksi saldo (`adjust`) dengan catatan wajib.
- Uji dulu di sandbox Duitku, termasuk callback ganda dan jumlah tidak cocok.

## Urutan pengerjaan yang disarankan
1. Ekstrak `OrderActivator` dari `OrderController::updateStatus` (tanpa ubah perilaku).
2. Ledger + saldo + halaman "Poin Saya" (riwayat) + admin adjust.
3. Bayar pesanan dengan poin + aktivasi otomatis.
4. Top up QRIS manual (upload bukti, konfirmasi admin).
5. Top up Duitku sandbox (create + callback + cek status), lalu production.
6. Auto renew.

## Keputusan pemilik (2026-11-18)
1. **Minimum top up Rp 10.000.**
2. **Biaya gateway ditanggung pembeli.** Pelanggan membayar nominal + biaya, poin yang masuk = nominal. Tampilkan rincian sebelum membayar (nominal, biaya, total). Hitung biaya dari daftar metode/biaya milik Duitku (ambil dari API atau dokumentasi), jangan ditulis tetap di kode.
3. **Poin tidak kedaluwarsa dan tidak bisa ditarik kembali.** Wajib tertulis jelas di web: di halaman top up, di konfirmasi sebelum bayar, dan di syarat. Tidak ada fitur cairkan poin. Refund hanya berupa poin kembali (ledger `refund`).
4. **Semua pembayaran pesanan memakai poin, otomatis.** Alur bayar manual per pesanan (QRIS/BCA + konfirmasi admin) dihapus setelah poin berjalan. Tombol di Pesanan Saya menjadi "Bayar dengan poin". Saldo kurang: arahkan ke top up dengan selisih terisi.
5. **Jenis Duitku belum diketahui.** Cara cek: login ke dashboard Duitku, buka menu Proyek, lihat jenis integrasi dan API key proyek, atau tanya CS Duitku. Saat mulai, baca dokumentasi resmi Duitku dulu lalu pilih satu (POP atau API klasik). QRIS dipakai sebagai salah satu metode di Duitku, jadi QRIS statis manual tidak perlu dipertahankan.

Dampak ke rancangan di atas: langkah 4 (QRIS manual) dibuang. Urutan jadi: OrderActivator, ledger + Poin Saya, bayar dengan poin, top up Duitku sandbox, hapus bayar manual lama, auto renew. Perlu juga halaman syarat singkat soal poin.

## Sebelum mulai mengerjakan
- Punya akun Duitku (sandbox dulu): merchant code dan API key.
- Domain web memakai HTTPS dan bisa dijangkau Duitku untuk callback.

## Status pengerjaan (2026-11-19)
SELESAI (kode ada, belum dijalankan di Laravel asli):
- `OrderActivator` (satu jalur aktivasi; dipakai admin dan bayar poin).
- Tabel `point_ledgers`, kolom `users.points`, `orders.price/paid_with/paid_at` (migrasi `2026_11_19_000001_create_points.php`).
- `PointService` (credit, debit, payOrder dengan kunci baris, saldo tidak bisa negatif).
- Halaman **Poin Saya**, tombol **Bayar dengan Poin** di Pesanan Saya (langsung diproses), saldo di menu.
- Harga pesanan katalog kini ditentukan server (`amount` di katalog), bukan dari form.
- Sementara top up belum ada: `php artisan points:add email@pelanggan.com 50000 --note="Top up manual"`.
BELUM:
- **Top up Duitku (API klasik).** Dokumentasi resmi tidak bisa diakses dari sesi ini. Perlu teks dari docs.duitku.com/api: Request Transaction, Get Payment Method, Callback, Check Transaction (endpoint sandbox, rumus signature, field callback). Jangan menebak rumus signature.
- Auto renew dari poin, hapus bayar manual lama (masih dipakai untuk pesanan tanpa harga tetap, mis. Setting Jaringan "Mulai Rp 50.000").
- Mikhmon Online diprovision lewat antrean: butuh `php artisan queue:work` agar otomatis.

## Keputusan tambahan (2026-11-19): pakai Duitku POP
- Pemilik memilih **Duitku POP** (popup pembayaran, tanpa redirect) agar cepat dan mudah. Mengganti catatan "API klasik".
- Alur: server membuat transaksi POP (dapat `reference`), halaman top up membuka popup Duitku dengan reference itu, Duitku memanggil callback, saldo bertambah.
- Ada paket Laravel `duitku/laravel` (menurut halaman Packagist: `Duitku::pop()->createTransaction()`, komponen Blade `<x-duitku-pop>`, `Duitku::handleCallback()`, `Duitku::checkStatus()`, tanda tangan HMAC-SHA256 otomatis, `php artisan duitku:doctor --pop`). Rumus signature dan nama kelas/event TIDAK terverifikasi dari sesi ini.
- Untuk menulis kodenya dengan benar dibutuhkan salah satu: (a) teks dokumentasi POP resmi (docs.duitku.com/pop), atau (b) setelah `composer require duitku/laravel`, isi `vendor/duitku/laravel/docs/guide/usage-pop.md` dan `callback-system.md` (atau README paket).
- **Biaya ditanggung pembeli dengan POP:** metode bayar dipilih di dalam popup, jadi biayanya baru diketahui setelah itu. Perlu keputusan: biaya admin tetap (mis. Rp X per top up) atau persentase, ditambahkan ke total sebelum popup dibuka. Poin yang masuk tetap sebesar nominal.
- Callback wajib: verifikasi signature, cocokkan `merchantOrderId` dan jumlah, idempoten, kecualikan route dari CSRF, HTTPS.

## Status (2026-11-19, sore): top up Duitku POP SELESAI ditulis
Kode: `config/duitku.php`, `app/Services/DuitkuPop.php`, `DuitkuCallbackController`, `PointController@topup`, migrasi `2026_11_19_000002_create_topups_table.php`, form di Poin Saya. Lihat `docs/DUITKU.md`.
Diuji: pembuatan header/signature, endpoint sandbox/production, penolakan respons galat, verifikasi callback (valid, jumlah/order/signature/merchant diubah). BELUM diuji: panggilan nyata ke Duitku sandbox, logika callback terhadap database (butuh Laravel asli).
Sisa: auto renew dari poin, hapus bayar manual lama untuk pesanan berharga tetap, pengingat masa aktif, endpoint cek status bila Duitku menyediakannya.
