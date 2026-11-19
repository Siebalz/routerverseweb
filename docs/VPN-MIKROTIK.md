# VPN Remote: sambungan ke MikroTik server

Aplikasi membuat akun VPN pelanggan lewat RouterOS API (port 8728, v6 dan v7). Algoritmanya sama dengan Pantau:
PPP secret (L2TP) dengan IP statis, aturan NAT dst-nat dari port publik ke IP VPN pelanggan, lalu script client L2TP.

## Siapkan di MikroTik server (sekali)
```
/user group add name=rv-api policy=api,read,write,policy,sensitive
/user add name=rv-api group=rv-api password=GANTI_PASSWORD_KUAT address=IP_SERVER_WEB/32
/ip service set api disabled=no address=IP_SERVER_WEB/32
/interface l2tp-server server set enabled=yes default-profile=default
```
Pastikan sudah ada NAT masquerade untuk subnet VPN dan firewall mengizinkan port publik 20000-29999 (rentang di `config/vpn.php`).

## Isi `.env`
Lihat bagian `VPN_*` di `.env.example`. Server yang `VPN_API_HOST_*`-nya kosong tidak akan disinkronkan.

## Jalankan
```
php artisan migrate
php artisan schedule:work       # vpn:sync tiap 5 menit: nonaktifkan akun habis masa aktif, ulang yang gagal
php artisan vpn:sync            # manual
```

## Paket
Diatur di `config/vpn.php` bagian `plans`:
- `port1` Rp 2.000: pelanggan memilih satu port (Winbox 8291, API 8728, atau custom).
- `port4` Rp 4.000: Winbox, API, WebFig (80), dan HTTPS (443) sekaligus. Port publiknya 4 nomor berurutan, mis. 23736-23739.
Tiap port punya aturan NAT sendiri dengan comment `VPN-<username>-<nama port>`. Port tujuan tiap port bisa diubah pelanggan di halaman Kelola.

## Kalau belum sinkron
Jalankan `php artisan vpn:test`. Perintah ini menunjukkan penyebabnya: `.env` belum diisi, login API gagal, profil PPP tidak ada, atau akun yang tertahan beserta pesan galatnya. Lalu `php artisan vpn:sync` untuk memaksa sinkron.
Akun hanya disinkronkan setelah statusnya aktif (admin mengubah pesanan ke Diproses atau Selesai).

## Alur
1. Pelanggan order, bayar, admin mengonfirmasi. Saat itu juga (tanpa antrean) secret + NAT dibuat di MikroTik dan IP statis tersimpan di `vpn_accounts.vpn_ip`. Kalau gagal, pesan galatnya tampil ke admin dan `vpn:sync` mengulang tiap 5 menit.
2. Ubah port atau password di halaman Kelola menerapkan perubahan ke router lewat job yang sama.
3. Masa aktif habis: `vpn:sync` menonaktifkan secret dan memutus sesi. Perpanjang mengaktifkannya lagi.
4. Tombol Hapus VPN di halaman Kelola menghapus secret, sesi aktif, dan semua NAT di MikroTik, lalu akunnya dari database.
5. Halaman Kelola menampilkan script L2TP client (dengan watchdog) untuk ditempel di router pelanggan.
