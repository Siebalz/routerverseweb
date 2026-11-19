@extends('layouts.site')

@section('title', 'Daftar | Routerverse')
@section('nofab', true)

@section('content')
<div class="container auth">
    <aside class="auth-aside">
        <x-arcs />
        <h1 style="margin-top:16px">Buat akun untuk memesan layanan Routerverse</h1>
        <p>Pendaftaran memakai nama, email, dan password. Setelah itu Anda diarahkan ke halaman masuk.</p>
        <ul class="rows">
            <li><h3>Pesan dari dashboard</h3><p>Pilih layanan dan kirim catatan kebutuhan Anda.</p></li>
            <li><h3>Pantau pesanan</h3><p>Status pesanan dan server Anda tampil di satu tempat.</p></li>
        </ul>
    </aside>

    <div class="auth-card">
        <h2>Buat akun baru</h2>

        @if ($errors->any())
            <p class="notice notice-bad" role="alert" style="margin:0 0 18px">Periksa kembali isian Anda. {{ $errors->first() }}</p>
        @endif

        <form method="POST" action="{{ route('register') }}" novalidate>
            @csrf
            <div class="field">
                <label for="name">Nama lengkap</label>
                <input class="input" type="text" id="name" name="name" value="{{ old('name') }}" autocomplete="name" required autofocus
                       @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                @error('name') <p class="field-error" id="name-error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input class="input" type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" required
                       @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email') <p class="field-error" id="email-error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="pw">
                    <input class="input" type="password" id="password" name="password" autocomplete="new-password" required aria-describedby="password-hint @error('password') password-error @enderror"
                           @error('password') aria-invalid="true" @enderror>
                    <button type="button" class="btn btn-line btn-sm" data-toggle-pw="password" aria-pressed="false">Tampilkan</button>
                </div>
                <p class="field-hint" id="password-hint">Minimal 8 karakter, kombinasi huruf dan angka.</p>
                @error('password') <p class="field-error" id="password-error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">Konfirmasi password</label>
                <div class="pw">
                    <input class="input" type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                    <button type="button" class="btn btn-line btn-sm" data-toggle-pw="password_confirmation" aria-pressed="false">Tampilkan</button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block" data-busy="Mendaftarkan...">Daftar</button>
        </form>

        <p class="auth-alt">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
    </div>
</div>
@endsection
