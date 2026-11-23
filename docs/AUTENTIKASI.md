# Verifikasi email dan verifikasi dua langkah (OTP email)

Dua lapis keamanan akun:

1. **Verifikasi email saat daftar.** Akun baru harus mengklik tautan di email sebelum dashboard bisa dipakai.
2. **Verifikasi kedua saat login.** Setelah email dan password benar, sistem mengirim kode 6 angka ke email. Kode dimasukkan untuk menyelesaikan login.
   - **Admin: wajib.**
   - **Pelanggan: opsional.** Diaktifkan sendiri di dashboard, menu **Keamanan Akun** (butuh password untuk mengaktifkan atau mematikan).

## Perlindungan brute-force: Turnstile dan Cloudflare

Berlapis dari luar ke dalam:

1. **Cloudflare WAF** menahan lonjakan permintaan sebelum sampai ke server.
2. **Turnstile** (kotak verifikasi "bukan robot") di halaman login dan daftar. Robot tanpa token ditolak sebelum password diperiksa.
3. **Pembatas Laravel**: login salah 5 kali per menit per email dan IP, serta 10 permintaan per menit per IP di rute login.
4. **Kode OTP** untuk admin (dan pelanggan yang mengaktifkannya): password yang bocor saja tidak cukup untuk masuk.

### Pasang Turnstile

1. Dashboard Cloudflare, menu **Turnstile**, **Add widget**. Nama bebas, **Hostname**: `routerverse.web.id` dan `www.routerverse.web.id`, mode **Managed**.
2. Salin **Site Key** dan **Secret Key** ke `.env` server, lalu `php artisan config:cache`:
   ```
   TURNSTILE_SITE_KEY=...
   TURNSTILE_SECRET_KEY=...
   ```
3. Buka `/login` dan `/register`: kotak verifikasi tampil di atas tombol. Tanpa kunci terisi, kotak tidak tampil dan pemeriksaan dilewati (cocok untuk lokal).
4. Uji di lokal dengan kunci uji Cloudflare (selalu lolos): site key `1x00000000000000000000AA`, secret key `1x0000000000000000000000000000000AA`. Jangan dipakai di produksi.

Token berlaku 5 menit dan sekali pakai. Bila Cloudflare tidak bisa dihubungi, login ditolak (fail closed). Untuk mematikan sementara: `TURNSTILE_ENABLED=false` lalu `config:cache`.

Catatan: Turnstile memuat satu skrip dari `challenges.cloudflare.com` (async, tidak menahan tampilan halaman) hanya di halaman login dan daftar, dan hanya bila kunci terisi.

### Aturan Cloudflare yang disarankan

- **Batas permintaan (WAF, Rate limiting rules).** Paket gratis: 1 aturan, periode 10 detik, hitung per IP, ekspresi hanya memakai Path. Contoh: Path sama dengan `/login`, 10 permintaan per 10 detik per IP, aksi Block (10 detik).
- **Bot Fight Mode: hati-hati.** Pengaturan ini bisa menantang atau memblokir permintaan server ke server, termasuk callback Duitku ke `/duitku/callback`. Kalau diaktifkan, lakukan top up sandbox dan pastikan baris "Duitku callback masuk" masih muncul di `storage/logs/laravel.log`. Bila callback berhenti, matikan lagi.
- Pastikan aturan WAF tidak memblokir path `/duitku/callback`.

### Cek IP asli pengunjung

Pembatas Laravel membaca IP lewat `X-Forwarded-For` (`trustProxies`). Cara memastikan: dari HP (data seluler) lakukan beberapa login salah sampai kena batas, lalu login normal dari laptop. Laptop tidak boleh ikut terblokir. Kalau ikut terblokir, semua pengunjung terbaca memakai IP yang sama dan pengaturan proxy perlu diperiksa.

## Alur

Daftar → email berisi tautan (berlaku 60 menit) → klik → akun aktif.
Login → email dan password → (jika OTP berlaku) kode 6 angka dikirim → masukkan kode → masuk.

Aturan kode OTP: berlaku 10 menit, sekali pakai, maksimal 5 kali salah (lalu harus login ulang), kode baru bisa diminta tiap 60 detik. Yang disimpan di database hanya hash kodenya (tabel `login_otps`).

## Pasang di server

1. Isi email di `.env` (lihat bagian berikut), lalu:
   ```bash
   php artisan migrate --force
   php artisan config:cache
   ```
   Migrasi menambah kolom `users.otp_enabled`, tabel `login_otps`, dan menandai semua akun yang **sudah ada** sebagai terverifikasi supaya tidak ada yang terkunci.
2. Uji kirim email sebelum mengandalkannya:
   ```bash
   php artisan tinker
   >>> Mail::raw('Tes email Routerverse', fn ($m) => $m->to('emailanda@contoh.com')->subject('Tes'));
   ```
3. Daftar dengan email baru dan pastikan email verifikasi masuk (cek juga folder spam).

Aplikasi berjalan di belakang Cloudflare Tunnel, jadi `APP_URL=https://routerverse.web.id` harus benar. Tautan verifikasi bertanda tangan: kalau skema atau domainnya berbeda dari yang diakses pengguna, tautan ditolak (403).

## Pengaturan SMTP

Cloudflare tidak menyediakan pengiriman email lewat SMTP. Pakai penyedia SMTP. Contoh nilai (periksa halaman SMTP penyedia untuk nilai terbaru dan batas pengirimannya):

```
# Gmail (butuh verifikasi 2 langkah Google dan "App password"; alamat pengirim harus alamat Gmail itu)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=alamat@gmail.com
MAIL_PASSWORD=app-password-16-huruf
MAIL_FROM_ADDRESS=alamat@gmail.com

# Brevo
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=login-brevo
MAIL_PASSWORD=smtp-key
MAIL_FROM_ADDRESS=noreply@routerverse.web.id
```

Port 465 memakai `MAIL_SCHEME=smtps`. Supaya email tidak masuk spam saat memakai alamat di domain sendiri, pasang rekaman **SPF** dan **DKIM** (dan sebaiknya **DMARC**) sesuai petunjuk penyedia SMTP di DNS domain Anda.

Uji lokal tanpa SMTP: biarkan `MAIL_MAILER=log`. Isi email, termasuk kode OTP dan tautan verifikasi, tertulis di `storage/logs/laravel.log`.

## Saklar di `.env`

| Variabel | Bawaan | Fungsi |
| --- | --- | --- |
| `AUTH_EMAIL_VERIFICATION` | `true` | `false` = tidak wajib verifikasi email (alur daftar lama) |
| `AUTH_OTP_ENABLED` | `true` | `false` = tidak ada yang diminta kode OTP |
| `AUTH_OTP_ADMIN_REQUIRED` | `true` | `false` = admin tidak dipaksa, ikut pilihan sendiri |
| `AUTH_OTP_TTL` | `10` | Umur kode (menit) |
| `AUTH_OTP_MAX_ATTEMPTS` | `5` | Batas salah memasukkan kode |
| `AUTH_OTP_RESEND_SECONDS` | `60` | Jeda minta kode baru |

Setelah mengubah `.env`: `php artisan config:cache`.

## Jika terkunci (email tidak terkirim)

Admin wajib OTP, jadi kalau SMTP rusak admin tidak bisa masuk. Jalan keluarnya, dari server:

```bash
# tandai email terverifikasi (dan matikan OTP pilihan akun itu)
php artisan user:verify emailadmin@contoh.com --otp-off

# bila admin tetap diminta kode: matikan OTP sementara, perbaiki SMTP, lalu nyalakan lagi
#   .env  ->  AUTH_OTP_ENABLED=false
php artisan config:cache
```

Pesan "Kode verifikasi gagal dikirim" saat login berarti SMTP bermasalah. Penyebabnya tertulis di `storage/logs/laravel.log`.

## Catatan keamanan

- Tautan verifikasi bertanda tangan dan memuat hash email. Tautan bisa dibuka di perangkat lain tanpa login.
- Login yang benar belum membuka sesi sampai kode OTP lolos. Status "menunggu kode" hanya hidup 30 menit.
- Login berulang dalam 60 detik tidak mengirim email baru, supaya halaman login tidak dipakai membanjiri email pemilik akun.
- Email adalah faktor kedua di sini. Kalau email seseorang dibobol, lapisan ini ikut lemah. Untuk keamanan lebih tinggi, tahap berikutnya adalah aplikasi authenticator (TOTP).
- "Lupa password" masih lewat WhatsApp admin. Reset password lewat email belum dibuat.
