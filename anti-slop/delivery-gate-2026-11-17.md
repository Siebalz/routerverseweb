# Delivery Gate: landing, login, daftar, katalog produk (mode DURING)

Design Read: layanan setting jaringan dan remote server untuk RT/RW Net, warnet, kantor kecil, gaya tegas dan hangat, dial ENERGY 2 / RHYTHM 2 / MOTION 1. Direction: `DESIGN.md` (disetujui pemilik).

Ruang lingkup: `welcome`, `auth/login`, `auth/register`, `products/index`, `products/show`, layout `site`, dialog pembayaran. Dashboard dan halaman admin belum disentuh.

## Hard Gate
- R-02 PASS: pencarian karakter em dash di semua file baru menghasilkan nol.
- R-03 PASS: 6 halaman diuji di 390px dan 1280px, tanpa scroll horizontal; tombol dan tautan nav minimal 44px.
- R-17/R-36/R-38 PASS: angka 99.9%, 50+ klien, "Rekomendasi", "Best Seller", dan klaim "respons 15 menit" dihapus. Harga paket adalah data pemilik.
- R-18 PASS: bagian testimoni (3 nama) dihapus.
- R-23 PASS: logo, ilustrasi, dan logo merek adalah milik pemilik. Tidak ada aset baru yang dibuat selain versi kecil logo.
- R-24 PASS: semua tautan # punya target (diuji), semua tautan nav menuju bagian yang ada.
- R-25 PASS: semua pasangan teks dan latar diukur, terendah 4.8:1 (status hijau), teks biasa 5.8:1 ke atas.
- R-26 PASS: tombol "VPN Gratis Sekarang" yang mati dihapus; "Lupa password?" yang href="#" diganti tautan WhatsApp.
- R-27 PASS (dengan catatan): kondisi kosong ada di landing dan katalog; memuat = tombol kirim berubah "Memproses..." dan nonaktif; galat = pesan validasi login/daftar. Halaman daftar server-render, jadi memuat dan galat jaringan ditangani peramban dan halaman galat Laravel.
- R-28 PASS: tidak ada FAQ.
- R-32 PASS: outline fokus 3px solid pada semua kontrol, menu dan dialog tertutup dengan Escape.
- R-33 PASS: tidak ada skrip penambal.
- R-34 PASS: hanya satu tema (terang), tanpa tombol tema.
- R-35 PARTIAL: dijalankan lewat pengompilasi Blade mini dengan data contoh di Chromium (tidak ada konsol galat, 31 pengecekan klik/keyboard). Aplikasi Laravel asli belum dijalankan di sini karena Composer tidak bisa mengunduh paket. Jalankan sekali di server Anda.
- R-37 PASS: DESIGN.md disetujui pemilik.
- R-39 PASS: tombol vs tautan semantik, label terlihat di semua field, alt di semua gambar, `lang="id"`, satu h1, landmark, prefers-reduced-motion, zoom tidak diblokir.
- R-40 PASS: semua gambar punya width/height, gambar hero eager dengan fetchpriority high, tanpa skrip pihak ketiga (Tailwind CDN dan Bootstrap Icons dihapus). Tidak ada skor Lighthouse yang dilaporkan: not measured.

## Purpose-Gate
- R-01 gradien/glow dihapus. R-04 tanpa ikon generik (ikon dihapus, motif busur logo). R-06 Poppins dari file milik pemilik, font sistem untuk isi, tanpa mono dekoratif. R-07 grid dihapus. R-08 tanpa panah. R-09 tanpa pil/badge. R-10 dan R-13 tanpa blur atau glow. R-12 satu bayangan (dialog). R-14 layanan sebagai baris, paket sebagai baris harga. R-19 tanpa animasi selain hover. R-22 satu ilustrasi milik pemilik.

## Liveliness
Dial dideklarasikan, satu titik fokus per layar, satu aksen amber (tombol pesan dan penanda harga), motif busur sinyal logo diulang sebagai penanda judul dan busur besar di hero.

## Catatan untuk pemilik
- Klaim yang tidak terbukti masih ada di data paket Anda: "Dukungan teknis 24/7", "Monitoring lanjutan". Dibiarkan sesuai permintaan, silakan edit di `welcome.blade.php` (array `$remote`, `$hosting`).
- Empat layanan lama "Monitoring 24/7", "Backup dan Restore", "Keamanan Berlapis" dihapus karena belum ada bukti.


# Tambahan 2026-11-18: dashboard pelanggan dan admin (mode DURING)

Ruang lingkup: `layouts/dashboard`, `dashboard`, `services/*` (layanan, pesanan saya, server saya, kelola pesanan), `settings/payment`, `products/create|edit|_form`. CSS di `public/app.css`, perilaku di `public/app.js` dan `public/rv.js`.

- R-01/R-04/R-07/R-10: banner gradien, bola cahaya, ikon berwarna, kartu statistik "Status Akun: Aktif" dihapus. Sidebar putih dengan penanda halaman aktif, tanpa gradien.
- R-14: dashboard memakai dua kolom tak sama, layanan sebagai baris berpemisah, bukan grid kartu identik.
- R-17/R-36: kartu "Status Akun: Aktif" dan "Aktif & Terverifikasi" (klaim tanpa dasar data) dihapus. Daftar layanan aktif memakai data pesanan asli, lengkap dengan sisa hari.
- R-19: animasi hover naik, popIn modal, dan transisi kartu dihapus.
- R-24/R-26: semua tautan punya tujuan nyata, tidak ada href="#".
- R-27 PASS: keadaan kosong ada di dashboard, pesanan, server, kelola pesanan, katalog. Tombol kirim berubah "Mengirim..." dan nonaktif. Galat validasi tampil di form pembayaran dan produk.
- R-32/R-39 PASS: tab kategori mengikuti pola WAI (panah kiri/kanan, Home, End), dialog native tertutup Escape, semua kolom form berlabel, tabel punya caption dan scope, semua target sentuh minimal 44px, satu h1 per halaman (diuji otomatis di 7 halaman).
- R-33 PASS: skrip inline per halaman diganti satu `app.js`.
- R-40 PASS: Bootstrap, Bootstrap Icons, Tailwind CDN, dan Google Fonts Inter dihapus dari dashboard.
- R-35 PARTIAL: sama seperti sebelumnya, diuji lewat pengompilasi Blade mini dengan data contoh (22 pengecekan, tanpa error konsol dan tanpa scroll horizontal di 390px dan 1280px). Belum dijalankan di Laravel asli.

Perubahan perilaku yang disengaja:
1. Dialog "Pesan" di katalog hanya berisi form pesanan. Tab QRIS dan BCA di dalamnya dihapus karena membayar sebelum memesan membingungkan. Pembayaran dilakukan dari Pesanan Saya (tombol Bayar per pesanan) atau dari banner setelah pesan.
2. Admin: status pesanan sekarang diubah dengan tombol Simpan, tidak lagi otomatis saat pilihan berganti, karena perubahan ke "diproses" memicu provisioning Mikhmon.
3. Server Saya: ditambah tombol "Tanya Admin" atau "Perpanjang via WhatsApp" untuk pelanggan.
