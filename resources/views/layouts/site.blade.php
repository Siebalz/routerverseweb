@php
    $wa   = config('services.whatsapp.number');
    $home = request()->routeIs('welcome') ? '' : route('welcome');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Routerverse')</title>
    <meta name="description" content="@yield('description', 'Setting jaringan MikroTik, remote server, hosting Mikhmon dan PHPNuxBill, serta template voucher hotspot dari Routerverse, Dayeuhkolot, Kab. Bandung.')">
    <meta name="theme-color" content="#3B46F2">
    <link rel="icon" type="image/png" href="{{ asset('image/favicon.png') }}">
    <link rel="preload" href="{{ asset('font/Poppins-Bold.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('rv.css') }}">
    @stack('head')
</head>
<body>
<a class="skip" href="#isi">Lewati ke konten</a>

<header class="site-header">
    <div class="container bar">
        <a class="brand" href="{{ route('welcome') }}">
            <img src="{{ asset('image/logo_sm.png') }}" width="40" height="25" alt="">
            Routerverse
        </a>

        <nav id="site-nav" class="site-nav" aria-label="Navigasi utama">
            <a href="{{ $home }}#setting-jaringan">Setting Jaringan</a>
            <a href="{{ $home }}#remote-server">Remote Server</a>
            <a href="{{ $home }}#hosting">Hosting</a>
            <a href="{{ route('products.index') }}" @if(request()->routeIs('products.*')) aria-current="page" @endif>Template Voucher</a>
            <a href="{{ $home }}#kontak">Kontak</a>
            <div class="nav-auth">
                @auth
                    <a class="btn-primary" href="{{ route('dashboard') }}">Dashboard</a>
                @else
                    <a class="btn-line" href="{{ route('login') }}">Masuk</a>
                    <a class="btn-primary" href="{{ route('register') }}">Daftar</a>
                @endauth
            </div>
        </nav>

        <div class="nav-actions">
            @auth
                <a class="btn btn-primary btn-sm" href="{{ route('dashboard') }}">Dashboard</a>
            @else
                <a class="btn btn-line btn-sm" href="{{ route('login') }}">Masuk</a>
                <a class="btn btn-primary btn-sm" href="{{ route('register') }}">Daftar</a>
            @endauth
        </div>
        <button id="menu-btn" class="menu-btn" type="button" aria-expanded="false" aria-controls="site-nav">Menu</button>
    </div>
</header>

<main id="isi">
    @if (session('success') || session('error'))
        <div class="container">
            @if (session('success')) <p class="notice notice-ok" role="status">{{ session('success') }}</p> @endif
            @if (session('error'))   <p class="notice notice-bad" role="alert">{{ session('error') }}</p> @endif
        </div>
    @endif
    @yield('content')
</main>

<footer class="site-footer">
    <div class="container foot">
        <div>
            <a class="brand" href="{{ route('welcome') }}">
                <img src="{{ asset('image/logo_putih_sm.png') }}" width="40" height="16" alt="">
                Routerverse
            </a>
            <p style="margin-top:12px;max-width:44ch">Setting jaringan, remote server, dan hosting. Dayeuhkolot, Kab. Bandung.</p>
        </div>
        <nav class="foot-links" aria-label="Tautan footer">
            <a href="{{ route('products.index') }}">Template Voucher</a>
            <a href="https://kuma.routerverse.id/status/routerverse" target="_blank" rel="noopener">Status Server</a>
            <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener">WhatsApp</a>
            <a href="https://instagram.com/routerverse.id" target="_blank" rel="noopener">Instagram</a>
        </nav>
        <p class="foot-copy">&copy; {{ date('Y') }} Routerverse.</p>
    </div>
</footer>

@hasSection('nofab')
@else
    <a class="wa-fab" href="https://wa.me/{{ $wa }}?text={{ rawurlencode('Halo Routerverse, saya tertarik dengan layanan Anda.') }}" target="_blank" rel="noopener">Chat WhatsApp</a>
@endif

@stack('dialogs')
<script src="{{ asset('rv.js') }}" defer></script>
</body>
</html>
