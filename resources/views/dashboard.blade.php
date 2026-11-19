@extends('layouts.dashboard')

@section('title', 'Dashboard')

@php
    $wa = config('services.whatsapp.number');
    $namaDepan = explode(' ', trim(Auth::user()->name))[0];
@endphp

@section('content')
<div class="pg-head">
    <div>
        <h1>Halo, {{ $namaDepan }}</h1>
        <p>Pantau layanan yang aktif, pesan paket baru, dan lihat status pesanan Anda di sini.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('services.index') }}">Pesan Layanan atau Paket</a>
</div>

@if ($latestOrder && $latestOrder->status === 'pending')
    <div class="callout" role="status">
        <p><strong>{{ $latestOrder->service_name }}</strong> menunggu konfirmasi admin. Setelah dikonfirmasi, layanan muncul di Server Saya.</p>
        <p><a href="{{ route('services.my-orders') }}">Lihat status pesanan</a></p>
    </div>
@endif

<div class="dash">
    <section class="block" aria-labelledby="judul-aktif">
        <h2 id="judul-aktif">Layanan aktif</h2>
        @if ($activeOrders->isNotEmpty())
            <ul class="list">
                @foreach ($activeOrders->take(5) as $order)
                    @php $sisa = $order->days_until_expired; @endphp
                    <li>
                        <span class="name">{{ $order->service_name }}</span>
                        <span class="meta {{ $sisa !== null && $sisa <= 5 ? 'warn' : '' }}">
                            @if ($order->expired_at)
                                Aktif sampai {{ $order->expired_at->format('d M Y') }}@if ($sisa !== null && $sisa >= 0), {{ $sisa }} hari lagi @endif
                            @else
                                Sedang aktif
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
            <p style="margin-top:12px"><a class="btn btn-line btn-sm" href="{{ route('services.my-servers') }}">Buka Server Saya</a></p>
        @else
            <div class="empty in-block">
                <h3>Belum ada layanan aktif</h3>
                <p>Pilih paket remote server, hosting, atau setting jaringan. Setelah pesanan dikonfirmasi, layanan tampil di sini.</p>
                <a class="btn btn-primary" href="{{ route('services.index') }}">Pesan Sekarang</a>
            </div>
        @endif
    </section>

    <div style="display:grid;gap:20px">
        <section class="block" aria-labelledby="judul-akses">
            <h2 id="judul-akses">Akses cepat</h2>
            <ul class="list list-links">
                <li><a href="{{ route('services.my-orders') }}"><span class="name">Pesanan Saya</span><span class="meta">Status pesanan dan pembayaran</span></a></li>
                <li><a href="{{ route('products.index') }}"><span class="name">Template Voucher Hotspot</span><span class="meta">Lihat dan beli template siap pakai</span></a></li>
                <li><a href="https://kuma.routerverse.id/status/routerverse" target="_blank" rel="noopener"><span class="name">Status Server</span><span class="meta">Cek uptime layanan, buka di tab baru</span></a></li>
                <li><a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener"><span class="name">Hubungi Support</span><span class="meta">Chat tim kami lewat WhatsApp</span></a></li>
            </ul>
        </section>

        <section class="block" aria-labelledby="judul-akun">
            <h2 id="judul-akun" style="margin-bottom:12px">Akun</h2>
            <dl class="kv">
                <div><dt>Nama</dt><dd>{{ Auth::user()->name }}</dd></div>
                <div><dt>Email</dt><dd>{{ Auth::user()->email }}</dd></div>
                <div><dt>Bergabung sejak</dt><dd>{{ Auth::user()->created_at?->format('d M Y') ?? '-' }}</dd></div>
            </dl>
        </section>
    </div>
</div>
@endsection
