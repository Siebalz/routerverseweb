@extends('layouts.dashboard')

@section('title', 'Pesanan Saya')

@php
    $saldo = (int) $saldo;
    $wa = config('services.whatsapp.number');
    $lastOrder = $lastOrderId ? $orders->where('id', $lastOrderId)->first() : null;
    $bisaBayar = $paymentSetting->qrisVisible() || $paymentSetting->bcaVisible();
@endphp

@section('content')
<div class="pg-head">
    <div>
        <h1>Pesanan saya</h1>
        <p>Riwayat pesanan layanan dan paket yang Anda ajukan.</p>
    </div>
    <a class="btn btn-line" href="{{ route('services.index') }}">Pesan Layanan Lain</a>
</div>

@if ($lastOrder)
    <section class="sent" aria-labelledby="judul-terkirim">
        <h2 id="judul-terkirim">Pesanan terkirim</h2>
        <p>{{ $lastOrder->service_name }} sudah masuk. Agar lebih cepat diproses, lanjutkan pembayaran atau chat tim kami.</p>
        <div class="actions" style="display:flex;flex-wrap:wrap;gap:8px">
            @if ($lastOrder->price)
                <a class="btn btn-primary" href="#order-{{ $lastOrder->id }}">Bayar dengan Poin</a>
            @elseif ($bisaBayar)
                <button type="button" class="btn btn-primary" data-pay-open data-pay-kind="paket" data-pay-name="{{ $lastOrder->service_name }}" data-pay-price="{{ $lastOrder->price_label }}">Bayar Sekarang</button>
            @endif
            <a class="btn btn-line" href="https://wa.me/{{ $wa }}?text={{ rawurlencode('Halo Routerverse, saya baru memesan: '.$lastOrder->service_name.' ('.$lastOrder->price_label.'). Mohon info kelanjutannya ya.') }}" target="_blank" rel="noopener">Chat di WhatsApp</a>
        </div>
    </section>
@endif

@if ($orders->isEmpty())
    <div class="empty">
        <h2 style="font-size:1.25rem">Belum ada pesanan</h2>
        <p>Pilih layanan di katalog, lalu pesanan Anda akan muncul di sini beserta statusnya.</p>
        <a class="btn btn-primary" href="{{ route('services.index') }}">Lihat Layanan dan Paket</a>
    </div>
@else
    <section class="block" aria-label="Daftar pesanan">
        <ul class="list ord">
            @foreach ($orders as $order)
                <li id="order-{{ $order->id }}">
                    <div>
                        <span class="name">{{ $order->service_name }}</span>
                        <p class="meta">{{ $order->price_label }}{{ $order->paid_with === 'points' ? ', dibayar dengan poin' : '' }}, dipesan {{ $order->created_at->format('d M Y, H:i') }}</p>
                        @if ($order->notes)<p class="meta">Catatan: {{ $order->notes }}</p>@endif
                    </div>
                    <div class="actions">
                        @if ($order->status === 'pending')
                            @if ($order->price && $saldo >= $order->price)
                                <form method="POST" action="{{ route('services.pay-points', $order) }}" data-confirm="Bayar Rp {{ number_format($order->price, 0, ',', '.') }} dengan poin? Saldo Anda berkurang dan layanan langsung diproses.">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm" data-busy="Memproses...">Bayar Rp {{ number_format($order->price, 0, ',', '.') }} dengan Poin</button>
                                </form>
                            @elseif ($order->price)
                                <a class="btn btn-line btn-sm" href="{{ route('points.index') }}">Poin kurang, top up</a>
                            @elseif ($bisaBayar)
                                <button type="button" class="btn btn-primary btn-sm" data-pay-open data-pay-kind="paket" data-pay-name="{{ $order->service_name }}" data-pay-price="{{ $order->price_label }}">Bayar</button>
                            @else
                                <a class="btn btn-line btn-sm" href="https://wa.me/{{ $wa }}?text={{ rawurlencode('Halo Routerverse, saya mau bayar pesanan: '.$order->service_name.'.') }}" target="_blank" rel="noopener">Tanya Pembayaran</a>
                            @endif
                        @endif
                        <span class="{{ $order->status_badge_class }}">{{ $order->status_label }}</span>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>
@endif
@endsection

@push('dialogs')
    @include('components.payment-dialog', ['paymentSetting' => $paymentSetting])
@endpush
