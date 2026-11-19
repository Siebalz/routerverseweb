@extends('layouts.site')

@section('title', 'Routerverse | Setting Jaringan dan Remote Server')

@php
    $wa = config('services.whatsapp.number');
    $waLink = fn (string $text) => 'https://wa.me/'.$wa.'?text='.rawurlencode($text);
    // Pemesanan dan pembayaran dilakukan di dashboard pelanggan (pembayaran Midtrans menyusul).
    $pesanUrl   = auth()->check() ? route('services.index') : route('register');
    $pesanLabel = auth()->check() ? 'Pesan di Dashboard' : 'Daftar untuk Memesan';

    $layanan = [
        ['Load Balance',  'Bagi beban ISP otomatis agar koneksi tetap stabil saat trafik tinggi.'],
        ['PPPoE Server',  'Setup PPPoE untuk manajemen pelanggan bulanan dengan limit per user.'],
        ['Hotspot',       'Konfigurasi hotspot voucher dengan halaman login custom dan integrasi Mikhmon.'],
        ['Pisah Trafik',  'Pisah trafik game, streaming, dan browsing agar bandwidth terpakai efisien.'],
    ];

    $merek = [
        ['Mikrotik','mikrotik.png',360,360], ['Ubiquiti','ubiquiti.png',360,360], ['Ruijie','ruijie.png',320,320],
        ['Cisco','cisco.png',360,190],       ['Aruba','aruba.png',360,360],       ['Huawei','huawei.png',354,360],
        ['C-Data','cdata.png',360,360],      ['Fortinet','fortinet.png',360,240], ['HSGQ','hsgq.png',360,270],
        ['Juniper','juniper.png',360,240],   ['VSOL','vsol.png',360,215],         ['ZTE','zte.png',360,129],
    ];

    // Harga paket: ubah di sini. Semua tombol paket mengarah ke dashboard pelanggan.
    $remote = [
        ['Starter', 'Cocok untuk uji coba',   0,     ['1 Port', 'Akses dasar remote', 'L2TP/IPsec', 'Garansi']],
        ['Basic',   'Untuk pemakaian harian', 2000,  ['3 Port', 'Semua fitur Starter', 'Monitoring lanjutan', 'Dukungan teknis 24/7']],
        ['Premium', 'Untuk kebutuhan besar',  10000, ['5 Port', 'Semua fitur Basic', 'Mikhmon Online 1 bulan gratis']],
    ];

    $hosting = [
        ['Mikhmon Online', 'mikhmonwhite.PNG', 183, 193, 10000, ['Akses Mikhmon Online 24/7', 'Remote dashboard dari mana saja', 'Kelola hotspot dan voucher', 'Dapat VPN port API']],
        ['PHPNuxBill', 'phpnuxbill_square.png', 512, 512, 10000, ['Akses PHPNuxBill 24/7', 'Kelola hotspot dan PPPoE teratur', 'Monitoring real-time', 'Dapat VPN port API']],
    ];

    $rp = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');
@endphp

@section('content')

{{-- 1. HERO: satu fokus, dua tindakan nyata --}}
<section class="hero" aria-labelledby="judul-hero">
    <x-arcs class="hero-arcs" />
    <div class="container hero-grid">
        <div>
            <h1 id="judul-hero">Setting jaringan dan remote server untuk RT/RW Net, warnet, dan kantor</h1>
            <p class="hero-lead">Kami mengerjakan konfigurasi MikroTik dari jarak jauh: load balance, PPPoE, hotspot, dan VPN. Anda pilih layanan, kami kerjakan tanpa perlu datang ke lokasi.</p>
            <div class="hero-actions">
                @auth
                    <a class="btn btn-primary" href="{{ route('services.index') }}">Pesan Layanan</a>
                @else
                    <a class="btn btn-primary" href="{{ route('register') }}">Daftar dan Pesan</a>
                @endauth
                <a class="btn btn-line" href="{{ $waLink('Halo Routerverse, saya mau konsultasi setting jaringan.') }}" target="_blank" rel="noopener">Chat Admin di WhatsApp</a>
            </div>
            <p class="hero-note">Dayeuhkolot, Kab. Bandung. Semua pekerjaan dilakukan secara remote.</p>
        </div>
        <div class="hero-art">
            <img src="{{ asset('image/datacenter.png') }}" width="512" height="512" alt="Ilustrasi server dan perangkat jaringan" fetchpriority="high">
        </div>
    </div>
</section>

{{-- 2. SETTING JARINGAN: judul menempel di kiri, layanan berbaris di kanan --}}
<section id="setting-jaringan" class="sec sec-canvas" aria-labelledby="judul-setting">
    <div class="container split">
        <div class="split-head">
            <div class="sec-head">
                <x-arcs />
                <h2 id="judul-setting">Setting jaringan yang kami kerjakan</h2>
                <p>Dari rumahan, RT/RW Net, sampai kantor. Ceritakan kondisi jaringan Anda, kami tentukan konfigurasinya.</p>
            </div>
            <a class="btn btn-primary" href="{{ $waLink('Halo Routerverse, saya mau tanya setting jaringan.') }}" target="_blank" rel="noopener">Tanya Setting Jaringan</a>
        </div>
        <ul class="rows">
            @foreach ($layanan as [$judul, $isi])
                <li>
                    <h3>{{ $judul }}</h3>
                    <p>{{ $isi }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- 3. PERANGKAT: logo statis, tanpa marquee --}}
<section id="perangkat-jaringan" class="sec" aria-labelledby="judul-perangkat">
    <div class="container">
        <div class="sec-head">
            <h2 id="judul-perangkat">Merek perangkat yang bisa kami tangani</h2>
            <p>Router, access point, switch, firewall, dan OLT. Merek Anda tidak ada di daftar? Tanyakan lewat WhatsApp.</p>
        </div>
        <ul class="brands">
            @foreach ($merek as [$nama, $file, $w, $h])
                <li><img src="{{ asset('image/brands/'.$file) }}" width="{{ $w }}" height="{{ $h }}" alt="{{ $nama }}" loading="lazy"></li>
            @endforeach
        </ul>
    </div>
</section>

{{-- 4. REMOTE SERVER: harga dalam baris, bukan tiga kartu --}}
<section id="remote-server" class="sec sec-canvas" aria-labelledby="judul-remote">
    <div class="container">
        <div class="sec-head">
            <x-arcs />
            <h2 id="judul-remote">Paket Remote Server</h2>
            <p>Pilih paket sesuai jumlah port dan fitur yang Anda butuhkan. Pesan dari dashboard pelanggan.</p>
        </div>
        <ul class="plans">
            @foreach ($remote as [$nama, $ket, $harga, $fitur])
                <li>
                    <div class="plan-name">
                        <h3>{{ $nama }}</h3>
                        <p>{{ $ket }}</p>
                    </div>
                    <p class="price {{ $harga > 0 ? 'price-amber' : '' }}" style="justify-self:start">{{ $rp($harga) }} <small>/ bulan</small></p>
                    <ul class="ticks">
                        @foreach ($fitur as $f) <li>{{ $f }}</li> @endforeach
                    </ul>
                    <a class="btn btn-primary" href="{{ $pesanUrl }}">{{ $pesanLabel }}</a>
                </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- 5. HOSTING --}}
<section id="hosting" class="sec" aria-labelledby="judul-hosting">
    <div class="container">
        <div class="sec-head">
            <x-arcs />
            <h2 id="judul-hosting">Paket Hosting</h2>
            <p>Aplikasi pengelola hotspot dan billing yang sudah terpasang dan siap dipakai. Pesan dari dashboard pelanggan.</p>
        </div>
        <div class="panels">
            @foreach ($hosting as [$nama, $logo, $w, $h, $harga, $fitur])
                <article class="panel">
                    <div class="panel-top">
                        <span class="panel-logo"><img src="{{ asset('image/'.$logo) }}" width="{{ $w }}" height="{{ $h }}" alt="" loading="lazy"></span>
                        <div>
                            <h3>{{ $nama }}</h3>
                            <p class="price price-amber" style="display:inline-block;margin-top:4px">{{ $rp($harga) }} <small>/ bulan</small></p>
                        </div>
                    </div>
                    <ul class="ticks">
                        @foreach ($fitur as $f) <li>{{ $f }}</li> @endforeach
                    </ul>
                    <a class="btn btn-primary" href="{{ $pesanUrl }}">{{ $pesanLabel }}</a>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- 6. TEMPLATE VOUCHER: data dari database, dengan keadaan kosong --}}
<section id="produk-kami" class="sec sec-canvas" aria-labelledby="judul-produk">
    <div class="container">
        <div class="sec-head">
            <x-arcs />
            <h2 id="judul-produk">Template voucher hotspot</h2>
            <p>Desain voucher siap pakai. Logo dan harga bisa disesuaikan.</p>
        </div>
        @if ($produkUnggulan->isEmpty())
            <div class="empty">
                <h3>Belum ada template</h3>
                <p>Template baru sedang disiapkan. Butuh desain voucher sekarang? Kirim permintaan lewat WhatsApp.</p>
                <a class="btn btn-primary" href="{{ $waLink('Halo Routerverse, saya mau pesan desain voucher hotspot.') }}" target="_blank" rel="noopener">Pesan Desain Voucher</a>
            </div>
        @else
            <ul class="tiles">
                @foreach ($produkUnggulan as $p)
                    <li class="tile">
                        <a class="tile-link" href="{{ route('products.show', $p) }}">
                            <div class="tile-img">
                                @if ($p->cover_image)
                                    <img src="{{ asset('storage/'.$p->cover_image) }}" width="400" height="400" alt="{{ $p->name }}" loading="lazy">
                                @else
                                    <span>Belum ada gambar</span>
                                @endif
                            </div>
                            <div class="tile-body">
                                <span class="tile-cat">{{ $p->category ?? 'Voucher Hotspot' }}</span>
                                <span class="tile-name">{{ $p->name }}</span>
                                <span class="tile-price">{{ $rp($p->price) }}</span>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
            <p style="margin-top:28px"><a class="btn btn-line" href="{{ route('products.index') }}">Lihat Semua Template</a></p>
        @endif
    </div>
</section>

{{-- 7. CARA PESAN: satu-satunya blok gelap, momen aksen --}}
<section id="cara-pesan" class="sec sec-dark on-dark" aria-labelledby="judul-pesan">
    <div class="container split">
        <div class="split-head">
            <div class="sec-head">
                <x-arcs />
                <h2 id="judul-pesan">Cara memesan dan membayar</h2>
                <p>Semua dilakukan dari HP atau laptop. Anda tidak perlu datang ke lokasi kami.</p>
            </div>
            <div class="hero-actions" style="margin-top:0">
                @auth
                    <a class="btn btn-accent" href="{{ route('services.index') }}">Buka Layanan dan Paket</a>
                @else
                    <a class="btn btn-accent" href="{{ route('register') }}">Daftar dan Pesan</a>
                @endauth
                <a class="btn btn-line" href="{{ $waLink('Halo Routerverse, saya mau pesan layanan jaringan. Bisa dibantu?') }}" target="_blank" rel="noopener">Tanya via WhatsApp</a>
            </div>
        </div>
        <ol class="rows steps">
            <li><h3>Pilih layanan</h3><p>Pilih setting jaringan, remote server, atau hosting. Bingung memilih? Tanya admin lewat WhatsApp dulu.</p></li>
            <li><h3>Kirim pesanan</h3><p>Masuk ke dashboard, buka Layanan dan Paket, lalu isi catatan singkat: merek router, jumlah ISP, dan kebutuhan Anda.</p></li>
            <li><h3>Konfirmasi dan bayar</h3><p>Admin menghubungi Anda lewat WhatsApp untuk konfirmasi detail dan nominal. Pembayaran lewat QRIS atau transfer BCA, dan pembayaran otomatis lewat payment gateway menyusul.</p></li>
            <li><h3>Dikerjakan dari jarak jauh</h3><p>Kami mengerjakan konfigurasi secara remote. Anda tidak perlu hadir di lokasi.</p></li>
        </ol>
    </div>
</section>

{{-- 8. KONTAK --}}
<section id="kontak" class="sec" aria-labelledby="judul-kontak">
    <div class="container split">
        <div class="split-head">
            <div class="sec-head">
                <x-arcs />
                <h2 id="judul-kontak">Hubungi kami</h2>
                <p>Tanya layanan, konsultasi setting jaringan, atau minta bantuan teknis.</p>
            </div>
            <a class="btn btn-primary" href="{{ $waLink('Halo Routerverse, saya mau konsultasi.') }}" target="_blank" rel="noopener">Konsultasi via WhatsApp</a>
        </div>
        <ul class="rows contact">
            <li><span class="label">WhatsApp</span><a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener">+62 851-7384-4715</a></li>
            <li><span class="label">Instagram</span><a href="https://instagram.com/routerverse.id" target="_blank" rel="noopener">@routerverse.id</a></li>
            <li><span class="label">Email</span><a href="mailto:iqbalrinaldi098@gmail.com">iqbalrinaldi098@gmail.com</a></li>
            <li><span class="label">Alamat</span><a href="https://maps.app.goo.gl/JyRA4M7y44UbFyL69" target="_blank" rel="noopener">Dayeuhkolot, Kab. Bandung</a></li>
            <li><span class="label">Status server</span><a href="https://kuma.routerverse.id/status/routerverse" target="_blank" rel="noopener">kuma.routerverse.id</a></li>
        </ul>
    </div>
</section>

@endsection

