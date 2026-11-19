@extends('layouts.dashboard')

@section('title', 'Layanan dan Paket')

@php
    $intro = [
        'remote_server'   => 'Akses remote ke router Anda lewat VPN. Pilih server, username, dan port tujuan di halaman order.',
        'hosting'         => 'Aplikasi pengelola hotspot dan billing yang sudah terpasang dan siap dipakai.',
        'network_setting' => 'Kami mengerjakan konfigurasi dari jarak jauh. Harga final dikonfirmasi admin setelah kami tahu kondisi jaringan Anda.',
    ];
@endphp

@section('content')
<div class="pg-head">
    <div>
        <h1>Layanan dan paket</h1>
        <p>Pilih yang Anda butuhkan lalu kirim pesanan. Tim kami menghubungi Anda lewat WhatsApp untuk konfirmasi.</p>
    </div>
    <a class="btn btn-line" href="{{ route('services.my-orders') }}">Pesanan Saya</a>
</div>

<div class="cat-tabs" role="tablist" aria-label="Kategori layanan">
    @foreach ($catalog as $key => $cat)
        <button type="button" role="tab" id="tab-{{ $key }}" data-cat-tab="{{ $key }}" aria-controls="panel-{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $cat['label'] }}</button>
    @endforeach
</div>

@foreach ($catalog as $key => $cat)
    <section id="panel-{{ $key }}" role="tabpanel" aria-labelledby="tab-{{ $key }}" @unless($loop->first) hidden @endunless>
        <p class="cat-intro">{{ $cat['desc'] ?? ($intro[$key] ?? '') }}</p>
        <ul class="svc">
            @foreach ($cat['items'] as $item)
                <li>
                    <div>
                        <h3>{{ $item['name'] }}</h3>
                        <p class="price">{{ $item['price'] }}</p>
                    </div>
                    <p class="desc">{{ $item['desc'] }}</p>
                    @if ($key === 'remote_server')
                        <a class="btn btn-primary" href="{{ route('vpn.create') }}">Order VPN Remote</a>
                    @else
                        <button type="button" class="btn btn-primary" data-order-open data-category="{{ $key }}" data-name="{{ $item['name'] }}" data-price="{{ $item['price'] }}">Pesan {{ $item['name'] }}</button>
                    @endif
                </li>
            @endforeach
        </ul>
    </section>
@endforeach
@endsection

@push('dialogs')
<dialog id="order-dialog" class="dialog" aria-labelledby="order-title">
    <form method="dialog" class="dialog-head">
        <h2 id="order-title">Pesan layanan</h2>
        <button type="submit">Tutup</button>
    </form>
    <form method="POST" action="{{ route('services.store') }}" class="dialog-body">
        @csrf
        <input type="hidden" name="category" id="order-category">
        <input type="hidden" name="service_name" id="order-service">
        <input type="hidden" name="price_label" id="order-price">

        <dl class="pay-sum">
            <div><dt>Paket</dt><dd id="order-show-name"></dd></div>
            <div><dt>Harga</dt><dd id="order-show-price"></dd></div>
        </dl>

        <div class="field" style="margin:0">
            <label for="order-notes">Catatan <span style="font-weight:400;color:var(--text-2)">(boleh dikosongkan)</span></label>
            <textarea class="input" id="order-notes" name="notes" rows="3" maxlength="1000" aria-describedby="order-hint"></textarea>
            <p class="field-hint" id="order-hint">Contoh: router MikroTik RB750, 2 ISP untuk load balance.</p>
        </div>

        <div class="dialog-actions">
            <button type="button" class="btn btn-line" data-close>Batal</button>
            <button type="submit" class="btn btn-primary" data-busy="Mengirim...">Kirim Pesanan</button>
        </div>
    </form>
</dialog>
@endpush
