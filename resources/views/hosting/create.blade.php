@extends('layouts.dashboard')

@section('title', 'Order Mikhmon Online')

@section('content')
<nav class="crumbs" aria-label="Jejak halaman"><a href="{{ route('services.index') }}">Layanan</a> <span aria-hidden="true">/</span> <span aria-current="page">Order Mikhmon Online</span></nav>

<div class="pg-head">
    <div><h1>Order Mikhmon Online</h1></div>
</div>

@if ($errors->any())
    <div class="notice notice-bad" role="alert" style="margin:0 0 20px">
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="vpn-grid">
    <form class="block" method="POST" action="{{ route('hosting.store') }}">
        @csrf
        <div class="field">
            <label for="subdomain">Subdomain</label>
            <div class="suffix-input">
                <input class="input" id="subdomain" name="subdomain" value="{{ old('subdomain') }}" required minlength="4" maxlength="24" pattern="[A-Za-z0-9]{4,24}" autocomplete="off" autocapitalize="none" spellcheck="false" aria-describedby="sub-hint" @error('subdomain') aria-invalid="true" @enderror>
                <span class="suffix">.{{ $domain }}</span>
            </div>
            <p class="field-hint" id="sub-hint">Huruf dan angka tanpa spasi, 4 sampai 24 karakter. Ini alamat Mikhmon Anda.</p>
        </div>

        @if (config('provisioning.docker.gate'))
        <div class="field">
            <label for="password">Sandi pembuka</label>
            <div class="pw">
                <input class="input" type="password" id="password" name="password" required minlength="6" maxlength="24" pattern="[A-Za-z0-9]{6,24}" autocomplete="new-password" aria-describedby="pw-hint" @error('password') aria-invalid="true" @enderror>
                <button type="button" class="btn btn-line btn-sm" data-toggle-pw="password" aria-pressed="false">Tampilkan</button>
            </div>
            <p class="field-hint" id="pw-hint">Dipakai di kotak login saat alamat Anda dibuka (username: pelanggan). Huruf dan angka, 6 sampai 24 karakter.</p>
        </div>
        @endif

        <p class="total">Total: <strong>Rp {{ number_format($price, 0, ',', '.') }}</strong> <span>per bulan</span></p>
        <div class="btn-bar">
            <button type="submit" class="btn btn-primary" data-busy="Mengirim...">Order</button>
            <a class="btn btn-line" href="{{ route('services.index') }}">Kembali</a>
        </div>
    </form>

    <aside class="block" aria-labelledby="info-title">
        <h2 id="info-title">Cara kerjanya</h2>
        <ol class="steps">
            <li>Pilih subdomain, lalu tekan Order.</li>
            <li>Bayar dengan poin dari Pesanan Saya. Setelah lunas, Mikhmon Anda aktif otomatis.</li>
            <li>Buka alamat Anda dan masuk ke Mikhmon dengan login bawaan <strong>mikhmon / 1234</strong>. <strong>Segera ganti</strong> di menu Admin, karena alamat Mikhmon bisa dibuka siapa saja.</li>
        </ol>
        <p>Untuk menghubungkan router, Anda perlu akun VPN Remote agar Mikhmon bisa menjangkau router lewat IP VPN.</p>
        <p>Subdomain tidak bisa diganti setelah dipesan.</p>
    </aside>
</div>
@endsection
