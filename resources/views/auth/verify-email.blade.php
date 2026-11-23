@extends('layouts.site')

@section('title', 'Verifikasi email | Routerverse')
@section('nofab', true)

@section('content')
<div class="container auth">
    <aside class="auth-aside">
        <x-arcs />
        <h1 style="margin-top:16px">Cek email Anda untuk mengaktifkan akun</h1>
        <p>Kami mengirim tautan verifikasi ke {{ $email }}. Klik tautan itu, lalu dashboard bisa dipakai.</p>
        <ul class="rows">
            <li><h3>Tautan berlaku 60 menit</h3><p>Setelah itu minta email baru dari halaman ini.</p></li>
            <li><h3>Tidak ada email?</h3><p>Periksa folder spam. Pastikan alamat email di atas sudah benar.</p></li>
        </ul>
    </aside>

    <div class="auth-card">
        <h2>Verifikasi email</h2>
        <p style="color:var(--text-2);margin:-8px 0 20px">Setelah email terverifikasi, muat ulang halaman ini atau buka dashboard.</p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary btn-block" data-busy="Mengirim...">Kirim ulang email</button>
        </form>

        <div class="btn-bar" style="margin-top:12px">
            <a class="btn btn-line btn-block" href="{{ route('dashboard') }}">Saya sudah verifikasi</a>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <p class="auth-alt">Salah alamat email? <button type="submit" class="linklike">Keluar</button> lalu daftar dengan email yang benar.</p>
        </form>
    </div>
</div>
@endsection
