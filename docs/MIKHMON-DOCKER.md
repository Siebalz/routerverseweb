# Mikhmon Online (Docker + Portainer + Cloudflare Tunnel)

Setiap pesanan Mikhmon Online mendapat satu container `animegasan/mikhmon` di Docker host
**192.168.5.45**, dengan alamat `orderN.mikhmonverse.my.id`. PHPNuxBill sudah dihapus.

```
Browser -> Cloudflare (HTTPS) -> cloudflared (di 192.168.5.45) -> Traefik -> container mikhmon-order-N
Laravel (192.168.5.101) --API Portainer :4500--> membuat/menghentikan container
```

## 1. Di Portainer (192.168.5.45)
1. Networks > Add network: `mikhmon-net` (bridge).
2. Account (pojok kanan atas) > My account > Access tokens > Add: salin token `ptr_...`.
3. Catat ID environment: buka environment Docker-mu, lihat angka di URL (`#!/2/docker/...` berarti ID 2).
4. Stacks > Add stack > tempel isi `docker/docker-compose.mikhmon-host.yml`, isi variabel `TUNNEL_TOKEN`, Deploy.

## 2. Di Cloudflare
1. Zero Trust > Networks > Tunnels > Create tunnel (cloudflared), salin token untuk langkah 1.4.
2. Published application route: hostname `*.mikhmonverse.my.id`, service `HTTP`, URL `traefik:80`.
3. DNS mikhmonverse.my.id: CNAME `*` ke `<ID-TUNNEL>.cfargotunnel.com`, Proxied.
4. SSL/TLS mode Full. Sertifikat Universal gratis menutup `*.mikhmonverse.my.id` (satu tingkat).

## 3. Di server Laravel (192.168.5.101), file `.env`
```
DOCKER_API_URL=http://192.168.5.45:4500/api/endpoints/2/docker
DOCKER_API_KEY=ptr_...
DOCKER_API_VERIFY=false
MIKHMON_IMAGE=animegasan/mikhmon:v3-latest
DOCKER_NETWORK=mikhmon-net
MIKHMON_BASE_DOMAIN=mikhmonverse.my.id
```
Port 4500 bisa HTTP atau HTTPS; sesuaikan skema di `DOCKER_API_URL`. Lalu:
```
php artisan migrate --force
php artisan config:cache
sudo supervisorctl restart all     # queue worker harus dimulai ulang
curl -s -H "X-API-Key: ptr_..." http://192.168.5.45:4500/api/endpoints/2/docker/version
```
Perintah curl terakhir harus mengembalikan JSON versi Docker.

## 4. Jalur ke router pelanggan
Container Mikhmon di 192.168.5.45 harus bisa menjangkau IP VPN pelanggan (subnet VPN di CHR,
mis. `11.11.11.0/24`). Tambahkan rute di host Docker:
```
sudo ip route add 11.11.11.0/24 via <IP-LAN-CHR>
```
Buat permanen lewat netplan. Di pelanggan, port API router (8728) harus terbuka ke IP VPN.

## 5. Cara kerja
- Pelanggan membuka Layanan > Order Mikhmon Online, memilih subdomain (`nama.mikhmonverse.my.id`, huruf dan angka 4-24) dan sandi pembuka. Alamat langsung dipesan, tidak bisa dipakai orang lain.
- Admin mengaktifkan pesanan atau pelanggan bayar dengan poin, antrean `ProvisionOrderJob` membuat container.
- Kotak login Traefik (basic auth) mati secara default (`MIKHMON_GATE=false`): alamat langsung membuka login Mikhmon (`mikhmon / 1234`, pelanggan wajib menggantinya). Isi `MIKHMON_GATE=true` untuk memasang kotak login di depannya.
- Pelanggan melihat alamat dan login pertama di halaman Kelola VPN. Login ini dari Traefik (basic auth),
  dengan username `pelanggan` dan sandi pilihan pelanggan, karena Mikhmon bawaan memakai `mikhmon / 1234`. Setelah itu pelanggan ganti login Mikhmon sendiri.
- `php artisan mikhmon:sync` (dijadwalkan tiap 5 menit) menghentikan container yang masa aktifnya habis
  dan menyalakannya lagi setelah diperpanjang. Data tidak dihapus.

## 6. Catatan
- Image yang dipakai: `animegasan/mikhmon:v3-latest` (Mikhmon v3, disarankan pembuatnya untuk pengguna Cloudflare). Port aplikasinya 80, jadi Traefik menunjuk ke port 80 dan container tidak perlu membuka port ke host. Image ini terakhir diperbarui sekitar 4 tahun lalu.
- Container tanpa volume: data Mikhmon aman selama container tidak dihapus (restart dan stop tidak menghapus).
- Jangan ekspos port 4500 ke internet.
