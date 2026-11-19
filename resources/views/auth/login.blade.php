@extends('layouts.site')

@section('title', 'Masuk | Routerverse')
@section('nofab', true)

@section('content')
<div class="container auth">
    <aside class="auth-aside">
        <x-arcs />
        <h1 style="margin-top:16px">Masuk untuk memesan dan memantau layanan</h1>
        <p>Dari dashboard Anda bisa mengerjakan tiga hal ini.</p>
        <ul class="rows">
            <li><h3>Layanan dan Paket</h3><p>Pesan setting jaringan, remote server, atau hosting.</p></li>
            <li><h3>Pesanan Saya</h3><p>Lihat status pesanan dan pembayaran.</p></li>
            <li><h3>Server Saya</h3><p>Lihat server yang sudah aktif.</p></li>
        </ul>
    </aside>

    <div class="auth-card">
        <h2>Masuk ke akun</h2>

        @if ($errors->any() && ! $errors->has('email') && ! $errors->has('password'))
            <p class="notice notice-bad" role="alert" style="margin:0 0 18px">{{ $errors->first() }}</p>
        @endif

        <form method="POST" action="{{ route('login.process') }}" novalidate>
            @csrf
            <div class="field">
                <label for="email">Email</label>
                <input class="input" type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus
                       @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email') <p class="field-error" id="email-error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="pw">
                    <input class="input" type="password" id="password" name="password" autocomplete="current-password" required
                           @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                    <button type="button" class="btn btn-line btn-sm" data-toggle-pw="password" aria-pressed="false">Tampilkan</button>
                </div>
                @error('password') <p class="field-error" id="password-error">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-block" data-busy="Memproses...">Masuk</button>
        </form>

        <p class="auth-alt">Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
        <p class="auth-alt" style="margin-top:8px">Lupa password? <a href="https://wa.me/{{ config('services.whatsapp.number') }}?text={{ rawurlencode('Halo Routerverse, saya lupa password akun saya.') }}" target="_blank" rel="noopener">Hubungi admin lewat WhatsApp</a></p>
    </div>
</div>
@endsection
