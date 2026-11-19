@extends('layouts.dashboard')

@section('title', 'Server Saya')

@php
    $wa = config('services.whatsapp.number');
    $kategori = ['remote_server' => 'Remote Server', 'hosting' => 'Hosting', 'network_setting' => 'Setting Jaringan'];
@endphp

@section('content')
<div class="pg-head">
    <div>
        <h1>Server saya</h1>
        <p>Layanan dan paket yang sedang aktif untuk akun Anda.</p>
    </div>
    <a class="btn btn-line" href="{{ route('services.index') }}">Pesan Layanan Baru</a>
</div>

@if ($servers->isEmpty())
    <div class="empty">
        <h2 style="font-size:1.25rem">Belum ada layanan aktif</h2>
        <p>Layanan muncul di sini setelah pesanan Anda dikonfirmasi admin.</p>
        <a class="btn btn-primary" href="{{ route('services.index') }}">Pesan Layanan</a>
    </div>
@else
    <div class="srv-grid">
        @foreach ($servers as $server)
            @php $sisa = $server->days_until_expired; @endphp
            <article class="block srv">
                <span class="cat">{{ $kategori[$server->category] ?? 'Layanan' }}</span>
                <h2>{{ $server->service_name }}</h2>
                <p class="meta" style="color:var(--text-2)">{{ $server->price_label }}</p>
                <dl class="kv" style="gap:8px">
                    <div><dt>Aktif sejak</dt><dd>{{ optional($server->activated_at)->format('d M Y') ?? $server->updated_at->format('d M Y') }}</dd></div>
                    @if ($server->status === 'expired')
                        <div><dt>Masa aktif</dt><dd style="color:var(--bad)">Berakhir {{ optional($server->expired_at)->format('d M Y') }}</dd></div>
                    @elseif ($server->expired_at)
                        <div><dt>Berlaku sampai</dt><dd class="{{ $sisa !== null && $sisa <= 5 ? 'warn' : '' }}">{{ $server->expired_at->format('d M Y') }}@if ($sisa !== null && $sisa >= 0), {{ $sisa }} hari lagi @endif</dd></div>
                    @endif
                    @if ($server->notes)
                        <div><dt>Catatan</dt><dd style="font-weight:400">{{ $server->notes }}</dd></div>
                    @endif
                </dl>
                <span class="{{ $server->status_badge_class }}">{{ $server->status_label }}</span>
                <a class="btn btn-line btn-sm" href="https://wa.me/{{ $wa }}?text={{ rawurlencode('Halo Routerverse, saya mau '.($server->status === 'expired' ? 'memperpanjang' : 'bertanya soal').' layanan: '.$server->service_name.'.') }}" target="_blank" rel="noopener">{{ $server->status === 'expired' ? 'Perpanjang via WhatsApp' : 'Tanya Admin' }}</a>
            </article>
        @endforeach
    </div>
@endif
@endsection
