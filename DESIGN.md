# DESIGN.md: Routerverse

Status: USULAN dari agen, menunggu persetujuan pemilik. Catatan jujur: arah yang ditulis agen cenderung generik. Koreksi apa pun yang tidak terasa seperti Routerverse.

Dial: ENERGY 2 / RHYTHM 2 / MOTION 1

Design Read: layanan setting jaringan dan remote server untuk pemilik RT/RW Net, warnet, dan kantor kecil di Indonesia, bahasa Indonesia, gaya tegas dan teknis tapi hangat, dial ENERGY 2 / RHYTHM 2 / MOTION 1.

## Identitas

- Nama: Routerverse (routerverse.web.id)
- Usaha: jasa setting dan konfigurasi jaringan (MikroTik, PPPoE, load balance, VPN, hotspot), remote server, hosting, dan template voucher hotspot.
- Pembeli: orang teknis lapangan yang butuh jaringannya jalan, bukan pembaca brosur. Mereka membuka situs dari HP, sering di luar ruangan.
- Kepribadian: to the point, bisa dipercaya karena menunjukkan pekerjaan nyata, tidak berlebihan.

## Palet (inti 2 + 1 aksen, sisanya netral)

| Peran | Nilai | Alasan |
|---|---|---|
| Inti 1: Indigo Routerverse | #3B46F2 | Warna logo awan dan sinyal milik Anda; jadi satu-satunya warna merek |
| Inti 2: Tinta biru malam | #0E1230 | Teks dan blok gelap; satu keluarga warna dengan indigo, bukan hitam netral |
| Aksen: Amber | #D98A3D | Dipakai hanya di momen pesan dan harga; kontras sengaja dengan indigo |
| Netral | #FFFFFF, #F4F5FB, garis #D9DCEE, teks sekunder #4A4F6A | Kertas dan pemisah |
| Status fungsional | hijau #0B7A5B (aktif/lunas), merah #B42342 (gagal/kedaluwarsa) | Hanya untuk status pesanan dan pembayaran, bukan dekorasi |

Dibuang dari versi sekarang: teal dan rose sebagai warna dekoratif, gradien indigo penuh di semua section, bola cahaya (glow), latar grid.

Kontras: semua teks wajib 4.5:1. Teks sekunder #4A4F6A di atas #F4F5FB sekitar 7:1.

## Tipografi

- Judul: Poppins SemiBold dan Bold. Alasan: file Poppins sudah Anda simpan di `public/font`, bentuknya bulat geometris, cocok dengan bentuk awan di logo. Bukan pilihan default AI (Inter, Geist).
- Isi: tumpukan font sistem. Alasan: pembeli sering memakai HP dengan kuota terbatas; tidak ada unduhan font tambahan, teks tampil langsung.
- Monospace: hanya untuk perintah RouterOS atau alamat asli yang memang ditampilkan sebagai kode. Tidak dipakai sebagai gaya "terminal".
- Tanpa label huruf kapital berspasi lebar.

## Motif identitas

Busur sinyal dari logo (tiga busur seperempat lingkaran yang membesar). Dipakai satu kali per section sebagai penanda judul, dan sekali besar terpotong di hero. Tidak dipakai sebagai pola latar berulang.

## Bentuk dan permukaan

- Radius: tombol dan input 8px, kartu data 12px, tidak ada bentuk pil kecuali status yang memang berstatus.
- Bayangan: hanya pada modal pembayaran (QRIS) dan dropdown, sebagai penanda elevasi.
- Tanpa glassmorphism, tanpa glow. Fokus keyboard: garis 3px indigo dengan offset.

## Komposisi (RHYTHM 2)

Urutan mengikuti cara orang memesan, bukan template landing page:
1. Hero: satu kalimat apa yang dikerjakan, dua tindakan nyata (Daftar dan Pesan lewat WhatsApp), gambar datacenter milik Anda.
2. Setting jaringan: daftar layanan nyata (load balance, PPPoE, VPN L2TP, hotspot) sebagai baris berisi, bukan empat kartu identik.
3. Perangkat yang ditangani: deretan logo merek asli yang sudah ada di `public/image/brands`, statis, bukan marquee.
4. Remote server dan hosting: paket dan harga dari data nyata Anda.
5. Template voucher: produk dari database, lengkap dengan kondisi kosong, memuat, dan gagal.
6. Cara memesan dan membayar: langkah sesuai alur sebenarnya di aplikasi (pilih layanan, bayar, proses), siap diganti saat payment gateway aktif.
7. Kontak dan footer sederhana dengan tautan yang benar-benar ada.

Variasi antar section: ada yang dua kolom asimetris, ada daftar baris, ada blok gelap penuh lebar satu kali (bagian pesan), sisanya putih dan #F4F5FB.

## Gerak (MOTION 1)

Hanya hover, fokus, buka-tutup menu dan modal. Tanpa reveal saat scroll, tanpa marquee, tanpa efek tilt atau kursor bercahaya. Semua gerak mati pada `prefers-reduced-motion`.

## Tema

Terang saja, tanpa tombol tema. Alasan: dilihat di HP siang hari, dan satu tema yang diuji penuh lebih baik daripada dua tema setengah jadi.

## Siap payment gateway

Halaman pembayaran dirancang untuk beberapa metode (QRIS statis dan transfer BCA sekarang, gateway nanti) dengan status pesanan yang jelas: menunggu bayar, lunas, kedaluwarsa, gagal. Setiap status punya tampilan sendiri, termasuk kondisi memuat dan galat. Tidak ada kode gateway dipasang sampai penyedia dipilih.

## Aturan teks

- Bahasa Indonesia sehari-hari, kalimat pendek, tanpa tanda pisah panjang.
- Tombol menyebut tindakannya: "Daftar dan Pesan", "Chat Admin di WhatsApp", "Lihat Template Voucher".
- Tanpa angka, klien, uptime, atau testimoni sampai ada data asli dengan sumber.
