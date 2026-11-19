@extends('layouts.dashboard')

@section('title', 'Order VPN Remote')

@section('content')
<nav class="crumbs" aria-label="Jejak halaman"><a href="{{ route('vpn.index') }}">VPN Remote</a> <span aria-hidden="true">/</span> <span aria-current="page">Order</span></nav>

<div class="pg-head">
    <div><h1>Order VPN Remote</h1></div>
</div>

@if ($errors->any())
    <div class="notice notice-bad" role="alert" style="margin:0 0 20px">
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

@php $planNow = old('plan', 'port1'); $portNow = old('port_type', 'winbox'); @endphp
<div class="vpn-grid">
    <form class="block" method="POST" action="{{ route('vpn.store') }}" id="vpn-form">
        @csrf
        <div class="field">
            <label for="server">Server</label>
            <select class="input" id="server" name="server" required @error('server') aria-invalid="true" @enderror>
                <option value="">Pilih server</option>
                @foreach ($servers as $key => $s)
                    <option value="{{ $key }}" @selected(old('server') === $key)>{{ $s['name'] }} ({{ $s['host'] }})</option>
                @endforeach
            </select>
        </div>

        <fieldset class="portbox">
            <legend>Paket</legend>
            <div class="radios stack">
                @foreach ($plans as $key => $pl)
                    <label class="radio">
                        <input type="radio" name="plan" value="{{ $key }}" data-plan-radio data-price="{{ $pl['price'] }}" data-single="{{ $pl['fixed'] ? '0' : '1' }}" @checked($planNow === $key)>
                        <span><strong>{{ $pl['name'] }}</strong>, Rp {{ number_format($pl['price'], 0, ',', '.') }} / bulan<small>{{ $pl['desc'] }}</small></span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div class="cols-2">
            <div class="field">
                <label for="username">Username</label>
                <input class="input" id="username" name="username" value="{{ old('username') }}" required minlength="4" maxlength="24" pattern="[A-Za-z0-9]{4,24}" autocomplete="off" autocapitalize="none" spellcheck="false" aria-describedby="username-hint" @error('username') aria-invalid="true" @enderror>
                <p class="field-hint" id="username-hint">Huruf dan angka, 4 sampai 24 karakter.</p>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <div class="pw">
                    <input class="input" type="password" id="password" name="password" required minlength="6" maxlength="24" pattern="[A-Za-z0-9]{6,24}" autocomplete="new-password" aria-describedby="password-hint" @error('password') aria-invalid="true" @enderror>
                    <button type="button" class="btn btn-line btn-sm" data-toggle-pw="password" aria-pressed="false">Tampilkan</button>
                </div>
                <p class="field-hint" id="password-hint">Huruf dan angka, 6 sampai 24 karakter.</p>
            </div>
        </div>

        <fieldset class="portbox" data-port-choice>
            <legend>Port tujuan</legend>
            <div class="radios">
                @foreach ($plans['port1']['choose'] as $key)
                    <label class="radio">
                        <input type="radio" name="port_type" value="{{ $key }}" @checked($portNow === $key) data-port-radio>
                        <span>{{ $ports[$key]['label'] }}@if ($ports[$key]['port']) ({{ $ports[$key]['port'] }})@else (1 sampai 65535)@endif</span>
                    </label>
                @endforeach
            </div>
            <div class="field" data-custom-port hidden style="margin:12px 0 0">
                <label for="custom_port">Nomor port custom</label>
                <input class="input" type="number" id="custom_port" name="custom_port" min="1" max="65535" inputmode="numeric" value="{{ old('custom_port') }}">
            </div>
        </fieldset>

        <div class="portbox" data-port-fixed hidden>
            <p style="margin:0"><strong>Port yang Anda dapat</strong></p>
            @php $fx = []; foreach ($plans['port4']['fixed'] as $key) { $fx[] = $ports[$key]['label'].' ('.$ports[$key]['port'].')'; } @endphp
            <p style="margin:6px 0 0;color:var(--text-2)">{{ implode(', ', $fx) }}. Port tujuan bisa diubah nanti di halaman Kelola.</p>
        </div>

        <p class="total">Total: <strong id="vpn-total">Rp 0</strong> <span>per bulan</span></p>
        <div class="btn-bar">
            <button type="submit" class="btn btn-primary" data-busy="Mengirim...">Order</button>
            <a class="btn btn-line" href="{{ route('vpn.index') }}">Kembali</a>
        </div>
    </form>

    <aside class="block" aria-labelledby="info-title">
        <h2 id="info-title">Cara kerjanya</h2>
        <ol class="steps">
            <li>Isi form, lalu tekan Order. Pesanan masuk ke Pesanan Saya.</li>
            <li>Bayar dari Pesanan Saya, lalu admin mengonfirmasi.</li>
            <li>Setelah aktif, buka Kelola untuk melihat alamat remote dan menyalin script L2TP untuk router Anda.</li>
        </ol>
        <p>Port tujuan adalah port di router Anda yang ingin dibuka dari luar, misalnya Winbox (8291) atau API (8728). Pakai custom kalau port service di router sudah Anda ubah.</p>
        <p>Port tujuan dan password bisa diubah kapan saja lewat tombol Kelola.</p>
    </aside>
</div>
@endsection
