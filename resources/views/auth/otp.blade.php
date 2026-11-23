@extends('layouts.site')

@section('title', 'Kode verifikasi | Routerverse')
@section('nofab', true)

@section('content')
<div class="container auth">
    <aside class="auth-aside">
        <x-arcs />
        <h1 style="margin-top:16px">Satu langkah lagi untuk masuk</h1>
        <p>Kami mengirim kode 6 angka ke {{ $maskedEmail }}. Kode ini memastikan hanya pemilik akun yang bisa masuk.</p>
        <ul class="rows">
            <li><h3>Kode berlaku {{ $ttl }} menit</h3><p>Setelah itu Anda bisa meminta kode baru.</p></li>
            <li><h3>Tidak ada email?</h3><p>Periksa folder spam, lalu minta kode baru di bawah.</p></li>
        </ul>
    </aside>

    <div class="auth-card">
        <h2>Masukkan kode verifikasi</h2>

        <form method="POST" action="{{ route('login.otp.verify') }}" novalidate>
            @csrf
            <div class="field">
                <label for="code">Kode 6 angka</label>
                <input class="input otp-input" type="text" id="code" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code" required autofocus
                       aria-describedby="code-hint @error('code') code-error @enderror" @error('code') aria-invalid="true" @enderror>
                <p class="field-hint" id="code-hint">Kode dikirim ke {{ $maskedEmail }}.</p>
                @error('code') <p class="field-error" id="code-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn btn-primary btn-block" data-busy="Memeriksa...">Masuk</button>
        </form>

        <form method="POST" action="{{ route('login.otp.resend') }}" style="margin-top:16px">
            @csrf
            <button type="submit" class="btn btn-line btn-block">Kirim ulang kode</button>
            <p class="field-hint" style="margin-top:8px">Kode baru bisa diminta setiap {{ $resendSeconds }} detik.@if ($wait > 0) Tunggu sekitar {{ $wait }} detik lagi.@endif</p>
        </form>

        <form method="POST" action="{{ route('login.otp.cancel') }}">
            @csrf
            <p class="auth-alt"><button type="submit" class="linklike">Kembali ke halaman masuk</button></p>
        </form>
    </div>
</div>
@endsection
