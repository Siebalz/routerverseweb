# Top up poin lewat Duitku POP

Sesuai dokumentasi resmi https://docs.duitku.com/pop/id

## Pasang
1. Isi `.env` (kunci dari dashboard Duitku, proyek sandbox dulu):
   ```
   DUITKU_ENV=sandbox
   DUITKU_MERCHANT_CODE=...
   DUITKU_API_KEY=...
   DUITKU_FEE_FLAT=0
   DUITKU_FEE_PERCENT=0
   ```
   Biaya ditanggung pembeli = `FEE_FLAT` + `FEE_PERCENT`% dari nominal. Isi sesuai biaya Duitku. Nilai 0 berarti Anda yang menanggung.
2. Di dashboard Duitku, isi **Callback URL** proyek: `https://DOMAIN-ANDA/duitku/callback`. Harus HTTPS dan bisa dijangkau dari internet (localhost tidak bisa).
3. `php artisan migrate` lalu `php artisan config:clear`.

## Alur
1. Pelanggan isi nominal di Poin Saya (min Rp 10.000), tekan Top Up Sekarang.
2. Server membuat tagihan (`createInvoice`) dan mengarahkan pelanggan ke halaman bayar Duitku (`paymentUrl`).
3. Setelah lunas, Duitku memanggil `/duitku/callback`. Server memeriksa signature `md5(merchantCode + amount + merchantOrderId + apiKey)`, memastikan jumlah sama dengan tagihan, lalu menambah poin sebesar nominal. Top up yang sama tidak bisa menambah poin dua kali (dikunci di database).
4. Halaman `returnUrl` hanya menampilkan pesan terima kasih. Poin tidak pernah bertambah dari halaman itu, hanya dari callback.

## Catatan
- Dokumen POP tidak memuat endpoint cek status, jadi poin hanya dari callback. Top up yang belum lunas muncul di "Top up terakhir" dengan tautan Lanjutkan pembayaran.
- Callback tanpa login dan tanpa CSRF (dikecualikan di `bootstrap/app.php`); keamanannya dari signature.
- Dokumen tidak menyebut isi respons callback. Server membalas HTTP 200 "OK" bila beres.
- Uji sandbox: top up kecil, bayar di simulator Duitku, cek saldo bertambah satu kali walau callback dikirim ulang.

## Poin tidak bertambah setelah bayar
Jalankan `php artisan duitku:test`. Perintah ini memeriksa kunci, alamat callback (peringatan bila masih localhost), dan top up terakhir. Lalu buka `storage/logs/laravel.log` dan cari "Duitku callback":
- **Tidak ada baris sama sekali:** callback tidak sampai. Penyebab umum: `APP_URL` masih localhost, server tidak bisa dijangkau dari internet, atau Callback URL di dashboard Duitku salah.
- **"signature/parameter tidak valid":** `DUITKU_MERCHANT_CODE` atau `DUITKU_API_KEY` di `.env` tidak sama dengan proyek yang dipakai membayar (sandbox dan production beda kunci).
- **"jumlah tidak cocok":** nominal dari Duitku berbeda dengan tagihan. Kirim log-nya.
- **"hasil":"credited":** poin sudah masuk, muat ulang halaman Poin Saya.
Bila sudah memastikan di dashboard Duitku bahwa transaksi itu lunas: `php artisan duitku:test --settle=NOMOR_ORDER` memasukkan poinnya manual (aman, tidak bisa dobel).

## Uji di komputer sendiri (php artisan serve)
Duitku tidak bisa menjangkau `localhost`, jadi buat terowongan sementara ke port 8000. Contoh dengan Cloudflare Tunnel (gratis, tanpa akun):
1. Pasang sekali (Windows): `winget install --id Cloudflare.cloudflared`
2. Terminal 1: `php artisan serve`
3. Terminal 2: `cloudflared tunnel --url http://localhost:8000` lalu salin alamat `https://xxxx.trycloudflare.com` yang muncul.
4. Di `.env`: `APP_URL=https://xxxx.trycloudflare.com`, lalu `php artisan config:clear`. Web tetap dibuka lewat `http://localhost:8000`.
5. `php artisan duitku:test` harus menampilkan alamat callback `https://xxxx.trycloudflare.com/duitku/callback` tanpa peringatan merah.
6. Buat top up baru dan bayar di sandbox. Callback dikirim ke alamat tunnel, diteruskan ke komputer Anda.
Alamat tunnel berubah setiap kali cloudflared dijalankan ulang: ulangi langkah 3 dan 4. Alternatif: ngrok (`ngrok http 8000`, perlu akun gratis).
Callback dikirim per transaksi (dari `APP_URL`), jadi top up yang dibuat sebelum APP_URL diganti tetap mengarah ke alamat lama. Buat top up baru, atau selesaikan yang lama dengan `php artisan duitku:test --settle=NOMOR_ORDER`.
